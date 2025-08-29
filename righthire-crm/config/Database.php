<?php
/**
 * Database Class
 * 
 * This class handles all database operations.
 */

class Database {
    private static $instance = null;
    private $conn;
    
    /**
     * Constructor
     */
    private function __construct() {
        try {
            $this->conn = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get instance (Singleton pattern)
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Get connection
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Execute query
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    /**
     * Get single row
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return array|false Row data or false if not found
     */
    public function getRow($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    /**
     * Get multiple rows
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return array Rows data
     */
    public function getRows($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    /**
     * Get single value
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return mixed Value or false if not found
     */
    public function getVar($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    /**
     * Insert data
     * 
     * @param string $table Table name
     * @param array $data Data to insert
     * @return int|false Last insert ID or false on failure
     */
    public function insert($table, $data) {
        try {
            // Build query
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            
            $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
            
            // Execute query
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array_values($data));
            
            // Return last insert ID
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    /**
     * Update data
     * 
     * @param string $table Table name
     * @param array $data Data to update
     * @param string $where Where clause
     * @param array $params Where parameters
     * @return bool Success or failure
     */
    public function update($table, $data, $where, $params = []) {
        try {
            // Build query
            $set = [];
            foreach (array_keys($data) as $column) {
                $set[] = "{$column} = ?";
            }
            
            $set = implode(', ', $set);
            
            $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
            
            // Execute query
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array_merge(array_values($data), $params));
            
            // Return success
            return true;
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    /**
     * Delete data
     * 
     * @param string $table Table name
     * @param string $where Where clause
     * @param array $params Where parameters
     * @return bool Success or failure
     */
    public function delete($table, $where, $params = []) {
        try {
            // Build query
            $sql = "DELETE FROM {$table} WHERE {$where}";
            
            // Execute query
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            // Return success
            return true;
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    /**
     * Begin transaction
     * 
     * @return bool Success or failure
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    /**
     * Commit transaction
     * 
     * @return bool Success or failure
     */
    public function commit() {
        return $this->conn->commit();
    }
    
    /**
     * Rollback transaction
     * 
     * @return bool Success or failure
     */
    public function rollBack() {
        return $this->conn->rollBack();
    }
}

