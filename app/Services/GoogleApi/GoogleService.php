<?php

namespace App\Services\GoogleApi;

use App\Models\GoogleToken;
use Exception;
use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Auth;
use function route;

class GoogleService
{
    private Client $client;
    private bool $tokenLoaded = false;

    public function __construct()
    {
        $this->client = new Client();

        $this->client->setClientId(config('googleapi.client_id'));
        $this->client->setClientSecret(config('googleapi.client_secret'));
        $this->client->setScopes(config('googleapi.scopes'));
        $this->client->setRedirectUri(route('google.oauth.callback'));

        $this->client->setApplicationName(config('app.name'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    public function getAuthUrl(?string $target = null): string
    {
        if ($target) {
            $this->client->setState($target);
        }

        return $this->client->createAuthUrl();
    }

    public function setAuthCode(string $code): void
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new Exception('Google OAuth exchange failed: ' . $token['error']);
        }

        $userId = Auth::id();
        $existing = GoogleToken::firstWhere('user_id', $userId);

        $refreshToken = $token['refresh_token'] ?? $existing?->refresh_token;

        GoogleToken::updateOrCreate(
            ['user_id' => $userId],
            [
                'access_token' => $token['access_token'] ?? null,
                'refresh_token' => $refreshToken,
                'expires_in' => isset($token['expires_in']) ? (int)$token['expires_in'] : null,
                'created_at_token' => now(),
            ]
        );

        $this->tokenLoaded = false;
    }

    public function ensureAuthorized(): bool
    {
        // If the app session expired, we can't load a per-user token.
        if (!Auth::id()) {
            return false;
        }

        $this->loadUserToken();

        $token = $this->client->getAccessToken();
        if (empty($token) || empty($token['access_token'])) {
            return false;
        }

        // If expiry meta is missing, be conservative and try to refresh.
        if (empty($token['expires_in']) || empty($token['created'])) {
            return $this->refreshAccessTokenIfPossible();
        }

        if ($this->client->isAccessTokenExpired()) {
            return $this->refreshAccessTokenIfPossible();
        }

        return true;
    }

    /**
     * Lazily load the token from storage into the client.
     */
    private function loadUserToken(): void
    {
        if ($this->tokenLoaded) {
            return;
        }

        $tokenRecord = GoogleToken::firstWhere('user_id', Auth::id());

        if ($tokenRecord) {
            $this->client->setAccessToken([
                'access_token' => $tokenRecord->access_token,
                'refresh_token' => $tokenRecord->refresh_token,
                'expires_in' => $tokenRecord->expires_in,
                'created' => $tokenRecord->created_at_token?->timestamp ?? time(),
            ]);
        }

        $this->tokenLoaded = true;
    }

    private function refreshAccessTokenIfPossible(): bool
    {
        $refreshToken = $this->client->getRefreshToken();
        if (!$refreshToken) {
            return false;
        }

        $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

        if (isset($newToken['error'])) {
            // Log the refresh failure for security monitoring
            Log::warning('Google token refresh failed', [
                'user_id' => Auth::id(),
                'error' => $newToken['error'],
                'error_description' => $newToken['error_description'] ?? null,
            ]);
            // Clear stored tokens so the next call redirects to OAuth
            GoogleToken::where('user_id', Auth::id())->update([
                'access_token' => null,
                'expires_in' => null,
                'refresh_token' => null,
                'created_at_token' => null,
            ]);

            // Leave client in a clean state (empty array avoids invalid json token)
            $this->client->setAccessToken([]);

            return false;
        }

        GoogleToken::where('user_id', Auth::id())->update([
            'access_token' => $newToken['access_token'] ?? null,
            'expires_in' => isset($newToken['expires_in']) ? (int)$newToken['expires_in'] : null,
            'refresh_token' => $refreshToken,
            'created_at_token' => now(),
        ]);

        $newToken['refresh_token'] = $refreshToken;
        $newToken['created'] = time();
        $this->client->setAccessToken($newToken);

        return !$this->client->isAccessTokenExpired();
    }

    /**
     * @throws Exception
     */
    public function getSheetsService(): Sheets
    {
        if (!$this->ensureAuthorized()) {
            throw new Exception('Google API client is not authorized.');
        }

        return new Sheets($this->client);
    }
}
