<?php

use App\Livewire\Projects\CreateProject;
use App\Livewire\Projects\ShowProject;
use App\Livewire\Projects\UpdateProject;
use App\Livewire\Projects\ViewProjects;
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
});

Route::prefix('projects')->name('projects.')->group(function () {
    Route::get('/', ViewProjects::class)->name('index')->can('viewAny', Project::class);
    Route::get('/create', CreateProject::class)->name('create')->can('create', Project::class);
    Route::get('/{project}', ShowProject::class)->name('show')->can('view', 'project');
    Route::get('/{project}/edit', UpdateProject::class)->name('edit')->can('update', 'project');
});
