<?php

declare(strict_types=1);

namespace Core;

use PDO;
use Core\Query;


abstract class ActiveRecord
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $with = [];

    /**
     * Database connection name to use
     * Set this in your model to use a specific database connection
     * 
     * @example protected ?string $connection = 'pgsql';
     */
    protected ?string $connection = null;

    public function __construct()
    {
        // Use specified connection or default
        $this->db = Database::connection($this->connection);
    }

    /**
     * Eager load relationships
     */
    public function with(array|string $relations): self
    {
        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $this->with = array_merge($this->with, $relations);
        return $this;
    }

    /**
     * Start a new query builder for this model
     */
    public static function findBuilder(): Query
    {
        $instance = new static();
        return (new Query($instance->connection))->from($instance->table);
    }

    /**
     * Alias for findBuilder()
     */
    public static function findQuery(): Query
    {
        return static::findBuilder();
    }

    /**
     * Find all records with eager loading
     */
    public function get(array $columns = ['*']): array
    {
        // For simplicity, we'll reuse all() logical here but add relationship loading
        // A real implementation would use a query builder pattern
        $results = $this->all($columns);

        if (!empty($this->with) && !empty($results)) {
            $this->loadRelations($results);
        }

        return $results;
    }

    /**
     * Load relations for result set
     */
    protected function loadRelations(array &$results): void
    {
        foreach ($this->with as $relation) {
            if (method_exists($this, $relation)) {
                $relationConfig = $this->$relation();

                // Collect IDs
                $ids = array_column($results, $relationConfig['local_key']);
                if (empty($ids)) continue;

                $placeholders = implode(',', array_fill(0, count($ids), '?'));

                // Fetch related data
                $relatedModel = new $relationConfig['model']();
                $sql = "SELECT * FROM {$relatedModel->table} WHERE {$relationConfig['foreign_key']} IN ($placeholders)";

                $stmt = $this->db->prepare($sql);
                $stmt->execute($ids);
                $relatedData = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Group related data by foreign key
                $relatedMap = [];
                foreach ($relatedData as $item) {
                    $relatedMap[$item[$relationConfig['foreign_key']]][] = $item;
                }

                // Attach to results
                foreach ($results as &$result) {
                    $key = $result[$relationConfig['local_key']];
                    $result[$relation] = $relatedMap[$key] ?? [];

                    // If belongsTo (single item), unwrap array
                    if ($relationConfig['type'] === 'belongsTo') {
                        $result[$relation] = $result[$relation][0] ?? null;
                    }
                }
            }
        }
    }

    // Relationship helpers
    protected function hasMany(string $model, string $foreignKey, string $localKey = 'id'): array
    {
        return [
            'type' => 'hasMany',
            'model' => $model,
            'foreign_key' => $foreignKey,
            'local_key' => $localKey
        ];
    }

    protected function belongsTo(string $model, string $foreignKey, string $ownerKey = 'id'): array
    {
        return [
            'type' => 'belongsTo',
            'model' => $model,
            'foreign_key' => $ownerKey, // In related table
            'local_key' => $foreignKey  // In this table
        ];
    }

    /**
     * Find all records
     */
    public function all(array $columns = ['*']): array
    {
        // Validate column names to prevent SQL injection
        $sanitizedCols = array_map(function ($col) {
            if ($col === '*') return $col;
            // Only allow valid column names (alphanumeric and underscore)
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
                throw new \InvalidArgumentException("Invalid column name: {$col}");
            }
            return $col;
        }, $columns);

        $cols = implode(', ', $sanitizedCols);
        $sql = "SELECT {$cols} FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        Database::logQuery($sql);

        $results = $this->hideFields($stmt->fetchAll(PDO::FETCH_ASSOC));

        // If 'with' was called on an instance before calling all()
        if (!empty($this->with) && !empty($results)) {
            $this->loadRelations($results);
        }

        return $results;
    }

    /**
     * Find record by ID
     */
    public function find(int|string $id, array $columns = ['*']): ?array
    {
        // Validate column names to prevent SQL injection
        $sanitizedCols = array_map(function ($col) {
            if ($col === '*') return $col;
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
                throw new \InvalidArgumentException("Invalid column name: {$col}");
            }
            return $col;
        }, $columns);

        $cols = implode(', ', $sanitizedCols);
        $sql = "SELECT {$cols} FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $params = ['id' => $id];
        $stmt->execute($params);
        Database::logQuery($sql, $params);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $results = [$result];
            if (!empty($this->with)) {
                $this->loadRelations($results);
            }
            return $this->hideFields($results)[0];
        }

        return null;
    }

    /**
     * Find records with conditions
     */
    public function where(array $conditions, array $columns = ['*']): array
    {
        // Validate column names to prevent SQL injection
        $sanitizedCols = array_map(function ($col) {
            if ($col === '*') return $col;
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
                throw new \InvalidArgumentException("Invalid column name: {$col}");
            }
            return $col;
        }, $columns);

        $cols = implode(', ', $sanitizedCols);
        $where = [];
        $params = [];

        foreach ($conditions as $key => $value) {
            // Validate condition keys as well
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
                throw new \InvalidArgumentException("Invalid condition key: {$key}");
            }
            $where[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT {$cols} FROM {$this->table} WHERE {$whereClause}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        Database::logQuery($sql, $params);

        $results = $this->hideFields($stmt->fetchAll(PDO::FETCH_ASSOC));

        if (!empty($this->with) && !empty($results)) {
            $this->loadRelations($results);
        }

        return $results;
    }

    /**
     * Create new record
     */
    public function create(array $data): int|string
    {
        $data = $this->filterFillable($data);

        if (!$this->beforeSave($data, true)) {
            return 0;
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        Database::logQuery($sql, $data);

        $id = $this->db->lastInsertId();

        // Add ID to data for afterSave
        $data[$this->primaryKey] = $id;
        $this->afterSave(true, $data);

        // Invalidate cache
        Cache::delete("table_count:{$this->table}");

        return $id;
    }

    /**
     * Update record by ID
     */
    public function update(int|string $id, array $data): bool
    {
        $data = $this->filterFillable($data);

        if (!$this->beforeSave($data, false)) {
            return false;
        }

        $set = [];
        $params = $data;
        foreach (array_keys($data) as $key) {
            $set[] = "{$key} = :{$key}";
        }

        $setClause = implode(', ', $set);
        $params['id'] = $id;

        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($params);
        Database::logQuery($sql, $params);

        if ($result) {
            $this->afterSave(false, array_merge(['id' => $id], $data));
        }

        return $result;
    }

    /**
     * Delete record by ID
     */
    public function delete(int|string $id): bool
    {
        if (!$this->beforeDelete($id)) {
            return false;
        }

        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $params = ['id' => $id];
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($params);
        Database::logQuery($sql, $params);

        if ($result) {
            $this->afterDelete($id);
        }

        // Invalidate cache
        Cache::delete("table_count:{$this->table}");

        return $result;
    }

    /**
     * Paginate results
     */
    public function paginate(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;

        // Cache total count for 5 minutes to avoid expensive COUNT(*) on every request
        $cacheKey = "table_count:{$this->table}";
        $total = Cache::remember($cacheKey, 300, function () {
            $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute();
            Database::logQuery($countSql);
            return $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        });

        // Get paginated data
        $sql = "SELECT * FROM {$this->table} LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        Database::logQuery($sql, ['limit' => $perPage, 'offset' => $offset]);

        $results = $this->hideFields($stmt->fetchAll(PDO::FETCH_ASSOC));

        if (!empty($this->with) && !empty($results)) {
            $this->loadRelations($results);
        }

        return [
            'data' => $results,
            'pagination' => [
                'total' => (int)$total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ]
        ];
    }

    /**
     * Execute raw query
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        Database::logQuery($sql, $params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Filter only fillable fields
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Hide sensitive fields
     */
    protected function hideFields(array $data): array
    {
        if (empty($this->hidden)) {
            return $data;
        }

        return array_map(function ($item) {
            foreach ($this->hidden as $field) {
                unset($item[$field]);
            }
            return $item;
        }, $data);
    }
    /**
     * Lifecycle Hook: Called before save (create/update)
     */
    protected function beforeSave(array &$data, bool $insert): bool
    {
        return true;
    }

    /**
     * Lifecycle Hook: Called after save (create/update)
     */
    protected function afterSave(bool $insert, array $data): void
    {
        // Override in model
    }

    /**
     * Lifecycle Hook: Called before delete
     */
    protected function beforeDelete(int|string $id): bool
    {
        return true;
    }

    /**
     * Lifecycle Hook: Called after delete
     */
    protected function afterDelete(int|string $id): void
    {
        // Override in model
    }
}
