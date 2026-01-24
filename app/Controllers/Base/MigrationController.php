<?php

namespace App\Controllers\Base;

use Core\Controller;
use App\Models\Migration;

class MigrationController extends Controller
{
    protected Migration $model;

    public function __construct(?\Core\Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Migration();
    }

    /**
     * Get all migrations with pagination
     * GET /migrations
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
     * Get all migrations without pagination
     * GET /migrations/all
     */
    public function all(): void
    {
        $data = $this->model::findQuery()->all();
        $this->success(['data' => $data]);
    }

    /**
     * Get single migration
     * GET /migrations/{id}
     */
    public function show(): void
    {
        $id = $this->request->param('id');
        $migration = $this->model->find($id);

        if (!$migration) {
            $this->notFound('Migration not found');
        }

        $this->success($migration);
    }

    /**
     * Create new migration
     * POST /migrations
     */
    public function store(): void
    {
        $validated = $this->validate([
            'migration' => 'required|max:255',
            'batch' => 'required|integer'
        ]);

        $id = $this->model->create($validated);

        $this->success([
            'id' => $id,
            'migration' => $this->model->find($id)
        ], 'Migration created successfully', 201);
    }

    /**
     * Update migration
     * PUT /migrations/{id}
     */
    public function update(): void
    {
        $id = $this->request->param('id');
        $migration = $this->model->find($id);

        if (!$migration) {
            $this->notFound('Migration not found');
        }

        $validated = $this->validate([
            'migration' => 'required|max:255',
            'batch' => 'required|integer'
        ]);

        $this->model->update($id, $validated);

        $this->success(
            $this->model->find($id),
            'Migration updated successfully'
        );
    }

    /**
     * Delete migration
     * DELETE /migrations/{id}
     */
    public function destroy(): void
    {
        $id = $this->request->param('id');
        $migration = $this->model->find($id);

        if (!$migration) {
            $this->notFound('Migration not found');
        }

        $this->model->delete($id);

        $this->success(null, 'Migration deleted successfully');
    }
}
