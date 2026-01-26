<?php

namespace App\Models\Base;

use Core\ActiveRecord;

class PostTag extends ActiveRecord
{
    protected string $table = 'post_tags';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'post_id', 'tag_id'
    ];
    
    protected array $hidden = [];

    /**
     * Audit fields detected: created_at, created_by
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
     * Search post_tags
     */
    public function search(string $keyword): array
    {
        $searchTerm = "%$keyword%";
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE post_id LIKE :keyword
                LIMIT 100";
        
        return $this->query($sql, [
            'keyword' => $searchTerm
        ]);
    }
}
