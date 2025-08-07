<?php

use App\Livewire\Ai\AnalyzePage;
use App\Livewire\Categories\ShowCategory;
use App\Livewire\Categories\ViewCategories;
use App\Livewire\Ai\Chat;
use App\Livewire\Criteria\ShowCriterion;
use App\Livewire\Criteria\ViewCriteria;
use App\Livewire\Guidelines\Doc;
use App\Livewire\Guidelines\ShowGuideline;
use App\Livewire\Guidelines\ViewGuidelines;
use App\Livewire\Issues\CreateProjectIssue;
use App\Livewire\Issues\CreateSiteimproveIssue;
use App\Livewire\Projects\CreateProject;
use App\Livewire\Projects\Report;
use App\Livewire\Projects\ShowProject;
use App\Livewire\Projects\UpdateProject;
use App\Livewire\Projects\ViewProjects;
use App\Livewire\Items\CreateItem;
use App\Livewire\Items\UpdateItem;
use App\Livewire\Issues\CreateIssue;
use App\Livewire\Issues\ShowIssue;
use App\Livewire\Issues\UpdateIssue;
use App\Livewire\ActRules\ShowRule;
use App\Livewire\ActRules\ViewRules;
use App\Livewire\Scopes\CreateScope;
use App\Livewire\Scopes\ShowScope;
use App\Livewire\Scopes\UpdateScope;
use App\Livewire\SiaRules\ShowSiaRule;
use App\Livewire\SiaRules\ViewSiaRules;
use App\Livewire\SiteimproveRules\ViewSiteimproveRules;
use App\Livewire\Teams\Manage;
use App\Livewire\Teams\ShowTeam;
use App\Models\Project;
use App\Models\Team;
use App\Services\GoogleApi\GoogleService;
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\AppTesters;
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\CUAuth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('welcome');

Route::group(['middleware' => [CUAuth::class]], function () {
    Route::get('login', fn() => redirect()->route('cu-auth.sso-login'))->name('login');
    Route::get('logout', fn() => redirect()->route('cu-auth.sso-logout'))->name('logout');
});

Route::group(['middleware' => [AppTesters::class]], function () {
    Route::get('/help', fn () => view('help'))->name('help');
    Route::get('/updates', fn () => view('updates'))->name('updates');

    Route::group(['middleware' => [CUAuth::class]], function () {
        Route::get('projects/', ViewProjects::class)->name('projects')->can('view-any', Project::class);
        Route::prefix('project')->name('project.')->group(function () {
            Route::get('/{project}', ShowProject::class)->name('show')->can('view', 'project');
            Route::get('/{project}/edit', UpdateProject::class)->name('edit')->can('update', 'project');
            Route::get('/{project}/scope/create', CreateScope::class)->name('scope.create')->can('update', 'project');
            Route::get('/{project}/issue/create', CreateProjectIssue::class)->name('issue.create')->can('update', 'project');
            Route::get('/{project}/report', Report::class)->name('report')->can('view', 'project');
            Route::get('/{project}/report/raw', function (Project $project) {
                return view('exports.project-report', [
                    'project' => $project,
                    'issues' => $project->getReportableIssues(),
                    'format' => 'raw',
                ]);
            })->name('report.raw')->can('view', 'project');
            Route::get('/{project}/report/google-export', function (Project $project, GoogleService $googleService) {
                $spreadsheetId = $googleService->createTestSheet();

                if (!$spreadsheetId) {
                    return redirect()->route('google.oauth', [
                        'target' => route('project.report.google-export', $project),
                    ]);
                }

                return redirect()->away('https://docs.google.com/spreadsheets/d/' . $spreadsheetId);
            })->name('report.google-export')->can('view', 'project');
        });

        Route::prefix('scope/{scope}')->name('scope.')->group(function () {
            Route::get('', ShowScope::class)->name('show')->can('view', 'scope');
            Route::get('/edit', UpdateScope::class)->name('edit')->can('update', 'scope');
            Route::get('/issue/create', CreateIssue::class)->name('issue.create')->can('update', 'scope');
            Route::get('/issue/siteimprove/create/{rule}/{guideline}', CreateSiteimproveIssue::class)->name('issue.siteimprove.create');
        });

        Route::prefix('issue/{issue}')->name('issue.')->group(function () {
            Route::get('', ShowIssue::class)->name('show')->can('view', 'issue');
            Route::get('/edit', UpdateIssue::class)->name('edit')->can('update', 'issue');
            Route::get('/item/create', CreateItem::class)->name('item.create')->can('update', 'issue');
            Route::get('/item/{item}/edit', UpdateItem::class)->name('item.edit')->can('update', 'issue');
        });

        Route::prefix('teams')->name('teams.')->group(function () {
            Route::get('', Manage::class)->name('manage')->can('view-any', Team::class);
            Route::get('{team}', ShowTeam::class)->name('show')->can('view', 'team');
            Route::get('{team}/project/create', CreateProject::class)->name('project.create')->can('create-projects', 'team');
        });

        Route::get('chat', Chat::class)->name('chat');
    });

    Route::prefix('guidelines')->name('guidelines.')->group(function () {
        Route::get('/', ViewGuidelines::class)->name('index');
        Route::get('/{guideline}', ShowGuideline::class)->name('show');
    });

    Route::prefix('criteria')->name('criteria.')->group(function () {
        Route::get('/', ViewCriteria::class)->name('index');
        Route::get('/{criterion}', ShowCriterion::class)->name('show');
    });

    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', ViewCategories::class)->name('index');
        Route::get('/{category}', ShowCategory::class)->name('show');
    });

//    Route::prefix('act-rules')->name('act-rules.')->group(function () {
//        Route::get('/', ViewRules::class)->name('index');
//        Route::get('/{rule}', ShowRule::class)->name('show');
//    });

    Route::prefix('siteimprove-rules')->name('siteimprove-rules.')->group(function () {
        Route::get('/', ViewSiteimproveRules::class)->name('index');
    });

    Route::prefix('sia-rules')->name('sia-rules.')->group(function () {
        Route::get('/', ViewSiaRules::class)->name('index');
        Route::get('/{rule}', ShowSiaRule::class)->name('show');
    });

    Route::get('guidelines.md', Doc::class)->name('guidelines.md');
    Route::get('analyze', AnalyzePage::class)->name('analyze');

});
