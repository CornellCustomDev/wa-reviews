<?php

namespace App\Listeners;

use App\Models\User;
use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\ShibIdentity;
use CornellCustomDev\LaravelStarterKit\CUAuth\Events\CUAuthenticated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CUAuthorize
{
    public function handle(CUAuthenticated $event): void
    {
        $shibboleth = ShibIdentity::fromServerVars();
        $netid = $event->remoteUser;
        $email = $shibboleth->email() ?: $netid . '@cornell.edu';

        // Look for a matching user.
        $user = User::firstWhere('email', $email);

        if (empty($user)) {
            // User does not exist, so create them.
            $user = new User;
            $user->name = $shibboleth->name() ?: $netid;
            $user->email = $email;
            $user->password = Str::random(32);
            $user->save();
            Log::info("AuthorizeUser: Created user $user->email with ID $user->id.");
        }

        auth()->login($user);
        Log::info("AuthorizeUser: Logged in user $user->email.");
    }
}
