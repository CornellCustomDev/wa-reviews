<?php

namespace App\Middleware;

use Closure;
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\IdentityManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LivewireAuth
{
    public function __construct(
        protected IdentityManager $identityManager
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // If this is /livewire/update without a logged in user, return forbidden
        if ($request->isMethod('POST') && $request->getRequestUri() === '/livewire/update') {
            if (! $this->identityManager->hasIdentity()) {
                if (app()->runningInConsole()) {
                    return response('Forbidden', Response::HTTP_FORBIDDEN);
                }
                abort(403);
            }
        }

        return $next($request);
    }
}
