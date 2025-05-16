<?php

namespace App\AiAgents\Tools;

use App\Models\Issue;
use LarAgent\Tool;

class FetchIssuePageContentTool extends Tool
{
    protected string $name = 'fetch_issue_page_content';

    protected string $description = 'Fetch the raw HTML of the web page related to the issue.';

    protected array $required = ['issue_id'];

    public function getProperties(): array
    {
        return [
            'issue_id' => [
                'type' => 'integer',
                'description' => 'The primary key of the issue.',
            ],
        ];
    }

    public function execute(array $input): mixed
    {
        $issueId = $input['issue_id'] ?? null;

        if (!is_numeric($issueId)) {
            return ['error' => 'issue_id_parameter_missing'];
        }

        $issue = Issue::find($issueId);

        if (!$issue) {
            return ['error' => 'issue_not_found'];
        }

        $pageContent = $issue->scope->page_content ?? '';

        return [
            'html' => $pageContent,
        ];
    }
}
