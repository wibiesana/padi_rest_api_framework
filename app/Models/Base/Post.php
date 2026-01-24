<?php

namespace App\Models\Base;

use Core\ActiveRecord;

class Post extends ActiveRecord
{
    protected string $table = 'posts';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'user_id', 'title', 'slug', 'content', 'excerpt', 'featured_image', 'status', 'published_at', 'views'
    ];
    
    protected array $hidden = [];
    
    /**
     * Search posts
     */
    public function search(string $keyword): array
    {
        $searchTerm = "%$keyword%";
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE title LIKE :keyword
                   OR content LIKE :keyword2
                   OR status LIKE :keyword3
                LIMIT 100";
        
        return $this->query($sql, [
            'keyword' => $searchTerm,
            'keyword2' => $searchTerm,
            'keyword3' => $searchTerm
        ]);
    }
}
