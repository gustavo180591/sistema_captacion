<?php
namespace Core;

use PDO;
use PDOException;

class QueryBuilder {
    protected $model;
    protected $modelClass;
    protected $select = ['*'];
    protected $from;
    protected $where = [];
    protected $orderBy = [];
    protected $groupBy = [];
    protected $limit = null;
    protected $offset = null;
    protected $joins = [];
    protected $bindings = [];
    protected $wheresRaw = [];
    protected $with = [];
    
    public function __construct($modelClass) {
        $this->modelClass = $modelClass;
        $this->model = new $modelClass();
        $this->from = $this->model->getTable();
    }
    
    /**
     * Set the columns to be selected
     * @param array|string $columns
     * @return $this
     */
    public function select($columns = ['*']) {
        $this->select = is_array($columns) ? $columns : func_get_args();
        return $this;
    }
    
    /**
     * Add a basic where clause to the query
     * @param string $column
     * @param mixed $operator
     * @param mixed $value
     * @param string $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and') {
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }
        
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->where[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];
        
        $this->addBinding($value, 'where');
        
        return $this;
    }
    
    /**
     * Add an "or where" clause to the query
     * @param string $column
     * @param mixed $operator
     * @param mixed $value
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null) {
        return $this->where($column, $operator, $value, 'or');
    }
    
    /**
     * Add a "where in" clause to the query
     * @param string $column
     * @param array $values
     * @param string $boolean
     * @param bool $not
     * @return $this
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false) {
        $type = $not ? 'NotIn' : 'In';
        
        $this->where[] = [
            'type' => $type,
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];
        
        $this->addBinding($values, 'where');
        
        return $this;
    }
    
    /**
     * Add a "where not in" clause to the query
     * @param string $column
     * @param array $values
     * @param string $boolean
     * @return $this
     */
    public function whereNotIn($column, $values, $boolean = 'and') {
        return $this->whereIn($column, $values, $boolean, true);
    }
    
    /**
     * Add a raw where clause to the query
     * @param string $sql
     * @param array $bindings
     * @param string $boolean
     * @return $this
     */
    public function whereRaw($sql, $bindings = [], $boolean = 'and') {
        $this->where[] = [
            'type' => 'raw',
            'sql' => $sql,
            'boolean' => $boolean
        ];
        
        $this->addBinding($bindings, 'where');
        
        return $this;
    }
    
    /**
     * Add a "where null" clause to the query
     * @param string $column
     * @param string $boolean
     * @param bool $not
     * @return $this
     */
    public function whereNull($column, $boolean = 'and', $not = false) {
        $type = $not ? 'NotNull' : 'Null';
        
        $this->where[] = [
            'type' => $type,
            'column' => $column,
            'boolean' => $boolean
        ];
        
        return $this;
    }
    
    /**
     * Add a "where not null" clause to the query
     * @param string $column
     * @param string $boolean
     * @return $this
     */
    public function whereNotNull($column, $boolean = 'and') {
        return $this->whereNull($column, $boolean, true);
    }
    
    /**
     * Add an "order by" clause to the query
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc') {
        $this->orderBy[] = [
            'column' => $column,
            'direction' => strtolower($direction) === 'asc' ? 'asc' : 'desc'
        ];
        
        return $this;
    }
    
    /**
     * Set the "limit" value of the query
     * @param int $value
     * @return $this
     */
    public function limit($value) {
        $this->limit = $value;
        return $this;
    }
    
    /**
     * Set the "offset" value of the query
     * @param int $value
     * @return $this
     */
    public function offset($value) {
        $this->offset = $value;
        return $this;
    }
    
    /**
     * Set the "offset" value of the query
     * @param int $value
     * @return $this
     */
    public function skip($value) {
        return $this->offset($value);
    }
    
    /**
     * Set the "limit" and "offset" for a given page
     * @param int $page
     * @param int $perPage
     * @return $this
     */
    public function forPage($page, $perPage = 15) {
        return $this->skip(($page - 1) * $perPage)->take($perPage);
    }
    
    /**
     * Set the "limit" value of the query
     * @param int $value
     * @return $this
     */
    public function take($value) {
        return $this->limit($value);
    }
    
    /**
     * Add a join clause to the query
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @param string $type
     * @return $this
     */
    public function join($table, $first, $operator = null, $second = null, $type = 'inner') {
        $this->joins[] = [
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
            'type' => $type
        ];
        
        return $this;
    }
    
    /**
     * Add a left join to the query
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @return $this
     */
    public function leftJoin($table, $first, $operator = null, $second = null) {
        return $this->join($table, $first, $operator, $second, 'left');
    }
    
    /**
     * Add a right join to the query
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @return $this
     */
    public function rightJoin($table, $first, $operator = null, $second = null) {
        return $this->join($table, $first, $operator, $second, 'right');
    }
    
    /**
     * Add a "group by" clause to the query
     * @param array|string $columns
     * @return $this
     */
    public function groupBy($columns) {
        $this->groupBy = array_merge(
            $this->groupBy,
            is_array($columns) ? $columns : func_get_args()
        );
        
        return $this;
    }
    
    /**
     * Execute the query as a "select" statement
     * @param array $columns
     * @return \Illuminate\Support\Collection
     */
    public function get($columns = ['*']) {
        if (!empty($columns)) {
            $this->select($columns);
        }
        
        $results = $this->runSelect();
        
        $models = [];
        
        foreach ($results as $result) {
            $model = new $this->modelClass((array)$result);
            $model->exists = true;
            $models[] = $model;
        }
        
        return collect($models);
    }
    
    /**
     * Execute the query and get the first result
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public function first($columns = ['*']) {
        $results = $this->limit(1)->get($columns);
        
        return $results->first();
    }
    
    /**
     * Find a model by its primary key
     * @param mixed $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public function find($id, $columns = ['*']) {
        return $this->where($this->model->getKeyName(), $id)->first($columns);
    }
    
    /**
     * Get the SQL representation of the query
     * @return string
     */
    public function toSql() {
        return $this->compileSelect();
    }
    
    /**
     * Execute the query as a "select" statement
     * @return array
     */
    protected function runSelect() {
        $sql = $this->compileSelect();
        
        $statement = $this->getPdo()->prepare($sql);
        
        $statement->execute($this->getBindings());
        
        return $statement->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Compile the select statement into SQL
     * @return string
     */
    protected function compileSelect() {
        $sql = [];
        
        $sql[] = 'SELECT ' . $this->compileColumns();
        $sql[] = 'FROM ' . $this->from;
        
        if (!empty($this->joins)) {
            $sql[] = $this->compileJoins();
        }
        
        if (!empty($this->where)) {
            $sql[] = 'WHERE ' . $this->compileWheres();
        }
        
        if (!empty($this->groupBy)) {
            $sql[] = 'GROUP BY ' . $this->compileGroups();
        }
        
        if (!empty($this->orderBy)) {
            $sql[] = 'ORDER BY ' . $this->compileOrders();
        }
        
        if (!is_null($this->limit)) {
            $sql[] = 'LIMIT ' . $this->limit;
        }
        
        if (!is_null($this->offset)) {
            $sql[] = 'OFFSET ' . $this->offset;
        }
        
        return implode(' ', $sql);
    }
    
    /**
     * Compile the columns portion of the query
     * @return string
     */
    protected function compileColumns() {
        if ($this->select === ['*']) {
            return '*';
        }
        
        $selects = [];
        
        foreach ($this->select as $column) {
            $selects[] = $column;
        }
        
        return implode(', ', $selects);
    }
    
    /**
     * Compile the "where" portions of the query
     * @return string
     */
    protected function compileWheres() {
        $sql = [];
        
        foreach ($this->where as $where) {
            $method = 'compileWhere' . $where['type'];
            
            if (method_exists($this, $method)) {
                $sql[] = $where['boolean'] . ' ' . $this->$method($where);
            }
        }
        
        $sql = implode(' ', $sql);
        
        return '1 = 1' . ($sql ? ' ' . $sql : '');
    }
    
    /**
     * Compile a basic where clause
     * @param array $where
     * @return string
     */
    protected function compileWhereBasic($where) {
        return $where['column'] . ' ' . $where['operator'] . ' ?';
    }
    
    /**
     * Compile a "where in" clause
     * @param array $where
     * @return string
     */
    protected function compileWhereIn($where) {
        $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
        return $where['column'] . ' IN (' . $placeholders . ')';
    }
    
    /**
     * Compile a "where not in" clause
     * @param array $where
     * @return string
     */
    protected function compileWhereNotIn($where) {
        $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
        return $where['column'] . ' NOT IN (' . $placeholders . ')';
    }
    
    /**
     * Compile a "where null" clause
     * @param array $where
     * @return string
     */
    protected function compileWhereNull($where) {
        return $where['column'] . ' IS NULL';
    }
    
    /**
     * Compile a "where not null" clause
     * @param array $where
     * @return string
     */
    protected function compileWhereNotNull($where) {
        return $where['column'] . ' IS NOT NULL';
    }
    
    /**
     * Compile a raw where clause
     * @param array $where
     * @return string
     */
    protected function compileWhereRaw($where) {
        return $where['sql'];
    }
    
    /**
     * Compile the "order by" portions of the query
     * @return string
     */
    protected function compileOrders() {
        $orders = [];
        
        foreach ($this->orderBy as $order) {
            $orders[] = $order['column'] . ' ' . $order['direction'];
        }
        
        return implode(', ', $orders);
    }
    
    /**
     * Compile the "group by" portions of the query
     * @return string
     */
    protected function compileGroups() {
        return implode(', ', $this->groupBy);
    }
    
    /**
     * Compile the "join" portions of the query
     * @return string
     */
    protected function compileJoins() {
        $joins = [];
        
        foreach ($this->joins as $join) {
            $joins[] = strtoupper($join['type']) . ' JOIN ' . $join['table'] . ' ON ' . 
                      $join['first'] . ' ' . $join['operator'] . ' ' . $join['second'];
        }
        
        return implode(' ', $joins);
    }
    
    /**
     * Add a binding to the query
     * @param mixed $value
     * @param string $type
     * @return $this
     */
    public function addBinding($value, $type = 'where') {
        if (is_array($value)) {
            $this->bindings[$type] = array_merge($this->bindings[$type] ?? [], $value);
        } else {
            $this->bindings[$type][] = $value;
        }
        
        return $this;
    }
    
    /**
     * Get the current query value bindings
     * @return array
     */
    public function getBindings() {
        return array_merge(
            $this->bindings['where'] ?? [],
            $this->bindings['join'] ?? []
        );
    }
    
    /**
     * Get the database connection PDO instance
     * @return \PDO
     */
    protected function getPdo() {
        return $this->model->getConnection()->getPdo();
    }
    
    /**
     * Add an array of where clauses to the query
     * @param array $column
     * @param string $boolean
     * @param string $method
     * @return $this
     */
    protected function addArrayOfWheres($column, $boolean, $method = 'where') {
        return $this->whereNested(function($query) use ($column, $method, $boolean) {
            foreach ($column as $key => $value) {
                if (is_numeric($key) && is_array($value)) {
                    $query->$method(...array_values($value));
                } else {
                    $query->$method($key, '=', $value, $boolean);
                }
            }
        }, $boolean);
    }
    
    /**
     * Add a nested where statement to the query
     * @param \Closure $callback
     * @param string $boolean
     * @return $this
     */
    public function whereNested(\Closure $callback, $boolean = 'and') {
        $query = $this->newQuery();
        
        call_user_func($callback, $query);
        
        return $this->addNestedWhereQuery($query, $boolean);
    }
    
    /**
     * Add another query builder as a nested where to the query builder
     * @param \Illuminate\Database\Query\Builder|static $query
     * @param string $boolean
     * @return $this
     */
    public function addNestedWhereQuery($query, $boolean = 'and') {
        if (count($query->wheres)) {
            $type = 'Nested';
            
            $this->wheres[] = compact('type', 'query', 'boolean');
            
            $this->addBinding($query->getBindings(), 'where');
        }
        
        return $this;
    }
    
    /**
     * Create a new query instance for nested where condition
     * @return static
     */
    public function newQuery() {
        return new static($this->modelClass);
    }
    
    /**
     * Get the count of the total records for the paginator
     * @param array $columns
     * @return int
     */
    public function getCountForPagination($columns = ['*']) {
        $results = $this->runPaginationCountQuery($columns);
        
        if (isset($this->groups)) {
            return count($results);
        }
        
        return (int) $results[0]->aggregate;
    }
    
    /**
     * Run a pagination count query
     * @param array $columns
     * @return array
     */
    protected function runPaginationCountQuery($columns = ['*']) {
        if ($this->groups === null && is_null($this->aggregate)) {
            return $this->cloneWithout(['columns', 'orders', 'limit', 'offset'])
                         ->cloneWithoutBindings(['select'])
                         ->setAggregate('count', $this->withoutSelectAliases($columns))
                         ->get()->all();
        }
        
        $results = $this->cloneWithout(['orders', 'limit', 'offset'])
                         ->cloneWithoutBindings(['order'])
                         ->get($columns);
        
        return $results->all();
    }
    
    /**
     * Clone the query without the given properties
     * @param array $except
     * @return static
     */
    public function cloneWithout(array $except) {
        $clone = clone $this;
        
        foreach ($except as $property) {
            unset($clone->{$property});
        }
        
        return $clone;
    }
    
    /**
     * Clone the query without the given bindings
     * @param array $except
     * @return static
     */
    public function cloneWithoutBindings(array $except) {
        $clone = clone $this;
        
        foreach ($except as $type) {
            unset($clone->bindings[$type]);
        }
        
        return $clone;
    }
    
    /**
     * Set the aggregate property without running the query
     * @param string $function
     * @param array $columns
     * @return $this
     */
    protected function setAggregate($function, $columns) {
        $this->aggregate = compact('function', 'columns');
        
        if (empty($this->groups)) {
            $this->orders = null;
            $this->bindings['order'] = [];
        }
        
        return $this;
    }
    
    /**
     * Remove the column aliases since they will break count queries
     * @param array $columns
     * @return array
     */
    protected function withoutSelectAliases(array $columns) {
        return array_map(function ($column) {
            return is_string($column) && ($aliasPosition = strpos(strtolower($column), ' as ')) !== false
                    ? substr($column, 0, $aliasPosition) : $column;
        }, $columns);
    }
}
