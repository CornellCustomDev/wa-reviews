<?php

namespace App\Services\GuidelinesAnalyzer\Tools;

use App\Models\Issue;
use App\Models\Item;
use App\Services\CornellAI\ChatServiceFactoryInterface;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use App\Events\ItemChanged;
use App\Enums\AIStatus;
use App\Enums\Assessment;
use App\Enums\Impact;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerServiceInterface;
use Illuminate\Support\Str;

class StoreGuidelineMatches extends Tool
{
    public function __construct(
        private readonly GuidelinesAnalyzerServiceInterface $guidelinesAnalyzerService,
    ) {}

    public function getName(): string
    {
        return 'store_guideline_matches';
    }

    public function getDescription(): string
    {
        return 'Stores web accessibility guideline matches for an issue into the database. The guidelines are based on the Guidelines Document.';
    }

    public function call(string $arguments): array
    {
        $args = json_decode($arguments, true);
        $issue = Issue::findOrFail($args['issue_id']);

        return $this->store($issue, $args['guidelines']);
    }

    public function schema(): array
    {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'issue_id' => [
                        'type' => 'integer',
                        'description' => 'The primary key of the issue.',
                    ],
                    'guidelines' => [
                        'type' => 'array',
                        'items' => self::getItemsSchema(),
                    ],
                ],
                'additionalProperties' => false,
                'required' => ['issue_id', 'guidelines'],
            ],
            'strict' => true,
        ];
    }

    public static function getItemsSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'reasoning' => ['type' => 'string'],
                'number' => ['type' => 'string'],
                'heading' => ['type' => 'string'],
                'criteria' => ['type' => 'string'],
                'assessment' => [
                    'type' => 'string',
                    'enum' => ['Fail', 'Warn'],
                    'description' => 'Must be one of "Fail" or "Warn".',
                ],
                'applicability' => ['type' => 'string'],
                'recommendation' => ['type' => 'string'],
                'testing' => ['type' => 'string'],
                'impact' => [
                    'type' => 'string',
                    'enum' => ['Critical', 'Serious', 'Moderate', 'Low'],
                    'description' => 'Select one of the four severity levels.',
                ],
            ],
            'required' => [
                'reasoning',
                'number',
                'heading',
                'criteria',
                'assessment',
                'applicability',
                'recommendation',
                'testing',
                'impact',
            ],
            'additionalProperties' => false,
        ];
    }

    public function store(Issue $issue, mixed $guidelines): array
    {
        $agent = $this->guidelinesAnalyzerService->getAgent();

        foreach ($guidelines as $guideline) {
            $item = Item::create([
                'issue_id' => $issue->id,
                'guideline_id' => $guideline['number'],
                'assessment' => Assessment::fromName($guideline['assessment']),
                'description' => Str::markdown(htmlentities($guideline['applicability'])),
                'recommendation' => Str::markdown(htmlentities($guideline['recommendation'])),
                'testing' => Str::markdown(htmlentities($guideline['testing'])),
                'impact' => Impact::fromName($guideline['impact']),
                'ai_reasoning' => Str::markdown(htmlentities($guideline['reasoning'])),
                'ai_status' => AIStatus::Generated,
                'agent_id' => $agent->id,
            ]);

            event(new ItemChanged($item, 'created', $item->getAttributes(), $agent));
        }

        return ['status' => 'stored'];
    }
}
