<?php

namespace App\Services\GuidelinesAnalyzer\Tools;

use App\Models\Issue;
use App\Services\GuidelinesAnalyzer\Tools\Tool;

class FetchIssuePageContent extends Tool
{

    public function getName(): string
    {
        return 'fetch_issue_page_content';
    }

    public function getDescription(): string
    {
        return 'Fetch the raw HTML of the web page related to the issue, if it is available.';
    }

    public function call(string $arguments): array
    {
        $arguments = json_decode($arguments, true);
        $issue = Issue::find($arguments['issue_id']);

        if (!$issue) {
            return ['error' => 'issue_not_found'];
        }

        $pageContent = $issue->scope->page_content ?? '';

        return [
            'html' => $pageContent,
        ];
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
                ],
                'required' => ['issue_id'],
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }
}
