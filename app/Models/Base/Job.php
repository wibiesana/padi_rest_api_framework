<?php

namespace App\Models\Base;

use Core\ActiveRecord;

class Job extends ActiveRecord
{
    protected string $table = 'jobs';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'queue', 'payload', 'attempts', 'reserved_at', 'available_at'
    ];
    
    protected array $hidden = [];

    /**
     * Audit fields detected: created_at
     * These will be auto-populated by ActiveRecord
     */
    protected bool $useAudit = true;
    
    /**
     * Timestamp format: 'unix'
     * 'datetime' = Y-m-d H:i:s (DATETIME/TIMESTAMP columns)
     * 'unix' = integer timestamp (INT/BIGINT columns)
     */
    protected string $timestampFormat = 'unix';
    
    /**
     * Search jobs
     */
    public function search(string $keyword): array
    {
        $searchTerm = "%$keyword%";
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE queue LIKE :keyword
                LIMIT 100";
        
        return $this->query($sql, [
            'keyword' => $searchTerm
        ]);
    }
}
