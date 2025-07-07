<?php

namespace App\AiAgents\Tools;

use App\Enums\Agents;
use App\Events\IssueChanged;
use App\Models\Agent;
use App\Models\Issue;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;

class UpdateIssueTool extends BaseTool
{
    protected string $description =
        'Updates an issue in the database.';

    public static array $schema = [
        'issue_id' => [
            'type' => 'integer',
            'description' => 'The primary key of the issue.',
        ],
        'data' => GuidelinesAnalyzerService::ITEM_SCHEMA,
    ];

    public static function run(int $issue_id, array $guideline): array
    {
        return (new self())->execute([
            'issue_id' => $issue_id,
            'data' => $guideline,
        ]);
    }

    public function handle(array $input): array
    {
        $issueId = $input['issue_id'];
        $data = $input['data'];

        $issue = Issue::findOrFail($issueId);

        // Check if the user has permission to update the issue
        if (!auth()->user()->can('update', $issue)) {
            return ['status' => 'forbidden', 'feedback' => 'You do not have permission to update this issue.'];
        }

        $agent = Agent::firstWhere('name', Agents::GuidelinesAnalyzer->value);
        $feedback = [];

        // Sometimes the data is a stdClass object, so convert it to an array
        $data = (array) $data;

        $issue->update(GuidelinesAnalyzerService::mapResponseToItemArray($data));

        event(new IssueChanged($issue, 'created', $issue->getAttributes(), $agent));

        return ['status' => 'stored', 'feedback' => $feedback];
    }
}
