<?php
/**
 * Base Model Class
 * 
 * This class provides common CRUD operations and audit trail functionality
 * for all models in the application.
 */

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $auditEnabled = true;
    
    /**
     * Constructor
     */
    public function __construct() {
        require_once APP_ROOT . '/config/database.php';
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all records with pagination
     * 
     * @param int $page
     * @param int $perPage
     * @param string $orderBy
     * @param string $order
     * @return array
     */
    public function getAll($page = 1, $perPage = RECORDS_PER_PAGE, $orderBy = 'id', $order = 'DESC') {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY {$orderBy} {$order} LIMIT {$perPage} OFFSET {$offset}";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Count total records
     * 
     * @return int
     */
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL";
        $result = $this->db->fetch($sql);
        
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * Find record by ID
     * 
     * @param int $id
     * @return array|bool
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id AND deleted_at IS NULL";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Find records by a specific field
     * 
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public function findBy($field, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = :value AND deleted_at IS NULL";
        
        return $this->db->fetchAll($sql, ['value' => $value]);
    }
    
    /**
     * Create a new record
     * 
     * @param array $data
     * @return int|bool
     */
    public function create($data) {
        // Filter data to only include fillable fields
        $data = array_intersect_key($data, array_flip($this->fillable));
        
        // Add audit fields
        if (isset($_SESSION['user_id'])) {
            $data['created_by'] = $_SESSION['user_id'];
            $data['updated_by'] = $_SESSION['user_id'];
        }
        
        // Prepare SQL
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ":{$field}";
        }, $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        try {
            $this->db->query($sql);
            $this->db->bind($data);
            $result = $this->db->execute();
            
            if ($result) {
                $id = $this->db->lastInsertId();
                
                // Log to audit trail
                if ($this->auditEnabled && isset($_SESSION['user_id'])) {
                    $this->logAudit('create', $id, null, $data);
                }
                
                return $id;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log('Create Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a record
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        // Get old values for audit
        $oldValues = $this->find($id);
        
        if (!$oldValues) {
            return false;
        }
        
        // Filter data to only include fillable fields
        $data = array_intersect_key($data, array_flip($this->fillable));
        
        // Add audit fields
        if (isset($_SESSION['user_id'])) {
            $data['updated_by'] = $_SESSION['user_id'];
        }
        
        // Prepare SQL
        $setClause = array_map(function($field) {
            return "{$field} = :{$field}";
        }, array_keys($data));
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " 
                WHERE {$this->primaryKey} = :id";
        
        $data['id'] = $id;
        
        try {
            $this->db->query($sql);
            $this->db->bind($data);
            $result = $this->db->execute();
            
            // Log to audit trail
            if ($result && $this->auditEnabled && isset($_SESSION['user_id'])) {
                $this->logAudit('update', $id, $oldValues, $data);
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log('Update Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Soft delete a record
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        // Get old values for audit
        $oldValues = $this->find($id);
        
        if (!$oldValues) {
            return false;
        }
        
        $data = [
            'deleted_at' => date('Y-m-d H:i:s')
        ];
        
        // Add audit fields
        if (isset($_SESSION['user_id'])) {
            $data['updated_by'] = $_SESSION['user_id'];
        }
        
        $sql = "UPDATE {$this->table} SET deleted_at = :deleted_at, updated_by = :updated_by 
                WHERE {$this->primaryKey} = :id";
        
        $data['id'] = $id;
        
        try {
            $this->db->query($sql);
            $this->db->bind($data);
            $result = $this->db->execute();
            
            // Log to audit trail
            if ($result && $this->auditEnabled && isset($_SESSION['user_id'])) {
                $this->logAudit('delete', $id, $oldValues, $data);
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log('Delete Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Restore a soft-deleted record
     * 
     * @param int $id
     * @return bool
     */
    public function restore($id) {
        // Get old values for audit
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $oldValues = $this->db->fetch($sql, ['id' => $id]);
        
        if (!$oldValues) {
            return false;
        }
        
        $data = [
            'deleted_at' => null
        ];
        
        // Add audit fields
        if (isset($_SESSION['user_id'])) {
            $data['updated_by'] = $_SESSION['user_id'];
        }
        
        $sql = "UPDATE {$this->table} SET deleted_at = NULL, updated_by = :updated_by 
                WHERE {$this->primaryKey} = :id";
        
        $data['id'] = $id;
        
        try {
            $this->db->query($sql);
            $this->db->bind($data);
            $result = $this->db->execute();
            
            // Log to audit trail
            if ($result && $this->auditEnabled && isset($_SESSION['user_id'])) {
                $this->logAudit('restore', $id, $oldValues, $data);
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log('Restore Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log action to audit trail
     * 
     * @param string $action
     * @param int $recordId
     * @param array $oldValues
     * @param array $newValues
     * @return bool
     */
    protected function logAudit($action, $recordId, $oldValues, $newValues) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        $auditData = [
            'user_id' => $_SESSION['user_id'],
            'table_name' => $this->table,
            'record_id' => $recordId,
            'action' => $action,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $fields = array_keys($auditData);
        $placeholders = array_map(function($field) {
            return ":{$field}";
        }, $fields);
        
        $sql = "INSERT INTO audit_logs (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        try {
            $this->db->query($sql);
            $this->db->bind($auditData);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log('Audit Log Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Search records by keyword in specified fields
     * 
     * @param string $keyword
     * @param array $fields
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function search($keyword, $fields, $page = 1, $perPage = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        $conditions = [];
        $params = [];
        
        foreach ($fields as $field) {
            $conditions[] = "{$field} LIKE :keyword";
        }
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE (" . implode(' OR ', $conditions) . ") 
                AND deleted_at IS NULL 
                LIMIT {$perPage} OFFSET {$offset}";
        
        $params['keyword'] = "%{$keyword}%";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Count search results
     * 
     * @param string $keyword
     * @param array $fields
     * @return int
     */
    public function countSearch($keyword, $fields) {
        $conditions = [];
        $params = [];
        
        foreach ($fields as $field) {
            $conditions[] = "{$field} LIKE :keyword";
        }
        
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE (" . implode(' OR ', $conditions) . ") 
                AND deleted_at IS NULL";
        
        $params['keyword'] = "%{$keyword}%";
        
        $result = $this->db->fetch($sql, $params);
        
        return $result ? (int)$result['total'] : 0;
    }
}

