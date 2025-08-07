<?php

use App\Services\GoogleApi\Controllers\GoogleController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], function () {
    Route::get('/google/oauth', [GoogleController::class, 'redirect'])->name('google.oauth');
    Route::get('/google/oauth/callback', [GoogleController::class, 'callback'])->name('google.oauth.callback');
});
