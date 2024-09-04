<?php

use App\Livewire\Categories\ShowCategory;
use App\Livewire\Categories\ViewCategories;
use App\Livewire\Chat;
use App\Livewire\Guidelines\ShowGuideline;
use App\Livewire\Guidelines\ViewGuidelines;
use App\Livewire\Projects\CreateProject;
use App\Livewire\Projects\ShowProject;
use App\Livewire\Projects\UpdateProject;
use App\Livewire\Projects\ViewProjects;
use App\Livewire\ReviewItems\CreateItem;
use App\Livewire\ReviewItems\UpdateItem;
use App\Livewire\Reviews\CreateReview;
use App\Livewire\Reviews\ShowReview;
use App\Livewire\Reviews\UpdateReview;
use App\Livewire\Reviews\ViewReviews;
use App\Models\Project;
use App\Models\Review;
use App\Models\ReviewItem;
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

Route::prefix('projects/{project}/reviews')->name('reviews.')->group(function () {
    Route::get('/', ViewReviews::class)->name('index')->can('viewAny', [Review::class, 'project']);
    Route::get('/create', CreateReview::class)->name('create')->can('create', [Review::class, 'project']);
    Route::get('/{review}', ShowReview::class)->name('show')->can('view', 'review');
    Route::get('/{review}/edit', UpdateReview::class)->name('edit')->can('update', 'review');
});

// Review Items
Route::prefix('projects/{project}/reviews/{review}/items')->name('review-items.')->group(function () {
    Route::get('/create', CreateItem::class)->name('create'); //->can('update', [Review::class, 'project']);
    Route::get('/{reviewItem}/edit', UpdateItem::class)->name('edit'); //->can('update', [Review::class, 'project']);
});

Route::prefix('guidelines')->name('guidelines.')->group(function () {
    Route::get('/', ViewGuidelines::class)->name('index');
    Route::get('/{guideline}', ShowGuideline::class)->name('show');
//    Route::get('/{guideline}/edit', UpdateGuideline::class)->name('edit')->can('update', 'guideline');
});

Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/', ViewCategories::class)->name('index');
    Route::get('/{category}', ShowCategory::class)->name('show');
});

// Livewire route
Route::get('/chat', Chat::class)->name('chat');
