<?php

namespace App\Models\Base;

use Core\ActiveRecord;

class Tag extends ActiveRecord
{
    protected string $table = 'tags';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'name', 'slug', 'description'
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
     * Search tags
     */
    public function search(string $keyword): array
    {
        $searchTerm = "%$keyword%";
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE name LIKE :keyword
                   OR description LIKE :keyword2
                LIMIT 100";
        
        return $this->query($sql, [
            'keyword' => $searchTerm,
            'keyword2' => $searchTerm
        ]);
    }
}
