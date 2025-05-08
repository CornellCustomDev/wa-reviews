<?php

namespace App\Services\GuidelinesAnalyzer;

use App\Models\Agent;
use App\Models\Guideline;
use App\Models\Issue;
use App\Models\Item;
use App\Services\CornellAI\ChatServiceFactoryInterface;

interface GuidelinesAnalyzerServiceInterface
{
    public function __construct(ChatServiceFactoryInterface $chatServiceFactory);

    public static function getAgent(): Agent;

    public function getTools(): array;

    public function analyzeIssue(Issue $issue): array;

    public function reviewApplicability(Item $item): array;

    public function storeItems(Issue $issue, array $items): array;

    public function populateIssueItemsWithAI(Issue $issue): array;

    public static function getIssueContext(Issue $issue): string;

    public static function getItemsSchema(): array;

    public static function mapItemToSchema(Item $item): array;

    public static function mapGuidelineToSchema(Guideline $guideline): array;

}
