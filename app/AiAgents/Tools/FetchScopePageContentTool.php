<?php

namespace App\AiAgents\Tools;

use App\Models\Scope;
use LarAgent\Tool;

class FetchScopePageContentTool extends Tool
{
    protected string $name = 'fetch_page_content';

    protected string $description = 'Fetch the raw HTML of a web page related to the scope.';

    protected array $required = ['scope_id'];

    public function getProperties(): array
    {
        return [
            'scope_id' => [
                'type' => 'integer',
                'description' => 'The primary key of the scope.',
            ],
        ];
    }

    public static function call($scopeId): array
    {
        return (new self())->execute(['scope_id' => $scopeId]);
    }

    public function execute(array $input): mixed
    {
        $scopeId = $input['scope_id'] ?? null;

        if (!is_numeric($scopeId)) {
            return ['error' => 'scope_id_parameter_missing'];
        }

        $scope = Scope::find($scopeId);

        if (!$scope) {
            return ['error' => 'scope_not_found'];
        }

        $pageContent = $scope->getPageContent() ?? '';

        return [
            'html' => $pageContent,
        ];
    }
}
