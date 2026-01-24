<?php

namespace App\Models\Base;

use Core\ActiveRecord;

class Migration extends ActiveRecord
{
    protected string $table = 'migrations';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'migration',
        'batch',
        'executed_at'
    ];

    protected array $hidden = [];

    /**
     * Search migrations
     */
    public function search(string $keyword): array
    {
        $searchTerm = "%$keyword%";

        $sql = "SELECT * FROM {$this->table} 
                WHERE migration LIKE :keyword
                LIMIT 100";

        return $this->query($sql, [
            'keyword' => $searchTerm
        ]);
    }
}
