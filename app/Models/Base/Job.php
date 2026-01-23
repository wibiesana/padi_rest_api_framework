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
