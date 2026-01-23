<?php

namespace App\Controllers\Base;

use Core\Controller;
use App\Models\Post;

class PostController extends Controller
{
    protected Post $model;
    
    public function __construct(?\Core\Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Post();
    }
    
    /**
     * Get all posts with pagination
     * GET /posts
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
     * Get all posts without pagination
     * GET /posts/all
     */
    public function all(): void
    {
        $data = $this->model::findQuery()->all();
        $this->success(['data' => $data]);
    }
    
    /**
     * Get single post
     * GET /posts/{id}
     */
    public function show(): void
    {
        $id = $this->request->param('id');
        $post = $this->model->find($id);
        
        if (!$post) {
            $this->notFound('Post not found');
        }
        
        $this->success($post);
    }
    
    /**
     * Create new post
     * POST /posts
     */
    public function store(): void
    {
        $validated = $this->validate([
            'user_id' => 'required|integer',
            'title' => 'required|max:255',
            'slug' => 'required|max:255|unique:posts,slug',
            'status' => 'max:20',
            'views' => 'integer'
        ]);
        
        $id = $this->model->create($validated);
        
        $this->success([
            'id' => $id,
            'post' => $this->model->find($id)
        ], 'Post created successfully', 201);
    }
    
    /**
     * Update post
     * PUT /posts/{id}
     */
    public function update(): void
    {
        $id = $this->request->param('id');
        $post = $this->model->find($id);
        
        if (!$post) {
            $this->notFound('Post not found');
        }
        
        $validated = $this->validate([
            'user_id' => 'required|integer',
            'title' => 'required|max:255',
            'slug' => 'required|max:255|unique:posts,slug,' . $id,
            'status' => 'max:20',
            'views' => 'integer'
        ]);
        
        $this->model->update($id, $validated);
        
        $this->success(
            $this->model->find($id),
            'Post updated successfully'
        );
    }
    
    /**
     * Delete post
     * DELETE /posts/{id}
     */
    public function destroy(): void
    {
        $id = $this->request->param('id');
        $post = $this->model->find($id);
        
        if (!$post) {
            $this->notFound('Post not found');
        }
        
        $this->model->delete($id);
        
        $this->success(null, 'Post deleted successfully');
    }
}
