<?php

namespace App\Services\AccessibilityContentParser;

use App\Services\AccessibilityContentParser\ActRules\ActRuleBase;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class AccessibilityContentParserService
{
    /**
     * @throws Exception
     */
    public function retrieveHtml(string $url): string
    {
        try {
            $response = Http::get($url);
        } catch (Exception $e) {
            throw new Exception("Error response retrieving '$url':  " . $e->getMessage());
        }

        if ($response->failed()) {
            throw new Exception('Failed to retrieve the URL: ' . $url);
        }

        return $response->body();
    }

    public function parseDom(string $html): Crawler
    {
        return new Crawler($html);
    }

    public function getPageContent(string $pageUrl)
    {
        $cacheKey = 'page_body_' . md5($pageUrl);
        return cache()->remember($cacheKey, 3600, function () use ($pageUrl) {
            $html = $this->retrieveHtml($pageUrl);
            $crawler = $this->parseDom($html);
            return $crawler->filter('body')->html();
        });
    }

    public function getApplicableRules($html): array
    {
        $rules = self::getAllRules();

        return $rules->filter(fn($rule) => $rule->doesRuleApply($html))
            ->map(fn(ActRuleBase $rule) => $rule->getRule())
            ->toArray();
    }

    public static function getAllRules(): Collection
    {
        return collect(scandir(__DIR__.'/ActRules/Rules'))
            ->filter(fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'php')
            ->map(fn($file) => pathinfo($file, PATHINFO_FILENAME))
            ->mapWithKeys(function ($className) {
                $ruleClass = 'App\Services\AccessibilityContentParser\ActRules\Rules\\' . $className;
                /** @var ActRuleBase $rule */
                $rule = new $ruleClass;
                return [($rule)->getMachineName() => $rule];
            });
    }
}
