<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Request;
use Core\Response;

class AuthMiddleware
{
    public function handle(Request $request): void
    {
        $token = $request->bearerToken();

        if (!$token) {
            $response = new Response();
            $response->json([
                'success' => false,
                'message' => 'Unauthorized - No token provided'
            ], 401);
        }

        $decoded = \Core\Auth::verifyToken($token);

        if (!$decoded) {
            $response = new Response();
            $response->json([
                'success' => false,
                'message' => 'Unauthorized - Invalid or expired token'
            ], 401);
        }

        // Attach user info to request for later use
        $request->user = $decoded;
    }
}
