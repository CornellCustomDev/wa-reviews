<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Middleware;

use Closure;
use CornellCustomDev\LaravelStarterKit\CUAuth\Events\CUAuthenticated;
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\IdentityManager;
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\Concerns\ChecksLocalLogin;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CUAuth
{
    use ChecksLocalLogin;

    public function __construct(
        protected IdentityManager $identityManager
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // If local login is allowed and someone is authenticated, let them through.
        if ($this->isLoggedInLocally()) {
            return $next($request);
        }

        // Make sure we are using HTTPS.
        if (! $request->secure()) {
            return redirect()->secure($request->getRequestUri());
        }

        // SSO routes need to pass through so the identity manager can process the SSO responses.
        $passThrough = in_array($request->path(), [
            route('cu-auth.sso-login'),
            route('cu-auth.sso-logout'),
            route('cu-auth.sso-acs'),
            route('cu-auth.sso-metadata'),
        ]);
        if ($passThrough) {
            return $next($request);
        }

        // If we don't have a remote identity, redirect to the SSO login route.
        if (! $this->identityManager->hasRemoteIdentity()) {
            return redirect()->route('cu-auth.sso-login', ['redirect_url' => $request->fullUrl()]);
        }

        // If requiring a local user, attempt to log in the user.
        if (config('cu-auth.require_local_user') && ! auth()->check()) {
            event(new CUAuthenticated);

            // If the authenticated user is still not logged in, return a 403.
            if (! auth()->check()) {
                if (app()->runningInConsole()) {
                    return response('Forbidden', Response::HTTP_FORBIDDEN);
                }
                abort(403);
            }
        }

        return $next($request);
    }
}
