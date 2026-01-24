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
