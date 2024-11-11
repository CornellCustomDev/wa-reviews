<?php

namespace App\Services\SiteImprove;

use ErrorException;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class SiteimproveService
{
    private string $siteId;
    private array $conformance = ['a', 'aa', 'aria', 'si'];

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

    public function setSite(string $siteId): void
    {
        $this->siteId = $siteId;
    }

    /**
     * @throws RequestException
     */
    private function get(string $endpoint, array $parameters = []): Response
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
    private function siteGet(string $endpoint, array $parameters = [], int $ttl_minutes = 5): array
    {
        if (empty($this->siteId)) {
            throw new ErrorException('Site ID is not set');
        }

        return Cache::remember(
            key: "siteimprove_" . $this->siteId . "_" . $endpoint . "_" . md5(serialize($parameters)),
            ttl: now()->addMinutes($ttl_minutes),
            callback: fn() => $this->get("sites/$this->siteId/$endpoint", $parameters)->json()
        );
    }

    /**
     * @throws RequestException|ErrorException
     */
    private function getPageId(string $url): ?string
    {
        $parameters = [
            'search_in' => 'url',
            'url' => $url,
        ];
        $response = $this->siteGet('content/pages', $parameters, 3600 * 30);
        $page = collect($response['items'])->first(fn($item) => $item['url'] === $url);

        return $page ? $page['id'] : null;
    }

    /**
     * @throws RequestException|ErrorException
     */
    private function getPagesWithIssues(string $urlQuery = null): ?array
    {
        $parameters = [
            'conformance' => join(',', $this->conformance)
        ];
        // If there's a $urlQuery, set the 'search_in' parameter to 'url' and set query to the urlQuery
        if ($urlQuery) {
            $parameters['search_in'] = 'url';
            $parameters['query'] = $urlQuery;
        }

        $response = $this->siteGet("a11y/issue_kinds/confirmed/pages", $parameters);

        $pages = [];
        foreach ($response['items'] as $item) {
            $pages[$item['url']] = [
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
        try {
            $pages = $this->getPagesWithIssues(urlQuery: $url);
        } catch (Exception) {
            return null;
        }

        return $pages[$url]['page_report'] ?? null;
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
