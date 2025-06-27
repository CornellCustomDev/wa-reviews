<?php

namespace App\AiAgents\Tools;

use App\Models\Issue;

class FetchIssuePageContentTool extends BaseTool
{
    protected string $description = 'Fetch the raw HTML of the web page related to the issue.';

    public static array $schema = [
        'issue_id' => [
            'type' => 'integer',
            'description' => 'The primary key of the issue.',
        ],
    ];

    public function handle(array $input): mixed
    {
        $issueId = $input['issue_id'];
        $issue = Issue::find($issueId);
        if (!$issue) {
            return ['error' => 'issue_not_found'];
        }

        return FetchScopePageContentTool::run($issue->scope_id);
    }
}
