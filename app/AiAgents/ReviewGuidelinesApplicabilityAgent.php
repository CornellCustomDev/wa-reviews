<?php

namespace App\AiAgents;

use App\Enums\ChatProfile;
use App\Models\Guideline;
use App\Models\Issue;
use App\Models\Item;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use LarAgent\Agent;
use Throwable;

class ReviewGuidelinesApplicabilityAgent extends Agent
{
    protected $history = 'file';

    protected Issue $issue;

    public function __construct(Issue $issue, string $key)
    {
        $this->provider = config('cornell_ai.laragent_profile');
        $this->model = config('cornell_ai.profiles')[ChatProfile::Chat->value]['model'];

        $this->issue = $issue;

        parent::__construct($key);
    }

    protected function buildSessionId(): string
    {
        return sprintf(
            '%s_%s_%s',
            class_basename($this),
            $this->issue->id,
            $this->getChatKey()
        );
    }

    /**
     * @throws Throwable
     */
    public function instructions(): string
    {
        return view('ai-agents.ReviewGuidelinesAnalyzer.instructions')->render();
    }

    public function structuredOutput(): array
    {
        $guidelineSchema = GuidelinesAnalyzerService::getItemsSchema();
        $guidelineSchema['type'] = ['object', 'null'];
        $guidelineSchema['description'] = 'Filled in when the guideline is applicable; null otherwise.';

        return [
            'name' => 'review_guideline_applicability_response',
            'schema' => [
                'description' => 'Response from the AI regarding the applicability of the guideline to the issue.',
                'type' => 'object',
                'properties' => [
                    'review' => [
                        'type' => 'string',
                        'enum' => ['Applicable', 'Not Applicable', 'Uncertain'],
                    ],
                    'review_reasoning' => [
                        'type' => 'string',
                        'description' => 'A brief explanation of the reasoning behind the assessment.',
                    ],
                    'guideline' => $guidelineSchema,
                ],
                'additionalProperties' => false,
                'required' => ['review', 'review_reasoning', 'guideline'],
            ],
            'strict' => true,
        ];
    }

    public function getContext(int $guidelineNumber, ?array $item): string
    {
        $guideline = Guideline::with(['criterion', 'category'])->firstWhere('id', $guidelineNumber);
        $guidelineData = GuidelinesAnalyzerService::mapGuidelineToSchema($guideline);
        $guidelineContext = json_encode($guidelineData, JSON_PRETTY_PRINT);
        $issueContext = GuidelinesAnalyzerService::getIssueContext($this->issue);
        $itemContext = $item ? json_encode($item, JSON_PRETTY_PRINT) : 'None provided';

        return <<<CONTEXT
# Context: Web accessibility guideline

$guidelineContext

# Context: Web accessibility issue

$issueContext

# Context: Current assessment of applicability

$itemContext;
CONTEXT;
    }
}
