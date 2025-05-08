<?php

namespace App\AiAgents\Tools;

use App\AiAgents\GuidelinesAnalyzerAgent;
use App\Models\Issue;
use Illuminate\Support\Str;
use LarAgent\Tool;

class AnalyzeIssueTool extends Tool
{
    public string $name = 'analyze_accessibility_issue';

    public string $description =
        'Analyze the specified accessibility issue and return applicable web accessibility guidelines based on '
        . 'the Guidelines Document. If no guideline applies, or more information is needed, return a feedback '
        . 'message instead.';

    public array $required = ['issue_id'];

    public function getProperties(): array
    {
        return [
            'issue_id' => [
                'type' => 'integer',
                'description' => 'The primary key of the accessibility issue to analyze.',
            ],
        ];
    }

    public function execute(array $input): array
    {
        $issueId = $input['issue_id'] ?? null;

        if (!is_numeric($issueId)) {
            return ['error' => 'issue_id_parameter_missing'];
        }

        // Fetch the issue and analyze it
        $issue = Issue::find($issueId);
        if (!$issue) {
            return ['error' => 'issue_not_found'];
        }

        // TODO: Determine how we want to key these, probably somehow back to the calling chat?
        $agent = new GuidelinesAnalyzerAgent($issue, Str::ulid());

        // Perform analysis and return results
        $response = $agent->respond($agent->getContext());

        $results = [];
        if (isset($response['guidelines'])) {
            foreach ($response['guidelines'] as $response) {
                $results[] = [
                    'number' => $response['number'],
                    'reasoning' => $response['reasoning'],
                    'assessment' => $response['assessment'],
                    'observation' => $response['observation'],
                    'recommendation' => $response['recommendation'],
                    'testing' => $response['testing'],
                    'impact' => $response['impact'],
                ];
            }
        } elseif (isset($response['feedback'])) {
            $results['feedback'] = $response['feedback'];
        }

        return $results;
    }

}
