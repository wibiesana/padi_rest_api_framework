<?php

namespace App\Controllers\Base;

use Core\Controller;
use App\Models\Tag;

class TagController extends Controller
{
    protected Tag $model;
    
    public function __construct(?\Core\Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Tag();
    }
    
    /**
     * Get all tags with pagination
     * GET /tags
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
     * Get all tags without pagination
     * GET /tags/all
     */
    public function all(): void
    {
        $data = $this->model::findQuery()->all();
        $this->success(['data' => $data]);
    }
    
    /**
     * Get single tag
     * GET /tags/{id}
     */
    public function show(): void
    {
        $id = $this->request->param('id');
        $tag = $this->model->find($id);
        
        if (!$tag) {
            $this->notFound('Tag not found');
        }
        
        $this->success($tag);
    }
    
    /**
     * Create new tag
     * POST /tags
     */
    public function store(): void
    {
        $validated = $this->validate([
            'name' => 'required|max:100|unique:tags,name',
            'slug' => 'required|max:100|unique:tags,slug'
        ]);
        
        $id = $this->model->create($validated);
        
        $this->success([
            'id' => $id,
            'tag' => $this->model->find($id)
        ], 'Tag created successfully', 201);
    }
    
    /**
     * Update tag
     * PUT /tags/{id}
     */
    public function update(): void
    {
        $id = $this->request->param('id');
        $tag = $this->model->find($id);
        
        if (!$tag) {
            $this->notFound('Tag not found');
        }
        
        $validated = $this->validate([
            'name' => 'required|max:100|unique:tags,name,' . $id,
            'slug' => 'required|max:100|unique:tags,slug,' . $id
        ]);
        
        $this->model->update($id, $validated);
        
        $this->success(
            $this->model->find($id),
            'Tag updated successfully'
        );
    }
    
    /**
     * Delete tag
     * DELETE /tags/{id}
     */
    public function destroy(): void
    {
        $id = $this->request->param('id');
        $tag = $this->model->find($id);
        
        if (!$tag) {
            $this->notFound('Tag not found');
        }
        
        $this->model->delete($id);
        
        $this->success(null, 'Tag deleted successfully');
    }
}
