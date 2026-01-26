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
     * Audit fields detected: created_at, updated_at, created_by, updated_by
     * These will be auto-populated by ActiveRecord
     */
    protected bool $useAudit = true;
    
    /**
     * Timestamp format: 'datetime'
     * 'datetime' = Y-m-d H:i:s (DATETIME/TIMESTAMP columns)
     * 'unix' = integer timestamp (INT/BIGINT columns)
     */
    protected string $timestampFormat = 'datetime';
    
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
