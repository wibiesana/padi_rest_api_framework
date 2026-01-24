<?php

namespace App\Controllers\Base;

use Core\Controller;
use App\Models\PasswordReset;

class PasswordResetController extends Controller
{
    protected PasswordReset $model;

    public function __construct(?\Core\Request $request = null)
    {
        parent::__construct($request);
        $this->model = new PasswordReset();
    }

    /**
     * Get all passwordresets with pagination
     * GET /passwordresets
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
     * Get all passwordresets without pagination
     * GET /passwordresets/all
     */
    public function all(): void
    {
        $data = $this->model::findQuery()->all();
        $this->success(['data' => $data]);
    }

    /**
     * Get single passwordreset
     * GET /passwordresets/{id}
     */
    public function show(): void
    {
        $id = $this->request->param('id');
        $passwordreset = $this->model->find($id);

        if (!$passwordreset) {
            $this->notFound('PasswordReset not found');
        }

        $this->success($passwordreset);
    }

    /**
     * Create new passwordreset
     * POST /passwordresets
     */
    public function store(): void
    {
        $validated = $this->validate([
            'email' => 'required|max:255|email',
            'token' => 'required|max:255',
            'expires_at' => 'required'
        ]);

        $id = $this->model->create($validated);

        $this->success([
            'id' => $id,
            'passwordreset' => $this->model->find($id)
        ], 'PasswordReset created successfully', 201);
    }

    /**
     * Update passwordreset
     * PUT /passwordresets/{id}
     */
    public function update(): void
    {
        $id = $this->request->param('id');
        $passwordreset = $this->model->find($id);

        if (!$passwordreset) {
            $this->notFound('PasswordReset not found');
        }

        $validated = $this->validate([
            'email' => 'required|max:255|email',
            'token' => 'required|max:255',
            'expires_at' => 'required'
        ]);

        $this->model->update($id, $validated);

        $this->success(
            $this->model->find($id),
            'PasswordReset updated successfully'
        );
    }

    /**
     * Delete passwordreset
     * DELETE /passwordresets/{id}
     */
    public function destroy(): void
    {
        $id = $this->request->param('id');
        $passwordreset = $this->model->find($id);

        if (!$passwordreset) {
            $this->notFound('PasswordReset not found');
        }

        $this->model->delete($id);

        $this->success(null, 'PasswordReset deleted successfully');
    }
}
