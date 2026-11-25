<?php

use CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers\AuthController;
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\AppTesters;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

// AppTesters is excluded so that anyone can log in or out when it is applied to the web group.
Route::group(['middleware' => ['web'], 'excluded_middleware' => [AppTesters::class]], function () {
    Route::get('/sso/login', [AuthController::class, 'login'])->name('cu-auth.sso-login');
    Route::get('/sso/select', [AuthController::class, 'select'])->name('cu-auth.sso-select');
    Route::get('/sso/logout', [AuthController::class, 'logout'])->name('cu-auth.sso-logout');
    Route::get('/sso/metadata', [AuthController::class, 'metadata'])->name('cu-auth.sso-metadata');
    // The IdP posts to sso/acs without a CSRF token. Laravel 13 uses PreventRequestForgery; the deprecated
    // VerifyCsrfToken is what matches on Laravel 12, so remove it when Laravel 12 support is dropped.
    Route::match(['get', 'post'], '/sso/acs', [AuthController::class, 'acs'])->name('cu-auth.sso-acs')
        ->withoutMiddleware([VerifyCsrfToken::class, PreventRequestForgery::class]);

    // Legacy
    Route::get('/shibboleth-login', [AuthController::class, 'login'])->name('cu-auth.shibboleth-login');
    Route::get('/shibboleth-logout', [AuthController::class, 'logout'])->name('cu-auth.shibboleth-logout');
});
