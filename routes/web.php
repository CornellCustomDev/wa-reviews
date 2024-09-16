<?php

use App\Livewire\Categories\ShowCategory;
use App\Livewire\Categories\ViewCategories;
use App\Livewire\AI\Chat;
use App\Livewire\Criteria\ShowCriterion;
use App\Livewire\Criteria\ViewCriteria;
use App\Livewire\Guidelines\Doc;
use App\Livewire\Guidelines\ShowGuideline;
use App\Livewire\Guidelines\ViewGuidelines;
use App\Livewire\Projects\CreateProject;
use App\Livewire\Projects\ShowProject;
use App\Livewire\Projects\UpdateProject;
use App\Livewire\Projects\ViewProjects;
use App\Livewire\Items\CreateItem;
use App\Livewire\Items\UpdateItem;
use App\Livewire\Issues\CreateIssue;
use App\Livewire\Issues\ShowIssue;
use App\Livewire\Issues\UpdateIssue;
use App\Models\Project;
use App\Models\Issue;
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
});

Route::prefix('projects')->name('projects.')->group(function () {
    Route::get('/', ViewProjects::class)->name('index')->can('viewAny', Project::class);
    Route::get('/create', CreateProject::class)->name('create')->can('create', Project::class);
    Route::get('/{project}', ShowProject::class)->name('show')->can('view', 'project');
    Route::get('/{project}/edit', UpdateProject::class)->name('edit')->can('update', 'project');
});

Route::prefix('projects/{project}/issues')->name('issues.')->group(function () {
    Route::get('/', fn($project) => redirect()->route('projects.show', $project))->name('index');
    Route::get('/create', CreateIssue::class)->name('create')->can('create', [Issue::class, 'project']);
    Route::get('/{issue}', ShowIssue::class)->name('show')->can('view', 'issue');
    Route::get('/{issue}/edit', UpdateIssue::class)->name('edit')->can('update', 'issue');
});

Route::prefix('projects/{project}/issues/{issue}/items')->name('items.')->group(function () {
    Route::get('/create', CreateItem::class)->name('create'); //->can('update', [issue::class, 'project']);
    Route::get('/{item}/edit', UpdateItem::class)->name('edit'); //->can('update', [issue::class, 'project']);
});

Route::prefix('guidelines')->name('guidelines.')->group(function () {
    Route::get('/', ViewGuidelines::class)->name('index');
    Route::get('/{guideline}', ShowGuideline::class)->name('show');
//    Route::get('/{guideline}/edit', UpdateGuideline::class)->name('edit')->can('update', 'guideline');
});

Route::prefix('criteria')->name('criteria.')->group(function () {
    Route::get('/', ViewCriteria::class)->name('index');
    Route::get('/{criterion}', ShowCriterion::class)->name('show');
});

Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/', ViewCategories::class)->name('index');
    Route::get('/{category}', ShowCategory::class)->name('show');
});

// Livewire route
Route::get('chat', Chat::class)->name('chat');
Route::get('guidelines.md', Doc::class)->name('guidelines.md');
