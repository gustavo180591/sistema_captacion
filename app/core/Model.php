<?php
namespace Core;

use PDO;
use PDOException;

abstract class Model {
    protected static $table = '';
    protected static $primaryKey = 'id';
    protected $attributes = [];
    protected $original = [];
    protected $exists = false;
    
    public function __construct($attributes = []) {
        $this->fill($attributes);
        $this->original = $this->attributes;
    }
    
    /**
     * Get the database connection
     * @return \PDO
     */
    protected static function db() {
        return Database::getInstance();
    }
    
    /**
     * Fill the model with an array of attributes
     * @param array $attributes
     * @return $this
     */
    public function fill(array $attributes) {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }
    
    /**
     * Set a given attribute on the model
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setAttribute($key, $value) {
        $this->attributes[$key] = $value;
        return $this;
    }
    
    /**
     * Get an attribute from the model
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute($key, $default = null) {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
        
        if (method_exists($this, $key)) {
            return $this->$key();
        }
        
        return $default;
    }
    
    /**
     * Get all of the current attributes on the model
     * @return array
     */
    public function getAttributes() {
        return $this->attributes;
    }
    
    /**
     * Get the original attribute values
     * @return array
     */
    public function getOriginal() {
        return $this->original;
    }
    
    /**
     * Get the table associated with the model
     * @return string
     */
    public static function getTable() {
        return static::$table ?: strtolower((new \ReflectionClass(get_called_class()))->getShortName()) . 's';
    }
    
    /**
     * Get the primary key for the model
     * @return string
     */
    public function getKeyName() {
        return static::$primaryKey;
    }
    
    /**
     * Get the value of the model's primary key
     * @return mixed
     */
    public function getKey() {
        return $this->getAttribute($this->getKeyName());
    }
    
    /**
     * Save the model to the database
     * @return bool
     */
    public function save() {
        if ($this->exists) {
            return $this->updateRecord();
        } else {
            return $this->insertRecord();
        }
    }
    
    /**
     * Insert a new record in the database
     * @return bool
     */
    protected function insertRecord() {
        $attributes = $this->getDirty();
        
        if (empty($attributes)) {
            return true;
        }
        
        $columns = implode(', ', array_keys($attributes));
        $placeholders = ':' . implode(', :', array_keys($attributes));
        
        $sql = "INSERT INTO " . static::getTable() . " ({$columns}) VALUES ({$placeholders})";
        
        $result = static::db()->query($sql, $attributes);
        
        if ($result) {
            $this->exists = true;
            $this->setAttribute($this->getKeyName(), static::db()->lastInsertId());
            $this->syncOriginal();
            return true;
        }
        
        return false;
    }
    
    /**
     * Update the model in the database
     * @return bool
     */
    protected function updateRecord() {
        $dirty = $this->getDirty();
        
        if (empty($dirty)) {
            return true;
        }
        
        $set = [];
        $values = [];
        
        foreach ($dirty as $key => $value) {
            $set[] = "{$key} = :{$key}";
            $values[$key] = $value;
        }
        
        $set = implode(', ', $set);
        $values[$this->getKeyName()] = $this->getKey();
        
        $sql = "UPDATE " . static::getTable() . " SET {$set} WHERE {$this->getKeyName()} = :{$this->getKeyName()}";
        
        $result = static::db()->query($sql, $values);
        
        if ($result) {
            $this->syncOriginal();
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete the model from the database
     * @return bool
     */
    public function delete() {
        if (!$this->exists) {
            return false;
        }
        
        $sql = "DELETE FROM " . static::getTable() . " WHERE {$this->getKeyName()} = ?";
        
        $result = static::db()->query($sql, [$this->getKey()]);
        
        if ($result) {
            $this->exists = false;
            return true;
        }
        
        return false;
    }
    
    /**
     * Get the attributes that have been changed since the last sync
     * @return array
     */
    public function getDirty() {
        $dirty = [];
        
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || 
                $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }
        
        return $dirty;
    }
    
    /**
     * Sync the original attributes with the current attributes
     * @return $this
     */
    public function syncOriginal() {
        $this->original = $this->attributes;
        return $this;
    }
    
    /**
     * Determine if the model or any of the given attribute(s) have been modified
     * @param  array|string|null  $attributes
     * @return bool
     */
    public function isDirty($attributes = null) {
        $dirty = $this->getDirty();
        
        if (is_null($attributes)) {
            return count($dirty) > 0;
        }
        
        if (!is_array($attributes)) {
            $attributes = func_get_args();
        }
        
        foreach ($attributes as $attribute) {
            if (array_key_exists($attribute, $dirty)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Find a model by its primary key
     * @param int $id
     * @return static|null
     */
    public static function find($id) {
        $instance = new static();
        $result = static::db()->query(
            "SELECT * FROM " . static::getTable() . " WHERE {$instance->getKeyName()} = ?", 
            [$id]
        )->first();
        
        if ($result) {
            $model = new static((array)$result);
            $model->exists = true;
            return $model;
        }
        
        return null;
    }
    
    /**
     * Get all records from the database
     * @return array
     */
    public static function all() {
        $results = static::db()->query("SELECT * FROM " . static::getTable())->results();
        $models = [];
        
        foreach ($results as $result) {
            $model = new static((array)$result);
            $model->exists = true;
            $models[] = $model;
        }
        
        return $models;
    }
    
    /**
     * Begin querying the model
     * @return \Core\QueryBuilder
     */
    public static function query() {
        return new QueryBuilder(static::class);
    }
    
    /**
     * Handle dynamic method calls into the model
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters) {
        return $this->forwardCallTo($this->newQuery(), $method, $parameters);
    }
    
    /**
     * Handle dynamic static method calls into the model
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters) {
        return (new static)->$method(...$parameters);
    }
    
    /**
     * Forward a method call to the given object
     * @param mixed $object
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    protected function forwardCallTo($object, $method, $parameters) {
        try {
            return $object->$method(...$parameters);
        } catch (\BadMethodCallException $e) {
            throw new \BadMethodCallException(sprintf(
                'Call to undefined method %s::%s()', static::class, $method
            ));
        }
    }
    
    /**
     * Get a new query builder for the model's table
     * @return \Core\QueryBuilder
     */
    public function newQuery() {
        return new QueryBuilder(static::class);
    }
    
    /**
     * Get an attribute from the model
     * @param string $key
     * @return mixed
     */
    public function __get($key) {
        return $this->getAttribute($key);
    }
    
    /**
     * Set a given attribute on the model
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value) {
        $this->setAttribute($key, $value);
    }
    
    /**
     * Check if an attribute exists on the model
     * @param string $key
     * @return bool
     */
    public function __isset($key) {
        return isset($this->attributes[$key]) || 
               (method_exists($this, $key) && !is_null($this->$key));
    }
    
    /**
     * Unset an attribute on the model
     * @param string $key
     * @return void
     */
    public function __unset($key) {
        unset($this->attributes[$key]);
    }
    
    /**
     * Convert the model to its string representation
     * @return string
     */
    public function __toString() {
        return json_encode($this->toArray());
    }
    
    /**
     * Convert the model instance to an array
     * @return array
     */
    public function toArray() {
        return $this->attributes;
    }
    
    /**
     * Convert the model instance to JSON
     * @param int $options
     * @return string
     */
    public function toJson($options = 0) {
        return json_encode($this->toArray(), $options);
    }
}
