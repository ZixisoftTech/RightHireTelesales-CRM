<?php
/**
 * Audit Log Model
 * 
 * This class handles audit log-related database operations.
 */

require_once 'Model.php';

class AuditLog extends Model {
    protected $table = 'audit_logs';
    protected $fillable = [
        'user_id', 'table_name', 'record_id', 'action', 'old_values', 'new_values', 'created_at'
    ];
    protected $auditEnabled = false; // Disable audit trail for audit logs
    
    /**
     * Get audit logs by table and record
     * 
     * @param string $tableName
     * @param int $recordId
     * @return array
     */
    public function getByTableAndRecord($tableName, $recordId) {
        $sql = "SELECT al.*, u.name as user_name 
                FROM {$this->table} al
                JOIN users u ON al.user_id = u.id
                WHERE al.table_name = :table_name AND al.record_id = :record_id
                ORDER BY al.created_at DESC";
        
        return $this->db->fetchAll($sql, [
            'table_name' => $tableName,
            'record_id' => $recordId
        ]);
    }
    
    /**
     * Get audit logs by user
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getByUser($userId, $limit = 100) {
        $sql = "SELECT al.*, u.name as user_name 
                FROM {$this->table} al
                JOIN users u ON al.user_id = u.id
                WHERE al.user_id = :user_id
                ORDER BY al.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }
    
    /**
     * Get recent audit logs
     * 
     * @param int $limit
     * @return array
     */
    public function getRecent($limit = 100) {
        $sql = "SELECT al.*, u.name as user_name 
                FROM {$this->table} al
                JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get audit logs by date range
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @return array
     */
    public function getByDateRange($startDate, $endDate, $limit = 1000) {
        $sql = "SELECT al.*, u.name as user_name 
                FROM {$this->table} al
                JOIN users u ON al.user_id = u.id
                WHERE al.created_at BETWEEN :start_date AND :end_date
                ORDER BY al.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, [
            'start_date' => $startDate . ' 00:00:00',
            'end_date' => $endDate . ' 23:59:59'
        ]);
    }
    
    /**
     * Get audit logs by action
     * 
     * @param string $action
     * @param int $limit
     * @return array
     */
    public function getByAction($action, $limit = 100) {
        $sql = "SELECT al.*, u.name as user_name 
                FROM {$this->table} al
                JOIN users u ON al.user_id = u.id
                WHERE al.action = :action
                ORDER BY al.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, ['action' => $action]);
    }
    
    /**
     * Get audit logs by table
     * 
     * @param string $tableName
     * @param int $limit
     * @return array
     */
    public function getByTable($tableName, $limit = 100) {
        $sql = "SELECT al.*, u.name as user_name 
                FROM {$this->table} al
                JOIN users u ON al.user_id = u.id
                WHERE al.table_name = :table_name
                ORDER BY al.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, ['table_name' => $tableName]);
    }
    
    /**
     * Format audit log values for display
     * 
     * @param string $values
     * @return array
     */
    public function formatValues($values) {
        if (empty($values)) {
            return [];
        }
        
        $decoded = json_decode($values, true);
        
        if (!$decoded) {
            return [];
        }
        
        // Remove sensitive fields
        unset($decoded['password']);
        
        return $decoded;
    }
    
    /**
     * Get changes between old and new values
     * 
     * @param string $oldValues
     * @param string $newValues
     * @return array
     */
    public function getChanges($oldValues, $newValues) {
        $old = $this->formatValues($oldValues);
        $new = $this->formatValues($newValues);
        $changes = [];
        
        foreach ($new as $key => $value) {
            // Skip audit fields
            if (in_array($key, ['created_at', 'updated_at', 'created_by', 'updated_by'])) {
                continue;
            }
            
            // Check if value changed
            if (!isset($old[$key]) || $old[$key] != $value) {
                $changes[$key] = [
                    'old' => isset($old[$key]) ? $old[$key] : null,
                    'new' => $value
                ];
            }
        }
        
        return $changes;
    }
}

