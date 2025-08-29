<?php
/**
 * Audit Log Model
 * 
 * This class handles all audit log-related database operations.
 */

require_once 'Model.php';

class AuditLog extends Model {
    protected $table = 'audit_logs';
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get audit logs by record
     * 
     * @param string $tableName
     * @param int $recordId
     * @return array
     */
    public function getByRecord($tableName, $recordId) {
        $sql = "SELECT al.*, u.name as user_name
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.table_name = :table_name AND al.record_id = :record_id
                ORDER BY al.created_at DESC";
        
        return $this->db->fetchAll($sql, [
            'table_name' => $tableName,
            'record_id' => $recordId
        ]);
    }
    
    /**
     * Get recent audit logs
     * 
     * @param int $limit
     * @return array
     */
    public function getRecent($limit = 10) {
        $sql = "SELECT al.*, u.name as user_name
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, ['limit' => $limit]);
    }
    
    /**
     * Get audit logs by user
     * 
     * @param int $userId
     * @param int $page
     * @return array
     */
    public function getByUser($userId, $page = 1) {
        $offset = ($page - 1) * RECORDS_PER_PAGE;
        
        $sql = "SELECT al.*
                FROM {$this->table} al
                WHERE al.user_id = :user_id
                ORDER BY al.created_at DESC
                LIMIT :offset, :limit";
        
        return $this->db->fetchAll($sql, [
            'user_id' => $userId,
            'offset' => $offset,
            'limit' => RECORDS_PER_PAGE
        ]);
    }
    
    /**
     * Count audit logs by user
     * 
     * @param int $userId
     * @return int
     */
    public function countByUser($userId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id";
        $result = $this->db->fetch($sql, ['user_id' => $userId]);
        
        return $result ? $result['count'] : 0;
    }
    
    /**
     * Get audit logs by date range
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int $page
     * @return array
     */
    public function getByDateRange($startDate, $endDate, $page = 1) {
        $offset = ($page - 1) * RECORDS_PER_PAGE;
        
        $sql = "SELECT al.*, u.name as user_name
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.created_at BETWEEN :start_date AND :end_date
                ORDER BY al.created_at DESC
                LIMIT :offset, :limit";
        
        return $this->db->fetchAll($sql, [
            'start_date' => $startDate . ' 00:00:00',
            'end_date' => $endDate . ' 23:59:59',
            'offset' => $offset,
            'limit' => RECORDS_PER_PAGE
        ]);
    }
    
    /**
     * Count audit logs by date range
     * 
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    public function countByDateRange($startDate, $endDate) {
        $sql = "SELECT COUNT(*) as count
                FROM {$this->table}
                WHERE created_at BETWEEN :start_date AND :end_date";
        
        $result = $this->db->fetch($sql, [
            'start_date' => $startDate . ' 00:00:00',
            'end_date' => $endDate . ' 23:59:59'
        ]);
        
        return $result ? $result['count'] : 0;
    }
    
    /**
     * Get audit logs by action
     * 
     * @param string $action
     * @param int $page
     * @return array
     */
    public function getByAction($action, $page = 1) {
        $offset = ($page - 1) * RECORDS_PER_PAGE;
        
        $sql = "SELECT al.*, u.name as user_name
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.action = :action
                ORDER BY al.created_at DESC
                LIMIT :offset, :limit";
        
        return $this->db->fetchAll($sql, [
            'action' => $action,
            'offset' => $offset,
            'limit' => RECORDS_PER_PAGE
        ]);
    }
    
    /**
     * Count audit logs by action
     * 
     * @param string $action
     * @return int
     */
    public function countByAction($action) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE action = :action";
        $result = $this->db->fetch($sql, ['action' => $action]);
        
        return $result ? $result['count'] : 0;
    }
}

