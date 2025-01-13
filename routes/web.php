<?php

use App\Livewire\Ai\AnalyzePage;
use App\Livewire\Ai\Prompt;
use App\Livewire\Categories\ShowCategory;
use App\Livewire\Categories\ViewCategories;
use App\Livewire\Ai\Chat;
use App\Livewire\Criteria\ShowCriterion;
use App\Livewire\Criteria\ViewCriteria;
use App\Livewire\Guidelines\Doc;
use App\Livewire\Guidelines\ShowGuideline;
use App\Livewire\Guidelines\ViewGuidelines;
use App\Livewire\Issues\CreateProjectIssue;
use App\Livewire\Projects\CreateProject;
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
use App\Livewire\SiteimproveRules\ViewSiteimproveRules;
use App\Models\Project;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('projects/', ViewProjects::class)->name('projects')->can('viewAny', Project::class);
Route::prefix('project')->name('project.')->group(function () {
    Route::get('/create', CreateProject::class)->name('create')->can('create', Project::class);
    Route::get('/{project}', ShowProject::class)->name('show')->can('view', 'project');
    Route::get('/{project}/edit', UpdateProject::class)->name('edit')->can('update', 'project');
    Route::get('/{project}/scope/create', CreateScope::class)->name('scope.create')->can('update', 'project');
    Route::get('/{project}/issue/create', CreateProjectIssue::class)->name('issue.create')->can('update', 'project');
});

Route::prefix('scope/{scope}')->name('scope.')->group(function () {
    Route::get('', ShowScope::class)->name('show')->can('view', 'scope');
    Route::get('/edit', UpdateScope::class)->name('edit')->can('update', 'scope');
    Route::get('/issue/create', CreateIssue::class)->name('issue.create')->can('update', 'scope');
});

Route::prefix('issue/{issue}')->name('issue.')->group(function () {
    Route::get('', ShowIssue::class)->name('show')->can('view', 'issue');
    Route::get('/edit', UpdateIssue::class)->name('edit')->can('update', 'issue');
    Route::get('/item/create', CreateItem::class)->name('item.create')->can('update', 'issue');
    Route::get('/item/{item}/edit', UpdateItem::class)->name('item.edit')->can('update', 'issue');
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

Route::prefix('act-rules')->name('act-rules.')->group(function () {
    Route::get('/', ViewRules::class)->name('index');
    Route::get('/{rule}', ShowRule::class)->name('show');
});

Route::prefix('siteimprove-rules')->name('siteimprove-rules.')->group(function () {
    Route::get('/', ViewSiteimproveRules::class)->name('index');
});

// Livewire route
Route::get('chat', Chat::class)->name('chat');
Route::get('guidelines.md', Doc::class)->name('guidelines.md');
Route::get('prompt', Prompt::class)->name('prompt');
Route::get('analyze', AnalyzePage::class)->name('analyze');
