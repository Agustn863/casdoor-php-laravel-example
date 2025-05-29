<?php

namespace App\Http\Controllers;

use App\Services\CasdoorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    private string $clientId;
    private string $clientSecret;
    private string $casdoorEndpoint = 'http://localhost:8000';
    private CasdoorService $casdoorService;

    public function __construct(CasdoorService $casdoorService)
    {
        $this->clientId = env('CASDOOR_CLIENT_ID', '8d14d972d494e2358dfa');
        $this->clientSecret = env('CASDOOR_CLIENT_SECRET', 'de88208dcd6d6eac42c7cce8be61104c15f22e30');
        $this->casdoorService = $casdoorService;
    }

    public function redirectToCasdoor()
    {
        $state = bin2hex(random_bytes(16));

        $query = http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => 'https://web_development_lab3.test/callback',
            'state' => $state,
            'scope' => 'read'
        ]);

        return redirect($this->casdoorEndpoint . '/login/oauth/authorize?' . $query);
    }

    public function callback(Request $request)
    {
        $code = $request->query('code');

        try {
            $token = $this->casdoorService->getOAuthToken($code);

            $tokenPart1 = substr($token, 0, strlen($token) / 2);
            $tokenPart2 = substr($token, strlen($token) / 2);

            $encodedTokenPart1 = base64_encode($tokenPart1);
            $encodedTokenPart2 = base64_encode($tokenPart2);

            $cookie1 = cookie('access_token_1', $encodedTokenPart1, 60, '/', null, true, true);
            $cookie2 = cookie('access_token_2', $encodedTokenPart2, 60, '/', null, true, true);

            Cookie::queue($cookie1);
            Cookie::queue($cookie2);

            return redirect('/')->withCookie($cookie1)->withCookie($cookie2);
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Authentication failed');
        }
    }

    public function getUserInfo(Request $request)
    {
        $encodedTokenPart1 = $request->cookie('access_token_1');
        $encodedTokenPart2 = $request->cookie('access_token_2');

        if (empty($encodedTokenPart1) || empty($encodedTokenPart2)) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $token = base64_decode($encodedTokenPart1) . base64_decode($encodedTokenPart2);

        $response = Http::withToken($token)
            ->get($this->casdoorEndpoint . '/api/get-account');

        if ($response->successful()) {
            $user = $response->json();
            return response()->json([
                'username' => $user['name'] ?? null,
                'displayName' => $user['data']['displayName'] ?? null,
                'email' => $user['data']['email'] ?? null,
            ]);
        }

        return response()->json(['error' => 'Failed to fetch user info'], 400);
    }

    public function logout()
    {
        Cookie::queue(Cookie::forget('access_token_1'));
        Cookie::queue(Cookie::forget('access_token_2'));
        return redirect('/');
    }
}
