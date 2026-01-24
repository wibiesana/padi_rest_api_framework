<?php

namespace App\Controllers\Base;

use Core\Controller;
use App\Models\Job;

class JobController extends Controller
{
    protected Job $model;
    
    public function __construct(?\Core\Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Job();
    }
    
    /**
     * Get all jobs with pagination
     * GET /jobs
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
     * Get all jobs without pagination
     * GET /jobs/all
     */
    public function all(): void
    {
        $data = $this->model::findQuery()->all();
        $this->success(['data' => $data]);
    }
    
    /**
     * Get single job
     * GET /jobs/{id}
     */
    public function show(): void
    {
        $id = $this->request->param('id');
        $job = $this->model->find($id);
        
        if (!$job) {
            $this->notFound('Job not found');
        }
        
        $this->success($job);
    }
    
    /**
     * Create new job
     * POST /jobs
     */
    public function store(): void
    {
        $validated = $this->validate([
            'queue' => 'required|max:255',
            'payload' => 'required',
            'attempts' => 'integer',
            'reserved_at' => 'integer',
            'available_at' => 'required|integer'
        ]);
        
        $id = $this->model->create($validated);
        
        $this->success([
            'id' => $id,
            'job' => $this->model->find($id)
        ], 'Job created successfully', 201);
    }
    
    /**
     * Update job
     * PUT /jobs/{id}
     */
    public function update(): void
    {
        $id = $this->request->param('id');
        $job = $this->model->find($id);
        
        if (!$job) {
            $this->notFound('Job not found');
        }
        
        $validated = $this->validate([
            'queue' => 'required|max:255',
            'payload' => 'required',
            'attempts' => 'integer',
            'reserved_at' => 'integer',
            'available_at' => 'required|integer'
        ]);
        
        $this->model->update($id, $validated);
        
        $this->success(
            $this->model->find($id),
            'Job updated successfully'
        );
    }
    
    /**
     * Delete job
     * DELETE /jobs/{id}
     */
    public function destroy(): void
    {
        $id = $this->request->param('id');
        $job = $this->model->find($id);
        
        if (!$job) {
            $this->notFound('Job not found');
        }
        
        $this->model->delete($id);
        
        $this->success(null, 'Job deleted successfully');
    }
}
