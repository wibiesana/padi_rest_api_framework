<?php

namespace App\Models\Base;

use Core\ActiveRecord;

class Comment extends ActiveRecord
{
    protected string $table = 'comments';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'post_id', 'user_id', 'parent_id', 'content', 'status'
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
     * Search comments
     */
    public function search(string $keyword): array
    {
        $searchTerm = "%$keyword%";
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE content LIKE :keyword
                   OR status LIKE :keyword2
                LIMIT 100";
        
        return $this->query($sql, [
            'keyword' => $searchTerm,
            'keyword2' => $searchTerm
        ]);
    }
}
