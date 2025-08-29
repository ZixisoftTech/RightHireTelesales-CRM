<?php
/**
 * Database Connection
 * 
 * This file handles the database connection using PDO.
 */

class Database {
    private static $instance = null;
    private $conn;
    
    /**
     * Constructor
     */
    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get database instance (singleton pattern)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Get database connection
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Execute a query
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die('Query failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get a single row
     */
    public function getRow($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Get multiple rows
     */
    public function getRows($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get a single value
     */
    public function getValue($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Insert a row
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->query($sql, array_values($data));
        
        return $this->conn->lastInsertId();
    }
    
    /**
     * Update a row
     */
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = ?";
        }
        
        $set = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        
        $params = array_merge(array_values($data), $whereParams);
        
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount();
    }
    
    /**
     * Delete a row
     */
    public function delete($table, $where, $whereParams = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        $stmt = $this->query($sql, $whereParams);
        
        return $stmt->rowCount();
    }
    
    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    /**
     * Commit a transaction
     */
    public function commit() {
        return $this->conn->commit();
    }
    
    /**
     * Rollback a transaction
     */
    public function rollback() {
        return $this->conn->rollBack();
    }
}

// Get database instance
$db = Database::getInstance();

