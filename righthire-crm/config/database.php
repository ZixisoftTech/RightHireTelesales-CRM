<?php
/**
 * Database Connection Class
 * 
 * This file handles the database connection and provides methods for executing queries.
 */

class Database {
    private static $instance = null;
    private $connection;
    private $statement;
    
    /**
     * Constructor - Creates a new PDO connection
     */
    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error and display user-friendly message
            error_log('Database Connection Error: ' . $e->getMessage());
            die('Database connection failed. Please contact the administrator.');
        }
    }
    
    /**
     * Get database instance (Singleton pattern)
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Prepare a statement
     * 
     * @param string $sql
     * @return Database
     */
    public function query($sql) {
        $this->statement = $this->connection->prepare($sql);
        return $this;
    }
    
    /**
     * Bind values to prepared statement
     * 
     * @param array $params
     * @return Database
     */
    public function bind($params) {
        if (!empty($params) && is_array($params)) {
            foreach ($params as $param => $value) {
                $type = PDO::PARAM_STR;
                
                if (is_int($value)) {
                    $type = PDO::PARAM_INT;
                } elseif (is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $type = PDO::PARAM_NULL;
                }
                
                if (is_int($param)) {
                    // For numeric keys, use positional parameters
                    $this->statement->bindValue($param + 1, $value, $type);
                } else {
                    // For string keys, use named parameters
                    $this->statement->bindValue(':' . $param, $value, $type);
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Execute the prepared statement
     * 
     * @return bool
     */
    public function execute() {
        try {
            return $this->statement->execute();
        } catch (PDOException $e) {
            error_log('Query Execution Error: ' . $e->getMessage());
            throw $e; // Re-throw to be handled by the caller
        }
    }
    
    /**
     * Execute a query and return all results
     * 
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        try {
            $this->query($sql);
            $this->bind($params);
            $this->execute();
            return $this->statement->fetchAll();
        } catch (PDOException $e) {
            error_log('FetchAll Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Execute a query and return a single row
     * 
     * @param string $sql
     * @param array $params
     * @return array|bool
     */
    public function fetch($sql, $params = []) {
        try {
            $this->query($sql);
            $this->bind($params);
            $this->execute();
            return $this->statement->fetch();
        } catch (PDOException $e) {
            error_log('Fetch Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a query and return the row count
     * 
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function rowCount($sql, $params = []) {
        try {
            $this->query($sql);
            $this->bind($params);
            $this->execute();
            return $this->statement->rowCount();
        } catch (PDOException $e) {
            error_log('RowCount Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get the last inserted ID
     * 
     * @return string
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
}

