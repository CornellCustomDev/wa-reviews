<?php

namespace App\Services\GuidelinesAnalyzer;

use App\Enums\Agents;
use App\Enums\AIStatus;
use App\Enums\Assessment;
use App\Enums\Impact;
use App\Events\ItemChanged;
use App\Models\Agent;
use App\Models\Issue;
use App\Models\Item;
use App\Services\CornellAI\ChatServiceFactoryInterface;
use App\Services\GuidelinesAnalyzer\Tools\AnalyzeIssue;
use App\Services\GuidelinesAnalyzer\Tools\StoreGuidelineMatches;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GuidelinesAnalyzerService implements GuidelinesAnalyzerServiceInterface
{
    public function __construct(
        private readonly ChatServiceFactoryInterface $chatServiceFactory,
    ) {}

    public static function getAgent(): Agent
    {
        return Agent::firstWhere('name', Agents::GuidelinesAnalyzer->value);
    }

    public function getTools(): array
    {
        return [
            'analyze_accessibility_issue' => new AnalyzeIssue($this->chatServiceFactory, $this),
            'store_guideline_matches' => new StoreGuidelineMatches($this),
        ];
    }

    public function populateIssueItemsWithAI(Issue $issue): array
    {
        $result = (new AnalyzeIssue($this->chatServiceFactory, $this))->analyze($issue);

        // TODO: Handle this feedback in a more meaningful way
        if (isset($result['feedback'])) {
            return $result;
        }

        if (count($result) > 0) {
            (new StoreGuidelineMatches($this))->store($issue, $result);
        }

        return $result;
    }

    public static function getGuidelinesDocumentPrompt(): string
    {
        return <<<PROMPT
# Guidelines Document

When instructions refer to the Guidelines Document, it is the document below. When Guideline numbers are mentioned,
they are the numbered sections in the Guidelines Document.

## Guidelines Document content

PROMPT
            . Storage::get('guidelines-list.md') . "\n\n";
    }

}
