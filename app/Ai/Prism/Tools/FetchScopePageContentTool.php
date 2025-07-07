<?php

namespace App\Ai\Prism\Tools;

use App\Models\Scope;
use Prism\Prism\Tool;

class FetchScopePageContentTool extends Tool
{
    public function __construct()
    {
        $this->as('fetch_scope_page_content')
            ->for('Fetch the raw HTML of the web page related to the scope.')
            ->withNumberParameter(
                name: 'scope_id',
                description: 'The primary key of the scope.',
            )
            ->using($this);
    }

    public function __invoke(int $scope_id): string
    {
        $scope = Scope::find($scope_id);
        if (!$scope) {
            return json_encode(['error' => 'scope_not_found']);
        }

        return json_encode(['html' => $scope->getPageContent() ?? ''], JSON_PRETTY_PRINT);
    }
}
