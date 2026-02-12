<?php

require_once __DIR__ . '/../../config/database.php';

class QueryBuilder
{
    protected $pdo;
    protected $table;
    protected $select = '*';
    protected $joins = [];
    protected $wheres = [];
    protected $bindings = [];
    protected $orderBy = [];
    protected $groupBy = [];
    protected $limit;
    protected $offset;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo) {
            $this->pdo = $pdo;
        } else {
            $db = new Database();
            $this->pdo = $db->getConnection();
        }
    }

    /**
     * Set table for query
     */
    public function table($table)
    {
        $this->table = $table;
        // Reset query parts when table is set
        $this->select = '*';
        $this->joins = [];
        $this->wheres = [];
        $this->bindings = [];
        $this->orderBy = [];
        $this->groupBy = [];
        $this->limit = null;
        $this->offset = null;
        return $this;
    }

    /**
     * SELECT clause
     */
    public function select($columns = ['*'])
    {
        if (is_array($columns)) {
            $this->select = implode(', ', $columns);
        } else {
            $this->select = $columns;
        }
        return $this;
    }

    /**
     * JOIN clauses
     */
    public function join($table, $first, $operator = null, $second = null, $type = 'INNER')
    {
        if ($operator === null && $second === null) {
            $this->joins[] = "{$type} JOIN {$table} ON {$first}";
        } else {
            $this->joins[] = "{$type} JOIN {$table} ON {$first} {$operator} {$second}";
        }
        return $this;
    }

    public function leftJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * WHERE clause
     */
    public function where($column, $operator, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        // Handle NULL values with IS NULL / IS NOT NULL syntax
        if ($value === null) {
            if ($operator === '=') {
                $this->wheres[] = [
                    'type' => 'AND',
                    'raw' => "{$column} IS NULL"
                ];
            } elseif ($operator === '!=' || $operator === '<>') {
                $this->wheres[] = [
                    'type' => 'AND',
                    'raw' => "{$column} IS NOT NULL"
                ];
            }
            return $this;
        }

        $this->wheres[] = [
            'type' => 'AND',
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        $this->bindings[] = $value;

        return $this;
    }

    public function orWhere($column, $operator, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        // Handle NULL values with IS NULL / IS NOT NULL syntax
        if ($value === null) {
            if ($operator === '=') {
                $this->wheres[] = [
                    'type' => 'OR',
                    'raw' => "{$column} IS NULL"
                ];
            } elseif ($operator === '!=' || $operator === '<>') {
                $this->wheres[] = [
                    'type' => 'OR',
                    'raw' => "{$column} IS NOT NULL"
                ];
            }
            return $this;
        }

        $this->wheres[] = [
            'type' => 'OR',
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        $this->bindings[] = $value;

        return $this;
    }

    public function whereIn($column, $values)
    {
        if (empty($values)) {
            $this->where('1', '=', '0'); // Force false condition if empty array
            return $this;
        }

        $placeholders = implode(',', array_fill(0, count($values), '?'));

        $this->wheres[] = [
            'type' => 'AND',
            'raw' => "{$column} IN ({$placeholders})",
            'values' => $values
        ];

        foreach ($values as $value) {
            $this->bindings[] = $value;
        }

        return $this;
    }

    /**
     * ORDER BY clause
     */
    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBy[] = "{$column} {$direction}";
        return $this;
    }

    /**
     * GROUP BY clause
     */
    public function groupBy($columns)
    {
        if (is_array($columns)) {
            $this->groupBy = array_merge($this->groupBy, $columns);
        } else {
            $this->groupBy[] = $columns;
        }
        return $this;
    }

    /**
     * LIMIT & OFFSET
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Execute SELECT query and return all results
     */
    public function get()
    {
        try {
            $query = $this->compileSelect();
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($this->bindings);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("QueryBuilder Error (get): " . $e->getMessage());
            error_log("Failed Query: " . $query);
            throw $e;
        }
    }

    /**
     * Execute SELECT query and return first result
     */
    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Execute SELECT query and return count
     */
    public function count($column = '*')
    {
        $originalSelect = $this->select;
        $this->select = "COUNT({$column}) as count";
        $result = $this->first();
        $this->select = $originalSelect; // Restore select
        return $result['count'] ?? 0;
    }

    /**
     * Retrieve a specific value from the first result
     */
    public function value($column)
    {
        $result = $this->first();
        return $result[$column] ?? null;
    }

    /**
     * Retrieve column values as array
     */
    public function pluck($column)
    {
        $originalSelect = $this->select;
        $this->select($column);
        $results = $this->get();
        $this->select = $originalSelect;

        return array_column($results, $column);
    }

    /**
     * Check if any records exist
     */
    public function exists()
    {
        $result = $this->first();
        return !empty($result);
    }

    /**
     * INSERT data
     */
    public function insert($data, $returnId = true)
    {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $values = array_values($data);

            $query = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

            // PostgreSQL specific: RETURNING id logic handled better by insertGetId or lastInsertId if standard

            $stmt = $this->pdo->prepare($query);
            $success = $stmt->execute($values);

            if ($success && $returnId) {
                return $this->pdo->lastInsertId();
            }

            return $success;
        } catch (PDOException $e) {
            error_log("QueryBuilder Error (insert): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Insert with RETURNING clause (PostgreSQL specific helper)
     */
    public function insertGetId($data, $primaryKey = 'id')
    {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $values = array_values($data);

            $query = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders}) RETURNING {$primaryKey}";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($values);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result[$primaryKey] ?? false;
        } catch (PDOException $e) {
            // Fallback for MySQL/others (or if Postgres syntax error, this propagates the exception from insert)
            return $this->insert($data, true);
        }
    }

    /**
     * UPDATE data
     */
    public function update($data)
    {
        try {
            $setClause = [];
            $values = [];

            foreach ($data as $column => $value) {
                $setClause[] = "{$column} = ?";
                $values[] = $value;
            }

            $query = "UPDATE {$this->table} SET " . implode(', ', $setClause);

            $whereClause = $this->compileWhereClause();

            if (!empty($whereClause)) {
                $query .= " WHERE {$whereClause}";
                // Add WHERE bindings to VALUES
                $values = array_merge($values, $this->bindings);
            }

            $stmt = $this->pdo->prepare($query);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("QueryBuilder Error (update): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * DELETE records
     */
    public function delete()
    {
        try {
            $query = "DELETE FROM {$this->table}";

            $whereClause = $this->compileWhereClause();

            if (!empty($whereClause)) {
                $query .= " WHERE {$whereClause}";
            }

            $stmt = $this->pdo->prepare($query);
            return $stmt->execute($this->bindings);
        } catch (PDOException $e) {
            error_log("QueryBuilder Error (delete): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Raw Query Execution
     */
    public function raw($sql, $bindings = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);

            if (stripos(trim($sql), 'SELECT') === 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return true;
        } catch (PDOException $e) {
            error_log("QueryBuilder Error (raw): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Transaction Methods
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Helper: Compile SELECT query string
     */
    protected function compileSelect()
    {
        $query = "SELECT {$this->select} FROM {$this->table}";

        if (!empty($this->joins)) {
            $query .= ' ' . implode(' ', $this->joins);
        }

        $whereClause = $this->compileWhereClause();
        if (!empty($whereClause)) {
            $query .= " WHERE {$whereClause}";
        }

        if (!empty($this->groupBy)) {
            $query .= " GROUP BY " . implode(', ', $this->groupBy);
        }

        if (!empty($this->orderBy)) {
            $query .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        if (isset($this->limit)) {
            $query .= " LIMIT {$this->limit}";
        }

        if (isset($this->offset)) {
            $query .= " OFFSET {$this->offset}";
        }

        return $query;
    }

    /**
     * Helper: Compile WHERE clause string
     */
    protected function compileWhereClause()
    {
        if (empty($this->wheres)) {
            return '';
        }

        $sqlParts = [];

        foreach ($this->wheres as $index => $where) {
            $prefix = ($index === 0) ? '' : $where['type'] . ' ';

            if (isset($where['raw'])) {
                $sqlParts[] = $prefix . $where['raw'];
            } else {
                $sqlParts[] = $prefix . "{$where['column']} {$where['operator']} ?";
            }
        }

        return implode(' ', $sqlParts);
    }

    protected function isPostgres()
    {
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql';
    }
}
