<?php

namespace App\Controllers\Base;

use Core\Controller;
use App\Models\Comment;

class CommentController extends Controller
{
    protected Comment $model;
    
    public function __construct(?\Core\Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Comment();
    }
    
    /**
     * Get all comments with pagination
     * GET /comments
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
     * Get all comments without pagination
     * GET /comments/all
     */
    public function all(): void
    {
        $data = $this->model::findQuery()->all();
        $this->success(['data' => $data]);
    }
    
    /**
     * Get single comment
     * GET /comments/{id}
     */
    public function show(): void
    {
        $id = $this->request->param('id');
        $comment = $this->model->find($id);
        
        if (!$comment) {
            $this->notFound('Comment not found');
        }
        
        $this->success($comment);
    }
    
    /**
     * Create new comment
     * POST /comments
     */
    public function store(): void
    {
        $validated = $this->validate([
            'post_id' => 'required|integer',
            'user_id' => 'required|integer',
            'parent_id' => 'integer',
            'content' => 'required',
            'status' => 'max:20'
        ]);
        
        $id = $this->model->create($validated);
        
        $this->success([
            'id' => $id,
            'comment' => $this->model->find($id)
        ], 'Comment created successfully', 201);
    }
    
    /**
     * Update comment
     * PUT /comments/{id}
     */
    public function update(): void
    {
        $id = $this->request->param('id');
        $comment = $this->model->find($id);
        
        if (!$comment) {
            $this->notFound('Comment not found');
        }
        
        $validated = $this->validate([
            'post_id' => 'required|integer',
            'user_id' => 'required|integer',
            'parent_id' => 'integer',
            'content' => 'required',
            'status' => 'max:20'
        ]);
        
        $this->model->update($id, $validated);
        
        $this->success(
            $this->model->find($id),
            'Comment updated successfully'
        );
    }
    
    /**
     * Delete comment
     * DELETE /comments/{id}
     */
    public function destroy(): void
    {
        $id = $this->request->param('id');
        $comment = $this->model->find($id);
        
        if (!$comment) {
            $this->notFound('Comment not found');
        }
        
        $this->model->delete($id);
        
        $this->success(null, 'Comment deleted successfully');
    }
}
