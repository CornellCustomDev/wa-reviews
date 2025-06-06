<?php

namespace App\Services\SiteImprove;

use App\Models\Scope;
use ErrorException;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class SiteimproveService
{
    protected string $siteId;
    protected array $conformance = ['a', 'aa', 'aria', 'si'];

    public function __construct(
        private readonly string $apiKey
    ) {
    }

    public static function make(string $siteId = null): SiteimproveService
    {
        $siteimproveService = app(SiteimproveService::class);

        if ($siteId) {
            $siteimproveService->setSite($siteId);
        }

        return $siteimproveService;
    }

    public static function fromScope(Scope $scope): SiteimproveService
    {
        $siteId = $scope->project->siteimprove_id;
        return self::make($siteId);
    }

    public static function findSite(string $url, ?bool $bustCache = false): ?string
    {
        $siteimproveService = app(SiteimproveService::class);

        if ($bustCache) {
            Cache::forget('siteimprove_sites');
        }

        $json_response = Cache::remember(
            key: 'siteimprove_sites',
            ttl: 60,
            callback: fn() => ($siteimproveService->get('sites', ['page_size' => 1000]))->json()
        );

        $domain = parse_url($url, PHP_URL_HOST);
        $site = collect($json_response['items'])->first(fn($item) => parse_url($item['url'], PHP_URL_HOST) === $domain);

        return $site ? $site['id'] : null;
    }

    public function setSite(string $siteId): void
    {
        $this->siteId = $siteId;
    }

    /**
     * @throws RequestException
     */
    protected function get(string $endpoint, array $parameters = []): Response
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->apiKey)
        ])->get('https://api.siteimprove.com/v2/' . $endpoint, $parameters);

        if ($response->failed()) {
            throw new RequestException($response);
        }

        return $response;
    }

    /**
     * @throws RequestException|ErrorException
     */
    protected function siteGet(string $endpoint, array $parameters = [], int $ttlMinutes = 120, bool $bustCache = false): array
    {
        if (empty($this->siteId)) {
            throw new ErrorException('Site ID is not set');
        }

        if ($bustCache) {
            Cache::forget('siteimprove_' . $this->siteId . '_' . $endpoint . '_' . md5(serialize($parameters)));
        }

        return Cache::remember(
            key: "siteimprove_" . $this->siteId . "_" . $endpoint . "_" . md5(serialize($parameters)),
            ttl: now()->addMinutes($ttlMinutes),
            callback: fn() => $this->get("sites/$this->siteId/$endpoint", $parameters)->json()
        );
    }

    /**
     * @throws RequestException|ErrorException
     */
    protected function getPageId(string $url): ?string
    {
        $trimmedUrl = rtrim($url, '/');

        $parameters = [
            'search_in' => 'url',
            'url' => $trimmedUrl,
        ];
        $response = $this->siteGet('content/pages', $parameters, 3600 * 30);
        $page = collect($response['items'])->first(fn($item) => rtrim($item['url'], '/') === $trimmedUrl);

        return $page ? $page['id'] : null;
    }

    /**
     * @throws RequestException|ErrorException
     */
    public function getPagesWithIssues(string $urlQuery = null, bool $bustCache = false): ?array
    {
        $parameters = [
            'conformance' => join(',', $this->conformance)
        ];
        // If there's a $urlQuery, set the 'search_in' parameter to 'url' and set query to the urlQuery
        if ($urlQuery) {
            $parameters['search_in'] = 'url';
            $parameters['query'] = $urlQuery;
        }

        $response = $this->siteGet("a11y/issue_kinds/confirmed/pages", $parameters, bustCache: $bustCache);

        $pages = [];
        foreach ($response['items'] as $item) {
            $pages[rtrim($item['url'], '/')] = [
                'id' => $item['id'],
                'url' => $item['url'],
                'issues' => $item['issues'],
                'occurrences' => $item['occurrences'],
                'page_report' => $item['_siteimprove']['page_report']['href']. '&conf=' . join('+', $this->conformance),
            ];
        }

        return $pages;
    }

    /**
     * @throws RequestException|ErrorException
     */
    public function getPageIssuesByPageId(string $pageId): ?array
    {
        $parameters = [
            'conformance' => join(',', $this->conformance)
        ];

        $response = $this->siteGet("a11y/issue_kinds/confirmed/pages/$pageId/issues", $parameters);

        $issues = [];
        foreach ($response['items'] as $item) {
            $issues[] = [
                'conformance' => $item['conformance'],
                'difficulty' => $item['difficulty'],
                'title' => $item['help']['title'],
                'description' => $item['help']['description'],
                'occurrences' => $item['occurrences'],
                'rule_id' => $item['rule_id'],
            ];
        }

        return $issues;
    }

    public function getPageReportUrl(string $url): ?string
    {
        $trimmedUrl = rtrim($url, '/');

        try {
            $pages = $this->getPagesWithIssues(urlQuery: $trimmedUrl);
        } catch (Exception) {
            return null;
        }

        return $pages[$trimmedUrl]['page_report'] ?? null;
    }

    public static function getPageReportUrlForScope(Scope $scope): ?string
    {
        $trimmedUrl = rtrim($scope->url, '/');

        $key = "siteimprove_url_$trimmedUrl";
        $cachedValue = Cache::get($key);
        if ($cachedValue === '') {
            Cache::forget($key);
        }

        $siteimproveService = SiteimproveService::fromScope($scope);
        return Cache::rememberForever(
            key: $key,
            callback: fn() => $siteimproveService->getPageReportUrl($trimmedUrl) ?? ''
        );
    }

    public function getPageIssuesCount(string $url): ?int
    {
        try {
            $pageId = $this->getPageId($url);
            if (!$pageId) {
                return null;
            }
            $issues = $this->getPageIssuesByPageId($pageId);
        } catch (Exception) {
            return null;
        }

        return collect($issues)->sum('occurrences');
    }

    public function getPageIssues(string $url): ?array
    {
        try {
            $pageId = $this->getPageId($url);
            if (!$pageId) {
                return null;
            }
            $issues = $this->getPageIssuesByPageId($pageId);
        } catch (Exception) {
            return null;
        }

        return $issues;
    }
}
