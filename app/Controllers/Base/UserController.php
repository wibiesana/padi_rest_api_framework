<?php

namespace App\Controllers\Base;

use Core\Controller;
use App\Models\User;

class UserController extends Controller
{
    protected User $model;

    public function __construct(?\Core\Request $request = null)
    {
        parent::__construct($request);
        $this->model = new User();
    }

    /**
     * Get all users with pagination
     * GET /users
     */
    public function index(): void
    {
        $page = max(1, (int)$this->request->query('page', 1)); // Min page 1
        $perPage = min(100, max(1, (int)$this->request->query('per_page', 10))); // Max 100 per page
        $search = $this->request->query('search');

        if ($search) {
            // Limit search query length to prevent abuse
            $search = substr($search, 0, 255);
            $data = $this->model->search($search);
            $this->success(['data' => $data]);
            return;
        }

        $result = $this->model->paginate($page, $perPage);
        $this->success($result);
    }

    /**
     * Get all users without pagination
     * GET /users/all
     */
    public function all(): void
    {
        $data = $this->model::findQuery()->all();
        $this->success(['data' => $data]);
    }

    /**
     * Get single user
     * GET /users/{id}
     */
    public function show(): void
    {
        $id = $this->request->param('id');
        $user = $this->model->find($id);

        if (!$user) {
            $this->notFound('User not found');
        }

        $this->success($user);
    }

    /**
     * Create new user
     * POST /users
     */
    public function store(): void
    {
        // Authorization: Only admin can create users
        if (!$this->request->user || $this->request->user->role !== 'admin') {
            $this->forbidden('Only administrators can create users');
        }

        $validated = $this->validate([
            'username' => 'max:50|unique:users,username',
            'email' => 'required|max:255|email|unique:users,email',
            'password' => 'required|max:255',
            'role' => 'max:50',
            'status' => 'max:20'
        ]);

        // Remove sensitive fields that should not be set by user input
        unset($validated['email_verified_at'], $validated['remember_token']);

        $id = $this->model->create($validated);

        $this->success([
            'id' => $id,
            'user' => $this->model->find($id)
        ], 'User created successfully', 201);
    }

    /**
     * Update user
     * PUT /users/{id}
     */
    public function update(): void
    {
        $id = $this->request->param('id');
        $user = $this->model->find($id);

        if (!$user) {
            $this->notFound('User not found');
        }

        // Authorization: Users can only update their own profile, admins can update anyone
        if (!$this->request->user) {
            $this->unauthorized('Authentication required');
        }

        $isAdmin = $this->request->user->role === 'admin';
        $isOwnProfile = $this->request->user->user_id == $id;

        if (!$isAdmin && !$isOwnProfile) {
            $this->forbidden('You can only update your own profile');
        }

        $validated = $this->validate([
            'username' => 'max:50|unique:users,username,' . $id,
            'email' => 'max:255|email|unique:users,email,' . $id,
            'password' => 'max:255', // Optional on update
            'role' => 'max:50',
            'status' => 'max:20'
        ]);

        // Non-admin users cannot change role or status
        if (!$isAdmin) {
            unset($validated['role'], $validated['status']);
        }

        // Remove sensitive fields that should not be set by user input
        unset($validated['email_verified_at'], $validated['remember_token']);

        // Remove password if empty (optional update)
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $this->model->update($id, $validated);

        $this->success(
            $this->model->find($id),
            'User updated successfully'
        );
    }

    /**
     * Delete user
     * DELETE /users/{id}
     */
    public function destroy(): void
    {
        // Authorization: Only admin can delete users
        if (!$this->request->user || $this->request->user->role !== 'admin') {
            $this->forbidden('Only administrators can delete users');
        }

        $id = $this->request->param('id');
        $user = $this->model->find($id);

        if (!$user) {
            $this->notFound('User not found');
        }

        // Prevent deleting yourself
        if ($this->request->user->user_id == $id) {
            $this->error('You cannot delete your own account', 400);
        }

        $this->model->delete($id);

        $this->success(null, 'User deleted successfully');
    }
}
