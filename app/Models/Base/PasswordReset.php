<?php

namespace App\Models\Base;

use Core\ActiveRecord;

class PasswordReset extends ActiveRecord
{
    protected string $table = 'password_resets';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'email',
        'token',
        'expires_at'
    ];

    protected array $hidden = ['token'];

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
