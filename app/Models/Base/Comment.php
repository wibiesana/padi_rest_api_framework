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
