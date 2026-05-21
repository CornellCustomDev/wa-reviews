<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Middleware;

use Closure;
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\IdentityManager;
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\Concerns\ChecksLocalLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Symfony\Component\HttpFoundation\Response;

class LivewireAuth
{
    use ChecksLocalLogin;

    public function __construct(
        protected IdentityManager $identityManager
    ) {}

    public static function requireLivewireAuth(): void
    {
        if (class_exists('Livewire\Livewire')) {
            Livewire::setUpdateRoute(function ($handle) {
                // Only logged in users can post data to livewire components
                return Route::post('/livewire/update', $handle)
                    ->middleware(['web', LivewireAuth::class]);
            });
        }
    }

    /**
     * Assure all Livewire updates are from authenticated users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isLoggedInLocally() || $this->identityManager->hasRemoteIdentity()) {
            return $next($request);
        }

        // This is a /livewire/update without a logged in user, so return forbidden.
        if (app()->runningInConsole()) {
            return response('Forbidden', Response::HTTP_FORBIDDEN);
        }
        abort(403);
    }
}
