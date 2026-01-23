<?php

namespace App\Controllers\Base;

use Core\Controller;
use App\Models\PostTag;

class PostTagController extends Controller
{
    protected PostTag $model;
    
    public function __construct(?\Core\Request $request = null)
    {
        parent::__construct($request);
        $this->model = new PostTag();
    }
    
    /**
     * Get all posttags with pagination
     * GET /posttags
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
     * Get all posttags without pagination
     * GET /posttags/all
     */
    public function all(): void
    {
        $data = $this->model::findQuery()->all();
        $this->success(['data' => $data]);
    }
    
    /**
     * Get single posttag
     * GET /posttags/{id}
     */
    public function show(): void
    {
        $id = $this->request->param('id');
        $posttag = $this->model->find($id);
        
        if (!$posttag) {
            $this->notFound('PostTag not found');
        }
        
        $this->success($posttag);
    }
    
    /**
     * Create new posttag
     * POST /posttags
     */
    public function store(): void
    {
        $validated = $this->validate([
            'post_id' => 'required|integer',
            'tag_id' => 'required|integer'
        ]);
        
        $id = $this->model->create($validated);
        
        $this->success([
            'id' => $id,
            'posttag' => $this->model->find($id)
        ], 'PostTag created successfully', 201);
    }
    
    /**
     * Update posttag
     * PUT /posttags/{id}
     */
    public function update(): void
    {
        $id = $this->request->param('id');
        $posttag = $this->model->find($id);
        
        if (!$posttag) {
            $this->notFound('PostTag not found');
        }
        
        $validated = $this->validate([
            'post_id' => 'required|integer',
            'tag_id' => 'required|integer'
        ]);
        
        $this->model->update($id, $validated);
        
        $this->success(
            $this->model->find($id),
            'PostTag updated successfully'
        );
    }
    
    /**
     * Delete posttag
     * DELETE /posttags/{id}
     */
    public function destroy(): void
    {
        $id = $this->request->param('id');
        $posttag = $this->model->find($id);
        
        if (!$posttag) {
            $this->notFound('PostTag not found');
        }
        
        $this->model->delete($id);
        
        $this->success(null, 'PostTag deleted successfully');
    }
}
