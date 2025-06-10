<?php

namespace App\AiAgents\Tools;

use App\AiAgents\ScopeAnalyzerAgent;
use App\Models\Scope;
use Illuminate\Support\Str;
use LarAgent\Tool;

class AnalyzeScopeTool extends Tool
{
    public string $name = 'analyze_scope';

    public string $description =
        'Analyze the specified page scope and return applicable web accessibility guideline issues based on '
        . 'the Guidelines Document. If no guideline applies, or more information is needed, it returns a feedback '
        . 'message instead.';

    public array $required = ['scope_id'];

    public static function call(int $scope_id, ?string $context = null): array
    {
        return (new self())->execute([
            'scope_id' => $scope_id,
            'context' => $context,
        ]);
    }

    public function getProperties(): array
    {
        return [
            'scope_id' => [
                'type' => 'integer',
                'description' => 'The primary key of the page scope to analyze.',
            ],
            'context' => [
                'type' => ['string', 'null'],
                'description' => 'Any additional context or information to provide to the agent.',
            ],
        ];
    }

    public function execute(array $input): array
    {
        $scopeId = $input['scope_id'] ?? null;

        if (!is_numeric($scopeId)) {
            return ['error' => 'scope_id_parameter_missing'];
        }

        $additionalContext = $input['context'] ?? null;

        // Fetch the scope and analyze it
        $scope = Scope::find($scopeId);
        if (!$scope) {
            return ['error' => 'scope_not_found'];
        }

        // TODO: Determine how we want to key these, probably somehow back to the calling chat?
        $agent = new ScopeAnalyzerAgent($scope, Str::ulid());

        // TODO: Can we stream tool usage? If so, this response should be streamed
        // Perform analysis and return results
        $response = $agent->respond($agent->getContext($additionalContext));

        $results = [];
        if (isset($response['issues'])) {
            foreach ($response['issues'] as $response) {
                $results[] = [
                    'target' => $response['target'],
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
