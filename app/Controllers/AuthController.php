<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    private User $model;

    public function __construct(?\Core\Request $request = null)
    {
        parent::__construct($request);
        $this->model = new User();
    }

    /**
     * Register new user
     * POST /api/auth/register
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => 'required|min:3|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'password_confirmation' => 'required'
        ]);

        // Additional password complexity validation
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]/', $validated['password'])) {
            $this->error('Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&#)', 422);
        }

        // Check password confirmation
        if ($validated['password'] !== $validated['password_confirmation']) {
            $this->error('Password confirmation does not match', 422);
        }

        unset($validated['password_confirmation']);
        $validated['role'] = 'user';

        $userId = $this->model->createUser($validated);
        $user = $this->model->find($userId);

        $token = \Core\Auth::generateToken([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);

        // Push Welcome Email Job to Queue
        \Core\Queue::push(\App\Jobs\SendEmailJob::class, [
            'email' => $user['email'],
            'subject' => 'Welcome to ' . ($_ENV['APP_NAME'] ?? 'Our API'),
            'body' => 'Thank you for registering!'
        ]);

        $this->success([
            'id' => $user['id'],
            'user_id' => $user['id'],
            'user' => $user,
            'token' => $token
        ], 'Registration successful. Welcome email will be sent shortly.', 201);
    }

    /**
     * Login user
     * POST /api/auth/login
     */
    public function login(): void
    {
        $validated = $this->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Find active user only
        $user = $this->model->findActiveByEmail($validated['email']);

        if (!$user) {
            $this->unauthorized('Invalid credentials or account is inactive');
        }

        // Get user with password for verification
        $stmt = $this->model->query(
            "SELECT * FROM users WHERE email = :email",
            ['email' => $validated['email']]
        );

        $userWithPassword = $stmt[0] ?? null;

        if (!$userWithPassword || !password_verify($validated['password'], $userWithPassword['password'])) {
            $this->unauthorized('Invalid credentials');
        }

        // Check user status
        if (!$this->model->isActive($userWithPassword)) {
            $this->unauthorized('Your account is inactive. Please contact support.');
        }

        // Update last login
        $this->model->updateLastLogin($user['id']);

        $token = \Core\Auth::generateToken([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'status' => $user['status']
        ]);

        // Refresh user data with updated last_login_at
        $user = $this->model->find($user['id']);

        $this->success([
            'user' => $user,
            'token' => $token
        ], 'Login successful');
    }

    /**
     * Get authenticated user
     * GET /api/auth/me
     */
    public function me(): void
    {
        if (!$this->request->user) {
            $this->unauthorized('Not authenticated');
        }

        $this->success([
            'user' => $this->request->user
        ]);
    }

    /**
     * Logout user
     * POST /api/auth/logout
     */
    public function logout(): void
    {
        // In a stateless JWT system, logout is typically handled client-side
        // You can implement token blacklisting here if needed

        $this->success(null, 'Logout successful');
    }
}
