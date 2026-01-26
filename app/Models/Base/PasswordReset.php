<?php

namespace App\Models\Base;

use Core\ActiveRecord;

class PasswordReset extends ActiveRecord
{
    protected string $table = 'password_resets';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'email', 'token', 'expires_at'
    ];
    
    protected array $hidden = ['token'];

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
     * Search password_resets
     */
    public function search(string $keyword): array
    {
        $searchTerm = "%$keyword%";
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE email LIKE :keyword
                LIMIT 100";
        
        return $this->query($sql, [
            'keyword' => $searchTerm
        ]);
    }
}
