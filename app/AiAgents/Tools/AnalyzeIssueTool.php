<?php

namespace App\AiAgents\Tools;

use App\AiAgents\IssueAnalyzerAgent;
use App\Models\Issue;
use Illuminate\Support\Str;
use Throwable;

class AnalyzeIssueTool extends BaseTool
{
    public string $name = 'analyze_accessibility_issue';

    public string $description =
        'Analyze the specified accessibility issue and return applicable web accessibility guidelines based on '
        . 'the Guidelines Document. If no guideline applies, or more information is needed, it returns a feedback '
        . 'message instead.';

    protected static array $schema = [
        'issue_id' => [
            'type' => 'integer',
            'description' => 'The primary key of the accessibility issue to analyze.',
        ],
        'context' => [
            'type' => ['string', 'null'],
            'description' => 'Any additional context or information to provide to the agent.',
        ],
    ];

    public static function run(int $issue_id, ?string $context = null): array
    {
        return parent::call([
            'issue_id' => $issue_id,
            'context' => $context,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function handle(array $input): mixed
    {
        $issueId = $input['issue_id'];
        $additionalContext = $input['context'] ?? null;

        $issue = Issue::find($issueId);
        if (!$issue) {
            return ['error' => 'issue_not_found'];
        }

        // TODO: Determine how we want to key these, probably somehow back to the calling chat?
        $agent = new IssueAnalyzerAgent($issue, Str::ulid());

        // TODO: Can we stream tool usage? If so, this response should be streamed
        // Perform analysis and return results
        $response = $agent->respond($agent->getContext($additionalContext));

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
