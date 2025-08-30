<?php
/**
 * Lead Model
 * 
 * This model handles all lead-related database operations.
 */

require_once 'Model.php';

class Lead extends Model {
    protected $table = 'leads';
    protected $fillable = ['name', 'email', 'phone', 'address', 'state_id', 'city_id', 'status', 'other_reason', 'follow_up_date', 'remarks', 'assigned_to', 'created_by', 'updated_by'];
    
    /**
     * Get all leads with related data
     */
    public function getAllWithRelatedData($page = 1, $limit = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT l.*, s.name AS state_name, c.name AS city_name, u.name AS assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.deleted_at IS NULL
                ORDER BY l.id DESC
                LIMIT ?, ?";
        
        return $this->db->getRows($sql, [$offset, $limit]);
    }
    
    /**
     * Get leads for employee
     */
    public function getLeadsForEmployee($userId, $page = 1, $limit = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        // Check if user has any territories
        $sql = "SELECT COUNT(*) FROM employee_territories 
                WHERE user_id = ? AND deleted_at IS NULL";
        
        $hasTerritory = $this->db->getValue($sql, [$userId]) > 0;
        
        if (!$hasTerritory) {
            // If user has no territories, return empty array
            return [];
        }
        
        // Get territories for user
        $territorySql = "SELECT state_id, city_id FROM employee_territories WHERE user_id = ? AND deleted_at IS NULL";
        $territories = $this->db->getRows($territorySql, [$userId]);
        
        // Build query conditions for territories
        $conditions = [];
        $params = [];
        
        foreach ($territories as $territory) {
            if ($territory['city_id']) {
                // Territory with specific city
                $conditions[] = "(l.state_id = ? AND l.city_id = ?)";
                $params[] = $territory['state_id'];
                $params[] = $territory['city_id'];
            } else {
                // Territory with entire state
                $conditions[] = "(l.state_id = ?)";
                $params[] = $territory['state_id'];
            }
        }
        
        // Add assigned leads
        $conditions[] = "(l.assigned_to = ?)";
        $params[] = $userId;
        
        // Build final query
        $sql = "SELECT l.*, s.name AS state_name, c.name AS city_name, u.name AS assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.deleted_at IS NULL AND (" . implode(" OR ", $conditions) . ")
                ORDER BY l.id DESC
                LIMIT ?, ?";
        
        $params[] = $offset;
        $params[] = $limit;
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Count leads for employee
     */
    public function countLeadsForEmployee($userId) {
        // Check if user has any territories
        $sql = "SELECT COUNT(*) FROM employee_territories 
                WHERE user_id = ? AND deleted_at IS NULL";
        
        $hasTerritory = $this->db->getValue($sql, [$userId]) > 0;
        
        if (!$hasTerritory) {
            // If user has no territories, return 0
            return 0;
        }
        
        // Get territories for user
        $territorySql = "SELECT state_id, city_id FROM employee_territories WHERE user_id = ? AND deleted_at IS NULL";
        $territories = $this->db->getRows($territorySql, [$userId]);
        
        // Build query conditions for territories
        $conditions = [];
        $params = [];
        
        foreach ($territories as $territory) {
            if ($territory['city_id']) {
                // Territory with specific city
                $conditions[] = "(l.state_id = ? AND l.city_id = ?)";
                $params[] = $territory['state_id'];
                $params[] = $territory['city_id'];
            } else {
                // Territory with entire state
                $conditions[] = "(l.state_id = ?)";
                $params[] = $territory['state_id'];
            }
        }
        
        // Add assigned leads
        $conditions[] = "(l.assigned_to = ?)";
        $params[] = $userId;
        
        // Build final query
        $sql = "SELECT COUNT(*) FROM {$this->table} l
                WHERE l.deleted_at IS NULL AND (" . implode(" OR ", $conditions) . ")";
        
        return $this->db->getValue($sql, $params);
    }
    
    /**
     * Get lead by ID with related data
     */
    public function getByIdWithRelatedData($id) {
        $sql = "SELECT l.*, s.name AS state_name, c.name AS city_name, u.name AS assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.id = ? AND l.deleted_at IS NULL";
        
        return $this->db->getRow($sql, [$id]);
    }
    
    /**
     * Get call logs for lead
     */
    public function getCallLogs($leadId) {
        $sql = "SELECT cl.*, u.name AS employee_name
                FROM call_logs cl
                LEFT JOIN users u ON cl.created_by = u.id
                WHERE cl.lead_id = ? AND cl.deleted_at IS NULL
                ORDER BY cl.created_at DESC";
        
        return $this->db->getRows($sql, [$leadId]);
    }
    
    /**
     * Add call log
     */
    public function addCallLog($leadId, $status, $otherReason = null, $followUpDate = null, $remarks = null) {
        $data = [
            'lead_id' => $leadId,
            'status' => $status,
            'other_reason' => $otherReason,
            'follow_up_date' => $followUpDate,
            'remarks' => $remarks,
            'created_by' => getCurrentUserId()
        ];
        
        return $this->db->insert('call_logs', $data);
    }
    
    /**
     * Update lead status
     */
    public function updateStatus($id, $status, $otherReason = null, $followUpDate = null, $remarks = null) {
        $data = [
            'status' => $status,
            'updated_by' => getCurrentUserId()
        ];
        
        if ($status === 'other') {
            $data['other_reason'] = $otherReason;
        } else {
            $data['other_reason'] = null;
        }
        
        if ($status === 'follow_up') {
            $data['follow_up_date'] = $followUpDate;
        } else {
            $data['follow_up_date'] = null;
        }
        
        $result = $this->update($id, $data);
        
        if ($result) {
            // Add call log
            $this->addCallLog($id, $status, $otherReason, $followUpDate, $remarks);
        }
        
        return $result;
    }
    
    /**
     * Get leads by status
     */
    public function getByStatus($status, $limit = 5) {
        $sql = "SELECT l.*, s.name AS state_name, c.name AS city_name, u.name AS assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.status = ? AND l.deleted_at IS NULL
                ORDER BY l.id DESC
                LIMIT ?";
        
        return $this->db->getRows($sql, [$status, $limit]);
    }
    
    /**
     * Get follow-up leads
     */
    public function getFollowUpLeads($limit = 5) {
        $sql = "SELECT l.*, s.name AS state_name, c.name AS city_name, u.name AS assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.status = 'follow_up' AND l.follow_up_date <= NOW() AND l.deleted_at IS NULL
                ORDER BY l.follow_up_date ASC
                LIMIT ?";
        
        return $this->db->getRows($sql, [$limit]);
    }
    
    /**
     * Get follow-up leads for employee
     */
    public function getFollowUpLeadsForEmployee($userId, $limit = 5) {
        // Check if user has any territories
        $sql = "SELECT COUNT(*) FROM employee_territories 
                WHERE user_id = ? AND deleted_at IS NULL";
        
        $hasTerritory = $this->db->getValue($sql, [$userId]) > 0;
        
        if (!$hasTerritory) {
            // If user has no territories, return empty array
            return [];
        }
        
        // Get territories for user
        $territorySql = "SELECT state_id, city_id FROM employee_territories WHERE user_id = ? AND deleted_at IS NULL";
        $territories = $this->db->getRows($territorySql, [$userId]);
        
        // Build query conditions for territories
        $conditions = [];
        $params = [];
        
        foreach ($territories as $territory) {
            if ($territory['city_id']) {
                // Territory with specific city
                $conditions[] = "(l.state_id = ? AND l.city_id = ?)";
                $params[] = $territory['state_id'];
                $params[] = $territory['city_id'];
            } else {
                // Territory with entire state
                $conditions[] = "(l.state_id = ?)";
                $params[] = $territory['state_id'];
            }
        }
        
        // Add assigned leads
        $conditions[] = "(l.assigned_to = ?)";
        $params[] = $userId;
        
        // Build final query
        $sql = "SELECT l.*, s.name AS state_name, c.name AS city_name, u.name AS assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.status = 'follow_up' AND l.follow_up_date <= NOW() AND l.deleted_at IS NULL 
                AND (" . implode(" OR ", $conditions) . ")
                ORDER BY l.follow_up_date ASC
                LIMIT ?";
        
        $params[] = $limit;
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Get lead counts by status
     */
    public function getCountsByStatus() {
        $sql = "SELECT 
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS new,
                SUM(CASE WHEN status = 'follow_up' THEN 1 ELSE 0 END) AS follow_up,
                SUM(CASE WHEN status = 'not_attend' THEN 1 ELSE 0 END) AS not_attend,
                SUM(CASE WHEN status = 'wrong_number' THEN 1 ELSE 0 END) AS wrong_number,
                SUM(CASE WHEN status = 'other' THEN 1 ELSE 0 END) AS other,
                SUM(CASE WHEN status = 'dead' THEN 1 ELSE 0 END) AS dead,
                SUM(CASE WHEN status = 'interested' THEN 1 ELSE 0 END) AS interested,
                SUM(CASE WHEN status = 'win' THEN 1 ELSE 0 END) AS win,
                COUNT(*) AS total
                FROM {$this->table}
                WHERE deleted_at IS NULL";
        
        return $this->db->getRow($sql);
    }
    
    /**
     * Get lead counts by status for employee
     */
    public function getCountsByStatusForEmployee($userId) {
        // Check if user has any territories
        $sql = "SELECT COUNT(*) FROM employee_territories 
                WHERE user_id = ? AND deleted_at IS NULL";
        
        $hasTerritory = $this->db->getValue($sql, [$userId]) > 0;
        
        if (!$hasTerritory) {
            // If user has no territories, return empty counts
            return [
                'new' => 0,
                'follow_up' => 0,
                'not_attend' => 0,
                'wrong_number' => 0,
                'other' => 0,
                'dead' => 0,
                'interested' => 0,
                'win' => 0,
                'total' => 0
            ];
        }
        
        // Get territories for user
        $territorySql = "SELECT state_id, city_id FROM employee_territories WHERE user_id = ? AND deleted_at IS NULL";
        $territories = $this->db->getRows($territorySql, [$userId]);
        
        // Build query conditions for territories
        $conditions = [];
        $params = [];
        
        foreach ($territories as $territory) {
            if ($territory['city_id']) {
                // Territory with specific city
                $conditions[] = "(l.state_id = ? AND l.city_id = ?)";
                $params[] = $territory['state_id'];
                $params[] = $territory['city_id'];
            } else {
                // Territory with entire state
                $conditions[] = "(l.state_id = ?)";
                $params[] = $territory['state_id'];
            }
        }
        
        // Add assigned leads
        $conditions[] = "(l.assigned_to = ?)";
        $params[] = $userId;
        
        // Build final query
        $sql = "SELECT 
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS new,
                SUM(CASE WHEN status = 'follow_up' THEN 1 ELSE 0 END) AS follow_up,
                SUM(CASE WHEN status = 'not_attend' THEN 1 ELSE 0 END) AS not_attend,
                SUM(CASE WHEN status = 'wrong_number' THEN 1 ELSE 0 END) AS wrong_number,
                SUM(CASE WHEN status = 'other' THEN 1 ELSE 0 END) AS other,
                SUM(CASE WHEN status = 'dead' THEN 1 ELSE 0 END) AS dead,
                SUM(CASE WHEN status = 'interested' THEN 1 ELSE 0 END) AS interested,
                SUM(CASE WHEN status = 'win' THEN 1 ELSE 0 END) AS win,
                COUNT(*) AS total
                FROM {$this->table} l
                WHERE l.deleted_at IS NULL AND (" . implode(" OR ", $conditions) . ")";
        
        return $this->db->getRow($sql, $params);
    }
    
    /**
     * Get filtered leads for reports
     */
    public function getFilteredLeads($page = 1, $stateId = 0, $cityId = 0, $status = '', $employeeId = 0, $startDate = '', $endDate = '', $limit = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $conditions = [];
        $params = [];
        
        // Add filters
        if ($stateId) {
            $conditions[] = "l.state_id = ?";
            $params[] = $stateId;
        }
        
        if ($cityId) {
            $conditions[] = "l.city_id = ?";
            $params[] = $cityId;
        }
        
        if ($status) {
            $conditions[] = "l.status = ?";
            $params[] = $status;
        }
        
        if ($employeeId) {
            $conditions[] = "l.assigned_to = ?";
            $params[] = $employeeId;
        }
        
        if ($startDate) {
            $conditions[] = "l.created_at >= ?";
            $params[] = $startDate . ' 00:00:00';
        }
        
        if ($endDate) {
            $conditions[] = "l.created_at <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        
        // Build WHERE clause
        $whereClause = "l.deleted_at IS NULL";
        if (!empty($conditions)) {
            $whereClause .= " AND " . implode(" AND ", $conditions);
        }
        
        // Build final query
        $sql = "SELECT l.*, s.name AS state_name, c.name AS city_name, u.name AS employee_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE {$whereClause}
                ORDER BY l.id DESC
                LIMIT ?, ?";
        
        $params[] = $offset;
        $params[] = $limit;
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Count filtered leads for reports
     */
    public function countFilteredLeads($stateId = 0, $cityId = 0, $status = '', $employeeId = 0, $startDate = '', $endDate = '') {
        $conditions = [];
        $params = [];
        
        // Add filters
        if ($stateId) {
            $conditions[] = "l.state_id = ?";
            $params[] = $stateId;
        }
        
        if ($cityId) {
            $conditions[] = "l.city_id = ?";
            $params[] = $cityId;
        }
        
        if ($status) {
            $conditions[] = "l.status = ?";
            $params[] = $status;
        }
        
        if ($employeeId) {
            $conditions[] = "l.assigned_to = ?";
            $params[] = $employeeId;
        }
        
        if ($startDate) {
            $conditions[] = "l.created_at >= ?";
            $params[] = $startDate . ' 00:00:00';
        }
        
        if ($endDate) {
            $conditions[] = "l.created_at <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        
        // Build WHERE clause
        $whereClause = "l.deleted_at IS NULL";
        if (!empty($conditions)) {
            $whereClause .= " AND " . implode(" AND ", $conditions);
        }
        
        // Build final query
        $sql = "SELECT COUNT(*) FROM {$this->table} l WHERE {$whereClause}";
        
        return $this->db->getValue($sql, $params);
    }
    
    /**
     * Get filtered leads for export
     */
    public function getFilteredLeadsForExport($stateId = 0, $cityId = 0, $status = '', $employeeId = 0, $startDate = '', $endDate = '') {
        $conditions = [];
        $params = [];
        
        // Add filters
        if ($stateId) {
            $conditions[] = "l.state_id = ?";
            $params[] = $stateId;
        }
        
        if ($cityId) {
            $conditions[] = "l.city_id = ?";
            $params[] = $cityId;
        }
        
        if ($status) {
            $conditions[] = "l.status = ?";
            $params[] = $status;
        }
        
        if ($employeeId) {
            $conditions[] = "l.assigned_to = ?";
            $params[] = $employeeId;
        }
        
        if ($startDate) {
            $conditions[] = "l.created_at >= ?";
            $params[] = $startDate . ' 00:00:00';
        }
        
        if ($endDate) {
            $conditions[] = "l.created_at <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        
        // Build WHERE clause
        $whereClause = "l.deleted_at IS NULL";
        if (!empty($conditions)) {
            $whereClause .= " AND " . implode(" AND ", $conditions);
        }
        
        // Build final query
        $sql = "SELECT l.*, s.name AS state_name, c.name AS city_name, u.name AS employee_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE {$whereClause}
                ORDER BY l.id DESC";
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Get filtered call logs for reports
     */
    public function getFilteredCallLogs($page = 1, $leadId = 0, $status = '', $employeeId = 0, $startDate = '', $endDate = '', $limit = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $conditions = [];
        $params = [];
        
        // Add filters
        if ($leadId) {
            $conditions[] = "cl.lead_id = ?";
            $params[] = $leadId;
        }
        
        if ($status) {
            $conditions[] = "cl.status = ?";
            $params[] = $status;
        }
        
        if ($employeeId) {
            $conditions[] = "cl.created_by = ?";
            $params[] = $employeeId;
        }
        
        if ($startDate) {
            $conditions[] = "cl.created_at >= ?";
            $params[] = $startDate . ' 00:00:00';
        }
        
        if ($endDate) {
            $conditions[] = "cl.created_at <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        
        // Build WHERE clause
        $whereClause = "cl.deleted_at IS NULL";
        if (!empty($conditions)) {
            $whereClause .= " AND " . implode(" AND ", $conditions);
        }
        
        // Build final query
        $sql = "SELECT cl.*, l.name AS lead_name, u.name AS employee_name
                FROM call_logs cl
                LEFT JOIN leads l ON cl.lead_id = l.id
                LEFT JOIN users u ON cl.created_by = u.id
                WHERE {$whereClause}
                ORDER BY cl.id DESC
                LIMIT ?, ?";
        
        $params[] = $offset;
        $params[] = $limit;
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Count filtered call logs for reports
     */
    public function countFilteredCallLogs($leadId = 0, $status = '', $employeeId = 0, $startDate = '', $endDate = '') {
        $conditions = [];
        $params = [];
        
        // Add filters
        if ($leadId) {
            $conditions[] = "cl.lead_id = ?";
            $params[] = $leadId;
        }
        
        if ($status) {
            $conditions[] = "cl.status = ?";
            $params[] = $status;
        }
        
        if ($employeeId) {
            $conditions[] = "cl.created_by = ?";
            $params[] = $employeeId;
        }
        
        if ($startDate) {
            $conditions[] = "cl.created_at >= ?";
            $params[] = $startDate . ' 00:00:00';
        }
        
        if ($endDate) {
            $conditions[] = "cl.created_at <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        
        // Build WHERE clause
        $whereClause = "cl.deleted_at IS NULL";
        if (!empty($conditions)) {
            $whereClause .= " AND " . implode(" AND ", $conditions);
        }
        
        // Build final query
        $sql = "SELECT COUNT(*) FROM call_logs cl WHERE {$whereClause}";
        
        return $this->db->getValue($sql, $params);
    }
    
    /**
     * Get filtered call logs for export
     */
    public function getFilteredCallLogsForExport($leadId = 0, $status = '', $employeeId = 0, $startDate = '', $endDate = '') {
        $conditions = [];
        $params = [];
        
        // Add filters
        if ($leadId) {
            $conditions[] = "cl.lead_id = ?";
            $params[] = $leadId;
        }
        
        if ($status) {
            $conditions[] = "cl.status = ?";
            $params[] = $status;
        }
        
        if ($employeeId) {
            $conditions[] = "cl.created_by = ?";
            $params[] = $employeeId;
        }
        
        if ($startDate) {
            $conditions[] = "cl.created_at >= ?";
            $params[] = $startDate . ' 00:00:00';
        }
        
        if ($endDate) {
            $conditions[] = "cl.created_at <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        
        // Build WHERE clause
        $whereClause = "cl.deleted_at IS NULL";
        if (!empty($conditions)) {
            $whereClause .= " AND " . implode(" AND ", $conditions);
        }
        
        // Build final query
        $sql = "SELECT cl.*, l.name AS lead_name, u.name AS employee_name
                FROM call_logs cl
                LEFT JOIN leads l ON cl.lead_id = l.id
                LEFT JOIN users u ON cl.created_by = u.id
                WHERE {$whereClause}
                ORDER BY cl.id DESC";
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Get employee performance data for reports
     */
    public function getEmployeePerformance($employeeId = 0, $startDate = '', $endDate = '') {
        $conditions = [];
        $params = [];
        
        // Add filters
        if ($employeeId) {
            $conditions[] = "u.id = ?";
            $params[] = $employeeId;
        } else {
            $conditions[] = "u.role = 'employee'";
        }
        
        if ($startDate) {
            $conditions[] = "l.created_at >= ?";
            $params[] = $startDate . ' 00:00:00';
        }
        
        if ($endDate) {
            $conditions[] = "l.created_at <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        
        // Build WHERE clause
        $whereClause = "u.deleted_at IS NULL AND u.status = 1";
        if (!empty($conditions)) {
            $whereClause .= " AND " . implode(" AND ", $conditions);
        }
        
        // Build final query
        $sql = "SELECT 
                u.id,
                u.name,
                COUNT(l.id) AS total_leads,
                SUM(CASE WHEN l.status = 'new' THEN 1 ELSE 0 END) AS new,
                SUM(CASE WHEN l.status = 'follow_up' THEN 1 ELSE 0 END) AS follow_up,
                SUM(CASE WHEN l.status = 'not_attend' THEN 1 ELSE 0 END) AS not_attend,
                SUM(CASE WHEN l.status = 'wrong_number' THEN 1 ELSE 0 END) AS wrong_number,
                SUM(CASE WHEN l.status = 'other' THEN 1 ELSE 0 END) AS other,
                SUM(CASE WHEN l.status = 'dead' THEN 1 ELSE 0 END) AS dead,
                SUM(CASE WHEN l.status = 'interested' THEN 1 ELSE 0 END) AS interested,
                SUM(CASE WHEN l.status = 'win' THEN 1 ELSE 0 END) AS win,
                ROUND(SUM(CASE WHEN l.status = 'win' THEN 1 ELSE 0 END) / COUNT(l.id) * 100, 2) AS win_rate
                FROM users u
                LEFT JOIN leads l ON u.id = l.assigned_to AND l.deleted_at IS NULL
                WHERE {$whereClause}
                GROUP BY u.id
                ORDER BY win_rate DESC, total_leads DESC";
        
        return $this->db->getRows($sql, $params);
    }
}

