<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Passport\Token;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request,$next): Response
    {
        // Get Bearer token
        $token = $request->bearerToken();

        Log::info('Received Token:', ['token' => $token]); // Log the token for debugging

        if (!$token) {
            return response()->json(['message' => 'Unauthorized: No token provided'], 401);
        }

        // Check if token exists in Passport's `oauth_access_tokens` table
        $tokenExists = Token::where('id', $token)->where('revoked', false)->exists();

        Log::info('Token Exists:', ['exists' => $tokenExists]); // Log if token is found

        if (!$tokenExists) {
            return response()->json(['message' => 'Unauthorized: Invalid or revoked token'], 401);
        }

        return $next($request);
    }
}
