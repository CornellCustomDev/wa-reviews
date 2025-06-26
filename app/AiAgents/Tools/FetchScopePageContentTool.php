<?php

namespace App\AiAgents\Tools;

use App\Models\Scope;

class FetchScopePageContentTool extends BaseTool
{
    protected string $description = 'Fetch the raw HTML of the web page related to the scope.';

    protected static array $schema = [
        'scope_id' => [
            'type' => 'integer',
            'description' => 'The primary key of the scope.',
        ],
    ];

    public static function run(int $scope_id): array
    {
        return parent::call([
            'scope_id' => $scope_id,
        ]);
    }

    protected function handle(array $input): array
    {
        $scopeId = $input['scope_id'];
        $scope = Scope::find($scopeId);
        if (!$scope) {
            return ['error' => 'scope_not_found'];
        }

        return [
            'html' => $scope->getPageContent() ?? '',
        ];
    }
}
