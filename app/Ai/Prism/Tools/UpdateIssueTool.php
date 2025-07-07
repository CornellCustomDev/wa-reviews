<?php

namespace App\Ai\Prism\Tools;

use App\Ai\Prism\PrismSchema;
use App\Events\IssueChanged;
use App\Models\Issue;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Prism\Prism\Tool;

class UpdateIssueTool extends Tool
{
    use PrismSchema;

    public function __construct()
    {
        $this->as('update_issue')
            ->for('Update an issue stored in the system.')
            ->withNumberParameter(
                name: 'issue_id',
                description: 'The ID of the issue to update.',
            )
            ->withArrayParameter(
                name: 'data',
                description: 'The data to update the issue with.',
                items: $this->convertToPrismSchema(GuidelinesAnalyzerService::ITEM_SCHEMA)
            )
            ->using($this);
    }

    public function __invoke(int $issue_id, array $data): string
    {
        // Validate the issue ID and data
        if (empty($issue_id) || empty($data)) {
            return json_encode(['error' => 'Missing issue ID or data']);
        }

        $issue = Issue::find($issue_id);
        if (!$issue) {
            return json_encode(['error' => 'Issue not found']);
        }

        // Check if the user has permission to update the issue
        if (!auth()->user()->can('update', $issue)) {
            return json_encode(['error' => 'You do not have permission to update this issue']);
        }

        $issue->update($data);

        event(new IssueChanged($issue, 'updated'));

        return json_encode(['status' => 'success', 'message' => 'Issue updated successfully', 'issue' => $issue]);
    }
}
