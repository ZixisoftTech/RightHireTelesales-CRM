<?php
/**
 * Lead Model
 * 
 * This model handles all lead-related database operations.
 */

require_once 'Model.php';

class Lead extends Model {
    protected $table = 'leads';
    protected $fillable = [
        'name', 'email', 'phone', 'address', 'state_id', 'city_id',
        'status', 'other_reason', 'follow_up_date', 'remarks', 'assigned_to',
        'created_by', 'updated_by'
    ];
    
    /**
     * Get all leads with related data
     */
    public function getAllWithRelations($filters = [], $page = 1, $limit = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $where = "l.deleted_at IS NULL";
        $params = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $where .= " AND l.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['state_id'])) {
            $where .= " AND l.state_id = ?";
            $params[] = $filters['state_id'];
        }
        
        if (!empty($filters['city_id'])) {
            $where .= " AND l.city_id = ?";
            $params[] = $filters['city_id'];
        }
        
        if (!empty($filters['assigned_to'])) {
            $where .= " AND l.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }
        
        if (!empty($filters['date_from'])) {
            $where .= " AND DATE(l.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where .= " AND DATE(l.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $where .= " AND (l.name LIKE ? OR l.email LIKE ? OR l.phone LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        // Apply territory restrictions for employees
        if (!hasRole('administrator')) {
            $userId = getCurrentUserId();
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
            $params[] = $userId;
            $params[] = $userId;
        }
        
        $sql = "SELECT l.*, s.name AS state_name, c.name AS city_name, u.name AS assigned_to_name, cu.name AS created_by
                FROM {$this->table} l
                JOIN states s ON l.state_id = s.id
                JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                LEFT JOIN users cu ON l.created_by = cu.id
                WHERE {$where}
                ORDER BY l.id DESC
                LIMIT ?, ?";
        
        $params[] = $offset;
        $params[] = $limit;
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Count filtered leads
     */
    public function countFiltered($filters = []) {
        $where = "deleted_at IS NULL";
        $params = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['state_id'])) {
            $where .= " AND state_id = ?";
            $params[] = $filters['state_id'];
        }
        
        if (!empty($filters['city_id'])) {
            $where .= " AND city_id = ?";
            $params[] = $filters['city_id'];
        }
        
        if (!empty($filters['assigned_to'])) {
            $where .= " AND assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }
        
        if (!empty($filters['date_from'])) {
            $where .= " AND DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where .= " AND DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $where .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        // Apply territory restrictions for employees
        if (!hasRole('administrator')) {
            $userId = getCurrentUserId();
            $where .= " AND (
                EXISTS (
                    SELECT 1 FROM employee_territories et
                    WHERE et.user_id = ? AND et.state_id = state_id AND et.city_id IS NULL AND et.deleted_at IS NULL
                )
                OR
                EXISTS (
                    SELECT 1 FROM employee_territories et
                    WHERE et.user_id = ? AND et.state_id = state_id AND et.city_id = city_id AND et.deleted_at IS NULL
                )
            )";
            $params[] = $userId;
            $params[] = $userId;
        }
        
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$where}";
        
        return $this->db->getValue($sql, $params);
    }
    
    /**
     * Get lead with related data
     */
    public function getWithRelations($id) {
        $sql = "SELECT l.*, s.name AS state_name, c.name AS city_name, u.name AS assigned_to_name, cu.name AS created_by
                FROM {$this->table} l
                JOIN states s ON l.state_id = s.id
                JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                LEFT JOIN users cu ON l.created_by = cu.id
                WHERE l.id = ? AND l.deleted_at IS NULL";
        
        return $this->db->getRow($sql, [$id]);
    }
    
    /**
     * Update lead status
     */
    public function updateStatus($id, $status, $otherReason = null, $followUpDate = null, $remarks = null) {
        $data = [
            'status' => $status,
            'other_reason' => $otherReason,
            'follow_up_date' => $followUpDate,
            'remarks' => $remarks
        ];
        
        // Create call log
        $callLogData = [
            'lead_id' => $id,
            'status' => $status,
            'other_reason' => $otherReason,
            'follow_up_date' => $followUpDate,
            'remarks' => $remarks,
            'created_by' => getCurrentUserId()
        ];
        
        $this->db->insert('call_logs', $callLogData);
        
        // Update lead
        return $this->update($id, $data);
    }
    
    /**
     * Get lead stats
     */
    public function getStats() {
        $where = "deleted_at IS NULL";
        $params = [];
        
        // Apply territory restrictions for employees
        if (!hasRole('administrator')) {
            $userId = getCurrentUserId();
            $where .= " AND (
                EXISTS (
                    SELECT 1 FROM employee_territories et
                    WHERE et.user_id = ? AND et.state_id = state_id AND et.city_id IS NULL AND et.deleted_at IS NULL
                )
                OR
                EXISTS (
                    SELECT 1 FROM employee_territories et
                    WHERE et.user_id = ? AND et.state_id = state_id AND et.city_id = city_id AND et.deleted_at IS NULL
                )
            )";
            $params[] = $userId;
            $params[] = $userId;
        }
        
        $sql = "SELECT
                COUNT(*) AS total_leads,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS new_leads,
                SUM(CASE WHEN status = 'follow_up' THEN 1 ELSE 0 END) AS follow_ups,
                SUM(CASE WHEN status = 'not_attend' THEN 1 ELSE 0 END) AS not_attend,
                SUM(CASE WHEN status = 'wrong_number' THEN 1 ELSE 0 END) AS wrong_number,
                SUM(CASE WHEN status = 'other' THEN 1 ELSE 0 END) AS other,
                SUM(CASE WHEN status = 'dead' THEN 1 ELSE 0 END) AS dead,
                SUM(CASE WHEN status = 'interested' THEN 1 ELSE 0 END) AS interested,
                SUM(CASE WHEN status = 'win' THEN 1 ELSE 0 END) AS wins
                FROM {$this->table}
                WHERE {$where}";
        
        return $this->db->getRow($sql, $params);
    }
    
    /**
     * Get today's follow-ups
     */
    public function getTodayFollowUps() {
        $today = date('Y-m-d');
        $where = "status = 'follow_up' AND DATE(follow_up_date) = ? AND deleted_at IS NULL";
        $params = [$today];
        
        // Apply territory restrictions for employees
        if (!hasRole('administrator')) {
            $userId = getCurrentUserId();
            $where .= " AND (
                EXISTS (
                    SELECT 1 FROM employee_territories et
                    WHERE et.user_id = ? AND et.state_id = state_id AND et.city_id IS NULL AND et.deleted_at IS NULL
                )
                OR
                EXISTS (
                    SELECT 1 FROM employee_territories et
                    WHERE et.user_id = ? AND et.state_id = state_id AND et.city_id = city_id AND et.deleted_at IS NULL
                )
            )";
            $params[] = $userId;
            $params[] = $userId;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY follow_up_date ASC";
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Get daily call count
     */
    public function getDailyCallCount($days = 30) {
        $sql = "SELECT DATE(cl.created_at) AS date, COUNT(*) AS count
                FROM call_logs cl
                WHERE cl.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL ? DAY) AND cl.deleted_at IS NULL
                GROUP BY DATE(cl.created_at)
                ORDER BY DATE(cl.created_at) ASC";
        
        return $this->db->getRows($sql, [$days]);
    }
    
    /**
     * Export leads to CSV
     */
    public function exportToCSV($filters = []) {
        $where = "l.deleted_at IS NULL";
        $params = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $where .= " AND l.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['state_id'])) {
            $where .= " AND l.state_id = ?";
            $params[] = $filters['state_id'];
        }
        
        if (!empty($filters['city_id'])) {
            $where .= " AND l.city_id = ?";
            $params[] = $filters['city_id'];
        }
        
        if (!empty($filters['assigned_to'])) {
            $where .= " AND l.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }
        
        if (!empty($filters['date_from'])) {
            $where .= " AND DATE(l.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where .= " AND DATE(l.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $where .= " AND (l.name LIKE ? OR l.email LIKE ? OR l.phone LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        // Apply territory restrictions for employees
        if (!hasRole('administrator')) {
            $userId = getCurrentUserId();
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
            $params[] = $userId;
            $params[] = $userId;
        }
        
        $sql = "SELECT l.id, l.name, l.email, l.phone, l.address, s.name AS state_name, c.name AS city_name,
                l.status, l.other_reason, l.follow_up_date, l.remarks, u.name AS assigned_to_name,
                l.created_at, cu.name AS created_by_name
                FROM {$this->table} l
                JOIN states s ON l.state_id = s.id
                JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                LEFT JOIN users cu ON l.created_by = cu.id
                WHERE {$where}
                ORDER BY l.id DESC";
        
        $leads = $this->db->getRows($sql, $params);
        
        $headers = ['ID', 'Name', 'Email', 'Phone', 'Address', 'State', 'City', 'Status', 'Other Reason', 'Follow-up Date', 'Remarks', 'Assigned To', 'Created At', 'Created By'];
        $data = [];
        
        foreach ($leads as $lead) {
            $data[] = [
                $lead['id'],
                $lead['name'],
                $lead['email'],
                $lead['phone'],
                $lead['address'],
                $lead['state_name'],
                $lead['city_name'],
                ucfirst(str_replace('_', ' ', $lead['status'])),
                $lead['other_reason'],
                $lead['follow_up_date'] ? formatDateTime($lead['follow_up_date']) : '',
                $lead['remarks'],
                $lead['assigned_to_name'],
                formatDateTime($lead['created_at']),
                $lead['created_by_name']
            ];
        }
        
        return [
            'headers' => $headers,
            'data' => $data
        ];
    }
    
    /**
     * Import leads from CSV
     */
    public function importFromCSV($data) {
        $this->db->beginTransaction();
        
        try {
            $count = 0;
            
            foreach ($data as $row) {
                $leadData = [
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'phone' => $row['phone'],
                    'address' => $row['address'],
                    'state_id' => $row['state_id'],
                    'city_id' => $row['city_id'],
                    'remarks' => $row['remarks'],
                    'status' => 'new',
                    'created_by' => getCurrentUserId()
                ];
                
                $this->create($leadData);
                $count++;
            }
            
            $this->db->commit();
            return $count;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}

