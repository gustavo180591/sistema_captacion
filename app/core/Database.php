<?php
namespace Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;
    private $query;
    private $results;
    private $count = 0;
    private $error = false;
    
    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function query($sql, $params = []) {
        $this->error = false;
        
        if ($this->query = $this->connection->prepare($sql)) {
            $x = 1;
            if (count($params)) {
                foreach ($params as $param) {
                    $this->query->bindValue($x, $param);
                    $x++;
                }
            }
            
            if ($this->query->execute()) {
                $this->results = $this->query->fetchAll(PDO::FETCH_OBJ);
                $this->count = $this->query->rowCount();
            } else {
                $this->error = true;
            }
        }
        
        return $this;
    }
    
    public function action($action, $table, $where = []) {
        if (count($where) === 3) {
            $operators = ['=', '>', '<', '>=', '<=', 'LIKE', '!=', '<>'];
            
            $field = $where[0];
            $operator = $where[1];
            $value = $where[2];
            
            if (in_array($operator, $operators)) {
                $sql = "{$action} FROM {$table} WHERE {$field} {$operator} ?";
                
                if (!$this->query($sql, [$value])->error()) {
                    return $this;
                }
            }
        } else {
            $sql = "{$action} FROM {$table}";
            
            if (!$this->query($sql)->error()) {
                return $this;
            }
        }
        
        return false;
    }
    
    public function get($table, $where = []) {
        return $this->action('SELECT *', $table, $where);
    }
    
    public function delete($table, $where = []) {
        return $this->action('DELETE', $table, $where);
    }
    
    public function insert($table, $fields = []) {
        $keys = array_keys($fields);
        $values = '';
        $x = 1;
        
        foreach ($fields as $field) {
            $values .= '?';
            if ($x < count($fields)) {
                $values .= ', ';
            }
            $x++;
        }
        
        $sql = "INSERT INTO {$table} (`" . implode('`, `', $keys) . "`) VALUES ({$values})";
        
        if (!$this->query($sql, $fields)->error()) {
            return $this->connection->lastInsertId();
        }
        
        return false;
    }
    
    public function update($table, $id, $fields = []) {
        $set = '';
        $x = 1;
        
        foreach (array_keys($fields) as $name) {
            $set .= "{$name} = ?";
            if ($x < count($fields)) {
                $set .= ', ';
            }
            $x++;
        }
        
        $sql = "UPDATE {$table} SET {$set} WHERE id = {$id}";
        
        if (!$this->query($sql, $fields)->error()) {
            return true;
        }
        
        return false;
    }
    
    public function first() {
        return $this->results()[0] ?? null;
    }
    
    public function results() {
        return $this->results;
    }
    
    public function count() {
        return $this->count;
    }
    
    public function error() {
        return $this->error;
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollBack() {
        return $this->connection->rollBack();
    }
}
