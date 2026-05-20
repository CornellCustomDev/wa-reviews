<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\Concerns;

trait ChecksLocalLogin
{
    protected function isLoggedInLocally(): bool
    {
        return config('cu-auth.allow_local_login') && auth()->check();
    }
}
