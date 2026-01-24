<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Request;
use Core\Response;

/**
 * Role-based Authorization Middleware
 * 
 * Usage in routes:
 * $router->get('/admin/users', [AdminController::class, 'index'])->middleware(['AuthMiddleware', 'RoleMiddleware:admin']);
 * $router->get('/users', [UserController::class, 'index'])->middleware(['AuthMiddleware', 'RoleMiddleware:admin,teacher']);
 */
class RoleMiddleware
{
    public function handle(Request $request, string $roles = ''): void
    {
        // Check if user is authenticated
        if (!$request->user) {
            $response = new Response();
            $response->json([
                'success' => false,
                'message' => 'Authentication required',
                'message_code' => 'UNAUTHORIZED'
            ], 401);
        }

        // If no specific roles required, just check authentication
        if (empty($roles)) {
            return;
        }

        // Parse allowed roles (comma-separated)
        $allowedRoles = array_map('trim', explode(',', $roles));

        // Get user role from JWT payload
        $userRole = $request->user->role ?? null;

        // Check if user has required role
        if (!in_array($userRole, $allowedRoles, true)) {
            $response = new Response();
            $response->json([
                'success' => false,
                'message' => 'You do not have permission to access this resource',
                'message_code' => 'FORBIDDEN'
            ], 403);
        }
    }
}
