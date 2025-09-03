<?php
/**
 * Call Log Model
 * 
 * This model handles all call log-related database operations.
 */

require_once 'Model.php';

class CallLog extends Model {
    protected $table = 'call_logs';
    protected $fillable = ['lead_id', 'status', 'other_reason', 'follow_up_date', 'remarks', 'created_by', 'updated_by'];
    
    /**
     * Get call logs by lead ID
     * 
     * @param int $leadId Lead ID
     * @return array Array of call logs
     */
    public function getByLeadId($leadId) {
        $sql = "SELECT cl.*, u.name AS created_by_name
                FROM {$this->table} cl
                LEFT JOIN users u ON cl.created_by = u.id
                WHERE cl.lead_id = ? AND cl.deleted_at IS NULL
                ORDER BY cl.created_at DESC";
        
        return $this->db->getRows($sql, [$leadId]);
    }
    
    /**
     * Alias for getByLeadId for backward compatibility
     * 
     * @param int $leadId Lead ID
     * @return array Array of call logs
     */
    public function getCallLogsByLeadId($leadId) {
        return $this->getByLeadId($leadId);
    }
    
    /**
     * Get recent call logs
     */
    public function getRecent($limit = 10) {
        $sql = "SELECT cl.*, l.name AS lead_name, l.id AS lead_id, u.name AS created_by_name
                FROM {$this->table} cl
                JOIN leads l ON cl.lead_id = l.id
                LEFT JOIN users u ON cl.created_by = u.id
                WHERE cl.deleted_at IS NULL";
        
        // Apply territory restrictions for employees
        if (!hasRole('administrator')) {
            $userId = getCurrentUserId();
            $sql .= " AND (
                EXISTS (
                    SELECT 1 FROM employee_territories et
                    WHERE et.user_id = ? AND et.state_id = l.state_id AND et.city_id IS NULL AND et.deleted_at IS NULL
                )
                OR
                EXISTS (
                    SELECT 1 FROM employee_territories et
                    WHERE et.user_id = ? AND et.state_id = l.state_id AND et.city_id = l.city_id AND et.deleted_at IS NULL
                )
            )";
            $params = [$userId, $userId];
        } else {
            $params = [];
        }
        
        $sql .= " ORDER BY cl.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Get recent call logs for dashboard
     * 
     * @param int $employeeId Employee ID filter (0 for all)
     * @param int $limit Number of records to return
     * @return array Array of call logs
     */
    public function getRecentCallLogs($employeeId = 0, $limit = 10) {
        $userId = getCurrentUserId();
        $isAdmin = hasRole('administrator');
        
        $sql = "SELECT cl.*, l.name as lead_name, l.status as status, u.name as created_by_name
                FROM {$this->table} cl
                LEFT JOIN leads l ON cl.lead_id = l.id
                LEFT JOIN users u ON cl.created_by = u.id
                WHERE cl.deleted_at IS NULL";
        
        $params = [];
        
        // Apply employee filter
        if ($employeeId > 0) {
            $sql .= " AND (cl.created_by = ? OR l.assigned_to = ?)";
            $params[] = $employeeId;
            $params[] = $employeeId;
        } else if (!$isAdmin) {
            // If not admin and no specific employee selected, only show calls made by the user or for leads assigned to the user
            $sql .= " AND (cl.created_by = ? OR l.assigned_to = ?)";
            $params[] = $userId;
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY cl.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Get call logs by date range
     */
    public function getByDateRange($dateFrom, $dateTo, $page = 1, $limit = RECORDS_PER_PAGE, $userId = null) {
        $offset = ($page - 1) * $limit;
        $where = "cl.deleted_at IS NULL";
        $params = [];
        
        if ($dateFrom) {
            $where .= " AND DATE(cl.created_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND DATE(cl.created_at) <= ?";
            $params[] = $dateTo;
        }
        
        if ($userId) {
            $where .= " AND cl.created_by = ?";
            $params[] = $userId;
        }
        
        // Apply territory restrictions for employees
        if (!hasRole('administrator')) {
            $currentUserId = getCurrentUserId();
            $where .= " AND (
                EXISTS (
                    SELECT 1 FROM employee_territories et
                    WHERE et.user_id = ? AND et.state_id = l.state_id AND et.city_id IS NULL AND et.deleted_at IS NULL
                )
                OR
                EXISTS (
                    SELECT 1 FROM employee_territories et
                    WHERE et.user_id = ? AND et.state_id = l.state_id AND et.city_id = l.city_id AND et.deleted_at IS NULL
                )
            )";
            $params[] = $currentUserId;
            $params[] = $currentUserId;
        }
        
        $sql = "SELECT cl.*, l.name AS lead_name, l.id AS lead_id, u.name AS created_by_name
                FROM {$this->table} cl
                JOIN leads l ON cl.lead_id = l.id
                LEFT JOIN users u ON cl.created_by = u.id
                WHERE {$where}
                ORDER BY cl.created_at DESC
                LIMIT ?, ?";
        
        $params[] = $offset;
        $params[] = $limit;
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Count call logs by date range
     */
    public function countByDateRange($dateFrom, $dateTo, $userId = null) {
        $where = "cl.deleted_at IS NULL";
        $params = [];
        
        if ($dateFrom) {
            $where .= " AND DATE(cl.created_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND DATE(cl.created_at) <= ?";
            $params[] = $dateTo;
        }
        
        if ($userId) {
            $where .= " AND cl.created_by = ?";
            $params[] = $userId;
        }
        
        // Apply territory restrictions for employees
        if (!hasRole('administrator')) {
            $currentUserId = getCurrentUserId();
            $where .= " AND (
                EXISTS (
                    SELECT 1 FROM employee_territories et
                    WHERE et.user_id = ? AND et.state_id = l.state_id AND et.city_id IS NULL AND et.deleted_at IS NULL
                )
                OR
                EXISTS (
                    SELECT 1 FROM employee_territories et
                    WHERE et.user_id = ? AND et.state_id = l.state_id AND et.city_id = l.city_id AND et.deleted_at IS NULL
                )
            )";
            $params[] = $currentUserId;
            $params[] = $currentUserId;
        }
        
        $sql = "SELECT COUNT(*)
                FROM {$this->table} cl
                JOIN leads l ON cl.lead_id = l.id
                WHERE {$where}";
        
        return $this->db->getValue($sql, $params);
    }
    
    /**
     * Get call log stats by date range
     */
    public function getStatsByDateRange($dateFrom, $dateTo, $userId = null) {
        $where = "cl.deleted_at IS NULL";
        $params = [];
        
        if ($dateFrom) {
            $where .= " AND DATE(cl.created_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND DATE(cl.created_at) <= ?";
            $params[] = $dateTo;
        }
        
        if ($userId) {
            $where .= " AND cl.created_by = ?";
            $params[] = $userId;
        }
        
        // Apply territory restrictions for employees
        if (!hasRole('administrator')) {
            $currentUserId = getCurrentUserId();
            $where .= " AND (
                EXISTS (
                    SELECT 1 FROM employee_territories et
                    WHERE et.user_id = ? AND et.state_id = l.state_id AND et.city_id IS NULL AND et.deleted_at IS NULL
                )
                OR
                EXISTS (
                    SELECT 1 FROM employee_territories et
                    WHERE et.user_id = ? AND et.state_id = l.state_id AND et.city_id = l.city_id AND et.deleted_at IS NULL
                )
            )";
            $params[] = $currentUserId;
            $params[] = $currentUserId;
        }
        
        $sql = "SELECT
                COUNT(*) AS total_calls,
                SUM(CASE WHEN cl.status = 'new' THEN 1 ELSE 0 END) AS new_calls,
                SUM(CASE WHEN cl.status = 'follow_up' THEN 1 ELSE 0 END) AS follow_ups,
                SUM(CASE WHEN cl.status = 'not_attend' THEN 1 ELSE 0 END) AS not_attend,
                SUM(CASE WHEN cl.status = 'wrong_number' THEN 1 ELSE 0 END) AS wrong_number,
                SUM(CASE WHEN cl.status = 'other' THEN 1 ELSE 0 END) AS other,
                SUM(CASE WHEN cl.status = 'dead' THEN 1 ELSE 0 END) AS dead,
                SUM(CASE WHEN cl.status = 'interested' THEN 1 ELSE 0 END) AS interested,
                SUM(CASE WHEN cl.status = 'win' THEN 1 ELSE 0 END) AS wins
                FROM {$this->table} cl
                JOIN leads l ON cl.lead_id = l.id
                WHERE {$where}";
        
        return $this->db->getRow($sql, $params);
    }
    
    /**
     * Create a new call log
     * 
     * @param array $data Call log data
     * @return int|bool ID of created call log or false on failure
     */
    public function createCallLog($data) {
        return $this->create($data);
    }
}
