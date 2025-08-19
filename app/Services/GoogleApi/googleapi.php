<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google API Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | See https://github.com/googleapis/google-api-php-client/blob/main/docs/oauth-web.md#create-authorization-credentials
    |
    | The client ID and secret can be found in the JSON credentials file.
    | Download this file from the Google Cloud Console after creating your
    | OAuth credentials.
    |
    */
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Google API Application Configuration
    |--------------------------------------------------------------------------
    */
    'scopes' => [
        Google\Service\Sheets::SPREADSHEETS,
        Google\Service\Drive::DRIVE_FILE,
    ],
];
