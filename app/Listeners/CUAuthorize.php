<?php

namespace App\Listeners;

use App\Models\User;
use CornellCustomDev\LaravelStarterKit\CUAuth\Events\CUAuthenticated;
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\IdentityManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CUAuthorize
{
    public function __construct(
        protected IdentityManager $identityManager
    ) {}

    public function handle(CUAuthenticated $event): void
    {
        $remoteIdentity = $this->identityManager->getIdentity();
        $netid = $event->remoteUser;
        $email = $remoteIdentity->email() ?: $netid . '@cornell.edu';

        // Look for a matching user.
        $user = User::firstWhere('email', $email);

        if (empty($user)) {
            // User does not exist, so create them.
            $user = new User;
            $user->name = $remoteIdentity->name() ?: $netid;
            $user->email = $email;
            $user->password = Str::random(32);
            $user->save();
            Log::info("AuthorizeUser: Created user $user->email with ID $user->id.");
        }

        auth()->login($user);
        Log::info("AuthorizeUser: Logged in user $user->email.");
    }
}
