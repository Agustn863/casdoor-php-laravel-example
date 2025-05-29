<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CasdoorService
{
    private string $clientId;
    private string $clientSecret;
    private string $casdoorEndpoint;

    public function __construct()
    {
        $this->clientId = env('CASDOOR_CLIENT_ID', '8d14d972d494e2358dfa');
        $this->clientSecret = env('CASDOOR_CLIENT_SECRET', 'de88208dcd6d6eac42c7cce8be61104c15f22e30');
        $this->casdoorEndpoint = 'http://localhost:8000';
    }

    public function getOAuthToken(string $code): string
    {
        $response = Http::post($this->casdoorEndpoint . '/api/login/oauth/access_token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => 'https://lab3-web-casdoor-auth.test/callback'
        ]);

        Log::info('Token response', ['response' => $response->json()]);

        if (!$response->successful()) {
            Log::error('Failed to get OAuth token', ['response' => $response->json()]);
            throw new \Exception('Failed to get OAuth token');
        }

        return $response->json()['access_token'];
    }
}
