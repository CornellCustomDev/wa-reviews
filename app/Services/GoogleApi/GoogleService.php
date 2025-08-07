<?php

namespace App\Services\GoogleApi;

use App\Models\GoogleToken;
use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\Spreadsheet;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Facades\Auth;
use function route;

class GoogleService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();

        $this->client->setClientId(config('googleapi.client_id'));
        $this->client->setClientSecret(config('googleapi.client_secret'));
        $this->client->setScopes(config('googleapi.scopes'));

        $this->client->setRedirectUri(route('google.oauth.callback'));

//        $client->setApplicationName(config('app.name'));
//        $client->setAccessType('offline');
//        $client->setPrompt('consent');
    }

    public function getAuthUrl(?string $target = null): string
    {
        $queryParams = [];
        if ($target) {
            // If a target URL is provided, use it for the redirect after authorization
            $queryParams['redirect_uri'] = route(
                name: 'google.oauth.callback',
                parameters: ['target' => urlencode($target)]
            );
        }

        return $this->client->createAuthUrl(queryParams: $queryParams);
    }

    public function setAuthCode(string $code): void
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        GoogleToken::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? null,
                'expires_in' => $token['expires_in'] ?? null,
                'created_at_token' => now(),
            ]
        );
    }

    private function refreshUserToken(): void
    {
        // If we have a stored token for this user
        $tokenRecord = GoogleToken::firstWhere('user_id', Auth::id());

        if ($tokenRecord) {
            $this->client->setAccessToken([
                'access_token' => $tokenRecord->access_token,
                'refresh_token' => $tokenRecord->refresh_token,
                'expires_in' => $tokenRecord->expires_in,
                'created' => $tokenRecord->created_at_token?->timestamp ?? time(),
            ]);

            if ($this->client->isAccessTokenExpired()) {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                $tokenRecord->update([
                    'access_token' => $newToken['access_token'],
                    'expires_in' => $newToken['expires_in'] ?? null,
                    'created_at_token' => now(),
                ]);
                $this->client->setAccessToken($newToken);
            }
        }
    }

    public function createTestSheet(): ?string
    {
        self::refreshUserToken();

        if (!$this->client->getAccessToken() || $this->client->isAccessTokenExpired()) {
            // Should we trigger google oauth flow here?
            return null;
        }

        $service = new Sheets($this->client);

        $spreadsheet = new Spreadsheet([
            'properties' => ['title' => 'Test Export'],
        ]);
        $spreadsheet = $service->spreadsheets->create($spreadsheet);

        // Write data
        $range = 'Sheet1!A1';
        $values = [['Hello World']];
        $body = new ValueRange(['values' => $values]);
        $service->spreadsheets_values->update($spreadsheet->spreadsheetId, $range, $body, ['valueInputOption' => 'RAW']);

        return $spreadsheet->spreadsheetId;
    }
}
