<?php

namespace App\Listeners;

use App\Models\User;
use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\RemoteIdentity;
use CornellCustomDev\LaravelStarterKit\CUAuth\Events\CUAuthenticated;
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\IdentityManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CUAuthorize
{
    public function __construct(
        protected IdentityManager $identityManager
    ) {}

    public function handle(CUAuthenticated $event, ?RemoteIdentity $remoteIdentity = null): void
    {
        $remoteIdentity ??= $this->identityManager->getIdentity();

        // Look for a matching user.
        $user = User::whereIn('email', array_filter([
            $remoteIdentity->primaryEmail(),
            // This allows to log in with an old emailAlias email if someone has an account with that
            $remoteIdentity->emailAlias() ?: null,
        ]))->first();

        if (empty($user)) {
            // User does not exist, so create them.
            $user = new User;
            $user->name = $remoteIdentity->name() ?: $remoteIdentity->id();
            $user->email = $remoteIdentity->primaryEmail();
            $user->uid = $remoteIdentity->id();
            $user->password = Str::random(32);
            $user->save();
            Log::info("CUAuthorize: Created user $user->email with ID $user->id.");
        }

        auth()->login($user);
        Log::info("CUAuthorize: Logged in user $user->email.");
    }
}
