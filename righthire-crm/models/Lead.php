<?php
/**
 * Lead Model
 * 
 * This model handles all lead-related database operations.
 */

require_once 'Model.php';

class Lead extends Model {
    protected $table = 'leads';
    protected $fillable = ['name', 'email', 'phone', 'address', 'state_id', 'city_id', 'status', 'other_reason', 'follow_up_date', 'remarks', 'assigned_to'];
    
    /**
     * Override the create method to handle city_id being NULL and assigned_to validation
     * 
     * @param array $data Lead data
     * @return int|bool ID of created lead or false on failure
     */
    public function create($data) {
        // Handle assigned_to validation
        if (isset($data['assigned_to']) && ($data['assigned_to'] === '' || $data['assigned_to'] === 0)) {
            // If assigned_to is empty or 0, set it to NULL to satisfy foreign key constraint
            $data['assigned_to'] = null;
        }
        
        // Ensure city_id is not null if state_id is provided
        if (isset($data['state_id']) && (!isset($data['city_id']) || $data['city_id'] === null || $data['city_id'] === '')) {
            // If no city_id is provided but state_id exists, set city_id to a default value
            // This is a workaround for the foreign key constraint
            // In a real-world scenario, we might want to create a "Default" or "Unknown" city for each state
            
            // For now, we'll try to find the first active city in the state
            $sql = "SELECT id FROM cities WHERE state_id = ? AND status = 1 AND deleted_at IS NULL ORDER BY id ASC LIMIT 1";
            $defaultCityId = $this->db->getValue($sql, [$data['state_id']]);
            
            if ($defaultCityId) {
                $data['city_id'] = $defaultCityId;
            } else {
                // If no cities exist for this state, we need to create one
                $sql = "INSERT INTO cities (state_id, name, status, created_by, created_at) 
                        VALUES (?, 'Default', 1, ?, NOW())";
                $this->db->query($sql, [$data['state_id'], getCurrentUserId()]);
                $data['city_id'] = $this->db->lastInsertId();
            }
        }
        
        // If city_id is still null after our attempts, log the error
        if (!isset($data['city_id']) || $data['city_id'] === null || $data['city_id'] === '') {
            error_log("Warning: Attempting to create lead with null city_id. Data: " . json_encode($data));
            
            // Add audit trail fields
            if (isLoggedIn()) {
                $data['created_by'] = getCurrentUserId();
            }
            
            // Filter data to only include fillable fields
            $filteredData = array_intersect_key($data, array_flip($this->fillable));
            
            // Prepare the SQL query
            $columns = implode(', ', array_keys($filteredData));
            $placeholders = implode(', ', array_fill(0, count($filteredData), '?'));
            
            $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
            
            // Execute the query
            try {
                $this->db->beginTransaction();
                $stmt = $this->db->query($sql, array_values($filteredData));
                $id = $this->db->lastInsertId();
                
                // Log audit
                if ($this->auditEnabled) {
                    $this->logAudit('create', $id, null, $filteredData);
                }
                
                $this->db->commit();
                return $id;
            } catch (Exception $e) {
                $this->db->rollBack();
                error_log("Error creating lead: " . $e->getMessage());
                return false;
            }
        }
        
        // If city_id is set, use the parent create method
        return parent::create($data);
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get leads based on filters
     * 
     * @param array $filters Filter parameters
     * @param bool $paginate Whether to paginate results
     * @param int $page Page number
     * @param int $limit Records per page
     * @return array Leads matching the filters
     */
    public function getLeads($filters = [], $paginate = true, $page = 1, $limit = RECORDS_PER_PAGE) {
        $params = [];
        
        $sql = "SELECT l.*, s.name as state_name, c.name as city_name, u.name as assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.deleted_at IS NULL";
        
        // Apply filters
        if (!empty($filters['state_id'])) {
            $sql .= " AND l.state_id = ?";
            $params[] = $filters['state_id'];
        }
        
        if (!empty($filters['city_id'])) {
            $sql .= " AND l.city_id = ?";
            $params[] = $filters['city_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['assigned_to'])) {
            $sql .= " AND l.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(l.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(l.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (l.name LIKE ? OR l.phone LIKE ? OR l.email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Check user role and restrict to assigned leads or territory leads if not admin
        if (!hasRole('administrator') && !isset($filters['assigned_to'])) {
            $userId = getCurrentUserId();
            $sql .= " AND (l.assigned_to = ? OR EXISTS (
                SELECT 1 FROM employee_territories et 
                WHERE et.user_id = ? 
                AND et.deleted_at IS NULL 
                AND (
                    (et.state_id = l.state_id AND et.city_id IS NULL) 
                    OR (et.state_id = l.state_id AND et.city_id = l.city_id)
                )
            ))";
            $params[] = $userId;
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY l.id DESC";
        
        if ($paginate) {
            $offset = ($page - 1) * $limit;
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $limit;
        }
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Count leads based on filters
     * 
     * @param array $filters Filter parameters
     * @return int Number of leads matching the filters
     */
    public function countLeads($filters = []) {
        $params = [];
        
        $sql = "SELECT COUNT(*) FROM {$this->table} l WHERE l.deleted_at IS NULL";
        
        // Apply filters
        if (!empty($filters['state_id'])) {
            $sql .= " AND l.state_id = ?";
            $params[] = $filters['state_id'];
        }
        
        if (!empty($filters['city_id'])) {
            $sql .= " AND l.city_id = ?";
            $params[] = $filters['city_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['assigned_to'])) {
            $sql .= " AND l.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(l.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(l.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (l.name LIKE ? OR l.phone LIKE ? OR l.email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Check user role and restrict to assigned leads or territory leads if not admin
        if (!hasRole('administrator') && !isset($filters['assigned_to'])) {
            $userId = getCurrentUserId();
            $sql .= " AND (l.assigned_to = ? OR EXISTS (
                SELECT 1 FROM employee_territories et 
                WHERE et.user_id = ? 
                AND et.deleted_at IS NULL 
                AND (
                    (et.state_id = l.state_id AND et.city_id IS NULL) 
                    OR (et.state_id = l.state_id AND et.city_id = l.city_id)
                )
            ))";
            $params[] = $userId;
            $params[] = $userId;
        }
        
        return $this->db->getValue($sql, $params);
    }
    
    /**
     * Get all leads with related data
     */
    public function getAllWithRelatedData($page = 1, $limit = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT l.*, s.name as state_name, c.name as city_name, u.name as assigned_to_name
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
     * Delete a lead (soft delete)
     * 
     * @param int $id Lead ID
     * @return bool Success or failure
     */
    public function deleteLead($id) {
        return $this->delete($id);
    }
    
    /**
     * Hard delete a lead
     * 
     * This method completely removes a lead from the database
     * instead of just marking it as deleted.
     * 
     * @param int $id Lead ID
     * @return bool Success or failure
     */
    public function hardDelete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }
    
    /**
     * Get lead by ID with related data
     */
    public function getByIdWithRelatedData($id) {
        $sql = "SELECT l.*, s.name as state_name, c.name as city_name, u.name as assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.id = ? AND l.deleted_at IS NULL";
        
        return $this->db->getRow($sql, [$id]);
    }
    
    /**
     * Get lead by ID
     * 
     * @param int $id Lead ID
     * @return array|bool Lead data or false if not found
     */
    public function getLeadById($id) {
        return $this->getByIdWithRelatedData($id);
    }
    
    /**
     * Get filtered leads
     */
    public function getFilteredLeads($page = 1, $stateId = 0, $cityId = 0, $status = '', $employeeId = 0, $startDate = '', $endDate = '', $limit = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $params = [];
        
        $sql = "SELECT l.*, s.name as state_name, c.name as city_name, u.name as assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.deleted_at IS NULL";
        
        // Apply filters
        if ($stateId > 0) {
            $sql .= " AND l.state_id = ?";
            $params[] = $stateId;
        }
        
        if ($cityId > 0) {
            $sql .= " AND l.city_id = ?";
            $params[] = $cityId;
        }
        
        if (!empty($status)) {
            $sql .= " AND l.status = ?";
            $params[] = $status;
        }
        
        if ($employeeId > 0) {
            $sql .= " AND l.assigned_to = ?";
            $params[] = $employeeId;
        }
        
        if (!empty($startDate)) {
            $sql .= " AND DATE(l.created_at) >= ?";
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $sql .= " AND DATE(l.created_at) <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY l.id DESC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $limit;
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Count filtered leads
     */
    public function countFilteredLeads($stateId = 0, $cityId = 0, $status = '', $employeeId = 0, $startDate = '', $endDate = '') {
        $params = [];
        
        $sql = "SELECT COUNT(*) FROM {$this->table} l WHERE l.deleted_at IS NULL";
        
        // Apply filters
        if ($stateId > 0) {
            $sql .= " AND l.state_id = ?";
            $params[] = $stateId;
        }
        
        if ($cityId > 0) {
            $sql .= " AND l.city_id = ?";
            $params[] = $cityId;
        }
        
        if (!empty($status)) {
            $sql .= " AND l.status = ?";
            $params[] = $status;
        }
        
        if ($employeeId > 0) {
            $sql .= " AND l.assigned_to = ?";
            $params[] = $employeeId;
        }
        
        if (!empty($startDate)) {
            $sql .= " AND DATE(l.created_at) >= ?";
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $sql .= " AND DATE(l.created_at) <= ?";
            $params[] = $endDate;
        }
        
        return $this->db->getValue($sql, $params);
    }
    
    /**
     * Get filtered leads for export
     */
    public function getFilteredLeadsForExport($stateId = 0, $cityId = 0, $status = '', $employeeId = 0, $startDate = '', $endDate = '') {
        $params = [];
        
        $sql = "SELECT l.*, s.name as state_name, c.name as city_name, u.name as assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.deleted_at IS NULL";
        
        // Apply filters
        if ($stateId > 0) {
            $sql .= " AND l.state_id = ?";
            $params[] = $stateId;
        }
        
        if ($cityId > 0) {
            $sql .= " AND l.city_id = ?";
            $params[] = $cityId;
        }
        
        if (!empty($status)) {
            $sql .= " AND l.status = ?";
            $params[] = $status;
        }
        
        if ($employeeId > 0) {
            $sql .= " AND l.assigned_to = ?";
            $params[] = $employeeId;
        }
        
        if (!empty($startDate)) {
            $sql .= " AND DATE(l.created_at) >= ?";
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $sql .= " AND DATE(l.created_at) <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY l.id DESC";
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Get filtered call logs
     */
    public function getFilteredCallLogs($page = 1, $leadId = 0, $status = '', $employeeId = 0, $startDate = '', $endDate = '', $limit = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $params = [];
        
        $sql = "SELECT cl.*, l.name as lead_name, u.name as created_by_name
                FROM call_logs cl
                LEFT JOIN leads l ON cl.lead_id = l.id
                LEFT JOIN users u ON cl.created_by = u.id
                WHERE cl.deleted_at IS NULL";
        
        // Apply filters
        if ($leadId > 0) {
            $sql .= " AND cl.lead_id = ?";
            $params[] = $leadId;
        }
        
        if (!empty($status)) {
            $sql .= " AND cl.status = ?";
            $params[] = $status;
        }
        
        if ($employeeId > 0) {
            $sql .= " AND cl.created_by = ?";
            $params[] = $employeeId;
        }
        
        if (!empty($startDate)) {
            $sql .= " AND DATE(cl.created_at) >= ?";
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $sql .= " AND DATE(cl.created_at) <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY cl.id DESC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $limit;
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Count filtered call logs
     */
    public function countFilteredCallLogs($leadId = 0, $status = '', $employeeId = 0, $startDate = '', $endDate = '') {
        $params = [];
        
        $sql = "SELECT COUNT(*) FROM call_logs cl WHERE cl.deleted_at IS NULL";
        
        // Apply filters
        if ($leadId > 0) {
            $sql .= " AND cl.lead_id = ?";
            $params[] = $leadId;
        }
        
        if (!empty($status)) {
            $sql .= " AND cl.status = ?";
            $params[] = $status;
        }
        
        if ($employeeId > 0) {
            $sql .= " AND cl.created_by = ?";
            $params[] = $employeeId;
        }
        
        if (!empty($startDate)) {
            $sql .= " AND DATE(cl.created_at) >= ?";
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $sql .= " AND DATE(cl.created_at) <= ?";
            $params[] = $endDate;
        }
        
        return $this->db->getValue($sql, $params);
    }
    
    /**
     * Get filtered call logs for export
     */
    public function getFilteredCallLogsForExport($leadId = 0, $status = '', $employeeId = 0, $startDate = '', $endDate = '') {
        $params = [];
        
        $sql = "SELECT cl.*, l.name as lead_name, u.name as created_by_name
                FROM call_logs cl
                LEFT JOIN leads l ON cl.lead_id = l.id
                LEFT JOIN users u ON cl.created_by = u.id
                WHERE cl.deleted_at IS NULL";
        
        // Apply filters
        if ($leadId > 0) {
            $sql .= " AND cl.lead_id = ?";
            $params[] = $leadId;
        }
        
        if (!empty($status)) {
            $sql .= " AND cl.status = ?";
            $params[] = $status;
        }
        
        if ($employeeId > 0) {
            $sql .= " AND cl.created_by = ?";
            $params[] = $employeeId;
        }
        
        if (!empty($startDate)) {
            $sql .= " AND DATE(cl.created_at) >= ?";
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $sql .= " AND DATE(cl.created_at) <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY cl.id DESC";
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Get employee performance
     */
    public function getEmployeePerformance($employeeId = 0, $startDate = '', $endDate = '') {
        $params = [];
        
        $sql = "SELECT 
                    u.id, 
                    u.name,
                    COUNT(DISTINCT l.id) as total_leads,
                    COUNT(DISTINCT cl.id) as total_calls,
                    SUM(CASE WHEN l.status = 'win' THEN 1 ELSE 0 END) as total_wins,
                    SUM(CASE WHEN l.status = 'interested' THEN 1 ELSE 0 END) as total_interested,
                    SUM(CASE WHEN l.status = 'follow_up' THEN 1 ELSE 0 END) as total_follow_ups,
                    SUM(CASE WHEN l.status = 'dead' THEN 1 ELSE 0 END) as total_dead,
                    SUM(CASE WHEN l.status = 'wrong_number' THEN 1 ELSE 0 END) as total_wrong_numbers,
                    SUM(CASE WHEN l.status = 'not_attend' THEN 1 ELSE 0 END) as total_not_attend,
                    SUM(CASE WHEN l.status = 'other' THEN 1 ELSE 0 END) as total_other
                FROM users u
                LEFT JOIN leads l ON u.id = l.assigned_to AND l.deleted_at IS NULL
                LEFT JOIN call_logs cl ON l.id = cl.lead_id AND cl.deleted_at IS NULL
                WHERE u.role = 'employee' AND u.deleted_at IS NULL";
        
        // Apply filters
        if ($employeeId > 0) {
            $sql .= " AND u.id = ?";
            $params[] = $employeeId;
        }
        
        if (!empty($startDate)) {
            $sql .= " AND (DATE(l.created_at) >= ? OR DATE(cl.created_at) >= ?)";
            $params[] = $startDate;
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $sql .= " AND (DATE(l.created_at) <= ? OR DATE(cl.created_at) <= ?)";
            $params[] = $endDate;
            $params[] = $endDate;
        }
        
        $sql .= " GROUP BY u.id, u.name ORDER BY total_wins DESC, total_interested DESC";
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Get lead statistics for dashboard
     * 
     * @param int $employeeId Employee ID to filter by (0 for all)
     * @param string $startDate Start date for filtering (YYYY-MM-DD)
     * @param string $endDate End date for filtering (YYYY-MM-DD)
     * @param string $status Status to filter by
     * @return array Lead statistics
     */
    public function getLeadStats($employeeId = 0, $startDate = '', $endDate = '', $status = '') {
        $userId = getCurrentUserId();
        $isAdmin = hasRole('administrator');
        
        // Base SQL for counting leads by status
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_leads,
                    SUM(CASE WHEN status = 'follow_up' THEN 1 ELSE 0 END) as follow_ups,
                    SUM(CASE WHEN status = 'not_attend' THEN 1 ELSE 0 END) as not_attend,
                    SUM(CASE WHEN status = 'wrong_number' THEN 1 ELSE 0 END) as wrong_number,
                    SUM(CASE WHEN status = 'other' THEN 1 ELSE 0 END) as other,
                    SUM(CASE WHEN status = 'dead' THEN 1 ELSE 0 END) as dead,
                    SUM(CASE WHEN status = 'interested' THEN 1 ELSE 0 END) as interested,
                    SUM(CASE WHEN status = 'win' THEN 1 ELSE 0 END) as wins
                FROM {$this->table}
                WHERE deleted_at IS NULL";
        
        $params = [];
        
        // Apply employee filter
        if ($employeeId > 0) {
            $sql .= " AND assigned_to = ?";
            $params[] = $employeeId;
        } else if (!$isAdmin) {
            // If not admin and no specific employee selected, only show assigned leads
            $sql .= " AND assigned_to = ?";
            $params[] = $userId;
        }
        
        // Apply date filters
        if (!empty($startDate)) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $endDate;
        }
        
        // Apply status filter
        if (!empty($status)) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        return $this->db->getRow($sql, $params);
    }
    
    /**
     * Get today's follow-ups
     * 
     * @param int $employeeId Employee ID to filter by (0 for all)
     * @return array Today's follow-ups
     */
    public function getTodayFollowUps($employeeId = 0) {
        $userId = getCurrentUserId();
        $isAdmin = hasRole('administrator');
        $today = date('Y-m-d');
        
        $sql = "SELECT l.*, s.name as state_name, c.name as city_name, u.name as assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.deleted_at IS NULL 
                AND l.status = 'follow_up' 
                AND DATE(l.follow_up_date) = ?";
        
        $params = [$today];
        
        // Apply employee filter
        if ($employeeId > 0) {
            $sql .= " AND l.assigned_to = ?";
            $params[] = $employeeId;
        } else if (!$isAdmin) {
            // If not admin and no specific employee selected, only show assigned leads
            $sql .= " AND l.assigned_to = ?";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY l.follow_up_date DESC";
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Get missed follow-ups
     * 
     * @param int $employeeId Employee ID to filter by (0 for all)
     * @return array Missed follow-ups
     */
    public function getMissedFollowUps($employeeId = 0) {
        $userId = getCurrentUserId();
        $isAdmin = hasRole('administrator');
        $today = date('Y-m-d');
        
        $sql = "SELECT l.*, s.name as state_name, c.name as city_name, u.name as assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.deleted_at IS NULL 
                AND l.status = 'follow_up' 
                AND DATE(l.follow_up_date) < ?
                AND l.follow_up_date IS NOT NULL";
        
        $params = [$today];
        
        // Apply employee filter
        if ($employeeId > 0) {
            $sql .= " AND l.assigned_to = ?";
            $params[] = $employeeId;
        } else if (!$isAdmin) {
            // If not admin and no specific employee selected, only show assigned leads
            $sql .= " AND l.assigned_to = ?";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY l.follow_up_date DESC";
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Get follow-ups with filters
     * 
     * @param int $page Page number
     * @param string $status Status filter
     * @param int $stateId State ID filter
     * @param int $cityId City ID filter
     * @param int $employeeId Employee ID filter
     * @param string $startDate Start date filter
     * @param string $endDate End date filter
     * @param int $limit Records per page
     * @return array Follow-ups
     */
    public function getFollowUps($page = 1, $status = '', $stateId = 0, $cityId = 0, $employeeId = 0, $startDate = '', $endDate = '', $limit = RECORDS_PER_PAGE) {
        $userId = getCurrentUserId();
        $isAdmin = hasRole('administrator');
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT l.*, s.name as state_name, c.name as city_name, u.name as assigned_to_name,
                (SELECT MAX(created_at) FROM call_logs WHERE lead_id = l.id AND deleted_at IS NULL) as last_follow_up
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.deleted_at IS NULL 
                AND l.follow_up_date IS NOT NULL";
        
        $params = [];
        
        // Apply status filter
        if (!empty($status)) {
            $sql .= " AND l.status = ?";
            $params[] = $status;
        }
        
        // Apply state filter
        if ($stateId > 0) {
            $sql .= " AND l.state_id = ?";
            $params[] = $stateId;
        }
        
        // Apply city filter
        if ($cityId > 0) {
            $sql .= " AND l.city_id = ?";
            $params[] = $cityId;
        }
        
        // Apply employee filter
        if ($employeeId > 0) {
            $sql .= " AND l.assigned_to = ?";
            $params[] = $employeeId;
        } else if (!$isAdmin) {
            // If not admin and no specific employee selected, only show assigned leads
            $sql .= " AND l.assigned_to = ?";
            $params[] = $userId;
        }
        
        // Apply date filters
        if (!empty($startDate)) {
            $sql .= " AND DATE(l.follow_up_date) >= ?";
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $sql .= " AND DATE(l.follow_up_date) <= ?";
            $params[] = $endDate;
        }
        
        // Order by follow-up date (most recent first)
        $sql .= " ORDER BY l.follow_up_date DESC";
        
        // Apply pagination
        $sql .= " LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $limit;
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Count follow-ups with filters
     * 
     * @param string $status Status filter
     * @param int $stateId State ID filter
     * @param int $cityId City ID filter
     * @param int $employeeId Employee ID filter
     * @param string $startDate Start date filter
     * @param string $endDate End date filter
     * @return int Total count
     */
    public function countFollowUps($status = '', $stateId = 0, $cityId = 0, $employeeId = 0, $startDate = '', $endDate = '') {
        $userId = getCurrentUserId();
        $isAdmin = hasRole('administrator');
        
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table} l
                WHERE l.deleted_at IS NULL 
                AND l.follow_up_date IS NOT NULL";
        
        $params = [];
        
        // Apply status filter
        if (!empty($status)) {
            $sql .= " AND l.status = ?";
            $params[] = $status;
        }
        
        // Apply state filter
        if ($stateId > 0) {
            $sql .= " AND l.state_id = ?";
            $params[] = $stateId;
        }
        
        // Apply city filter
        if ($cityId > 0) {
            $sql .= " AND l.city_id = ?";
            $params[] = $cityId;
        }
        
        // Apply employee filter
        if ($employeeId > 0) {
            $sql .= " AND l.assigned_to = ?";
            $params[] = $employeeId;
        } else if (!$isAdmin) {
            // If not admin and no specific employee selected, only show assigned leads
            $sql .= " AND l.assigned_to = ?";
            $params[] = $userId;
        }
        
        // Apply date filters
        if (!empty($startDate)) {
            $sql .= " AND DATE(l.follow_up_date) >= ?";
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $sql .= " AND DATE(l.follow_up_date) <= ?";
            $params[] = $endDate;
        }
        
        $result = $this->db->getRow($sql, $params);
        return $result['total'];
    }
    
    /**
     * Get daily call count
     * 
     * @param int $employeeId Employee ID to filter by (0 for all)
     * @param string $startDate Start date for filtering (YYYY-MM-DD)
     * @param string $endDate End date for filtering (YYYY-MM-DD)
     * @return array Daily call counts
     */
    public function getDailyCallCount($employeeId = 0, $startDate = '', $endDate = '') {
        $userId = getCurrentUserId();
        $isAdmin = hasRole('administrator');
        
        // Get call counts for the last 7 days by default
        $sql = "SELECT 
                    DATE(created_at) as call_date,
                    COUNT(*) as call_count
                FROM call_logs
                WHERE deleted_at IS NULL";
        
        $params = [];
        
        // Apply date filters
        if (!empty($startDate)) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $startDate;
        } else {
            $sql .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        }
        
        if (!empty($endDate)) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $endDate;
        }
        
        // Apply employee filter
        if ($employeeId > 0) {
            $sql .= " AND created_by = ?";
            $params[] = $employeeId;
        } else if (!$isAdmin) {
            // If not admin and no specific employee selected, only show calls made by the user
            $sql .= " AND created_by = ?";
            $params[] = $userId;
        }
        
        $sql .= " GROUP BY DATE(created_at) ORDER BY call_date ASC";
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Get recent call logs
     * 
     * @param int $employeeId Employee ID to filter by (0 for all)
     * @param string $startDate Start date for filtering (YYYY-MM-DD)
     * @param string $endDate End date for filtering (YYYY-MM-DD)
     * @return array Recent call logs
     */
    public function getRecentCallLogs($employeeId = 0, $startDate = '', $endDate = '') {
        $userId = getCurrentUserId();
        $isAdmin = hasRole('administrator');
        
        $sql = "SELECT cl.*, l.name as lead_name, u.name as created_by_name
                FROM call_logs cl
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
        
        // Apply date filters
        if (!empty($startDate)) {
            $sql .= " AND DATE(cl.created_at) >= ?";
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $sql .= " AND DATE(cl.created_at) <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY cl.created_at DESC LIMIT 10";
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Get employee performance stats for dashboard
     * 
     * @param int $employeeId Employee ID to filter by (0 for all)
     * @param string $startDate Start date for filtering (YYYY-MM-DD)
     * @param string $endDate End date for filtering (YYYY-MM-DD)
     * @return array Employee performance stats
     */
    public function getEmployeePerformanceStats($employeeId = 0, $startDate = '', $endDate = '') {
        $sql = "SELECT 
                    u.id, 
                    u.name,
                    COUNT(DISTINCT l.id) as total_leads,
                    COUNT(DISTINCT cl.id) as total_calls,
                    SUM(CASE WHEN l.status = 'win' THEN 1 ELSE 0 END) as wins,
                    SUM(CASE WHEN l.status = 'interested' THEN 1 ELSE 0 END) as total_interested,
                    ROUND(CASE WHEN COUNT(DISTINCT l.id) > 0 THEN 
                        (SUM(CASE WHEN l.status = 'win' THEN 1 ELSE 0 END) / COUNT(DISTINCT l.id)) * 100 
                    ELSE 0 END, 2) as conversion_rate
                FROM users u
                LEFT JOIN leads l ON u.id = l.assigned_to AND l.deleted_at IS NULL
                LEFT JOIN call_logs cl ON l.id = cl.lead_id AND cl.deleted_at IS NULL
                WHERE u.role = 'employee' AND u.deleted_at IS NULL";
        
        $params = [];
        
        // Apply employee filter
        if ($employeeId > 0) {
            $sql .= " AND u.id = ?";
            $params[] = $employeeId;
        }
        
        // Apply date filters
        if (!empty($startDate)) {
            $sql .= " AND (DATE(l.created_at) >= ? OR DATE(cl.created_at) >= ?)";
            $params[] = $startDate;
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $sql .= " AND (DATE(l.created_at) <= ? OR DATE(cl.created_at) <= ?)";
            $params[] = $endDate;
            $params[] = $endDate;
        }
        
        $sql .= " GROUP BY u.id, u.name
                  ORDER BY wins DESC, total_interested DESC
                  LIMIT 5";
        
        return $this->db->getRows($sql, $params);
    }
    
    /**
     * Get leads for employee (including territory-based leads)
     */
    public function getLeadsForEmployee($employeeId) {
        // First, log the territories assigned to this employee for debugging
        $territorySql = "SELECT et.id, et.state_id, s.name as state_name, et.city_id, c.name as city_name
                        FROM employee_territories et
                        LEFT JOIN states s ON et.state_id = s.id
                        LEFT JOIN cities c ON et.city_id = c.id
                        WHERE et.user_id = ? AND et.deleted_at IS NULL";
        
        $territories = $this->db->getRows($territorySql, [$employeeId]);
        
        // Log territories to a file for debugging
        $logFile = 'logs/territory_debug.log';
        $logData = date('Y-m-d H:i:s') . " - Employee ID: $employeeId - Territories: " . print_r($territories, true) . "\n";
        file_put_contents($logFile, $logData, FILE_APPEND);
        
        // Get leads for this employee (assigned directly or via territory)
        $sql = "SELECT l.*, s.name as state_name, c.name as city_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                WHERE l.deleted_at IS NULL AND (
                    l.assigned_to = ? 
                    OR EXISTS (
                        SELECT 1 FROM employee_territories et 
                        WHERE et.user_id = ? 
                        AND et.deleted_at IS NULL 
                        AND (
                            (et.state_id = l.state_id AND et.city_id IS NULL) 
                            OR (et.state_id = l.state_id AND et.city_id = l.city_id)
                        )
                    )
                )
                ORDER BY l.id DESC";
        
        $leads = $this->db->getRows($sql, [$employeeId, $employeeId]);
        
        // Log leads to the file for debugging
        $logData = date('Y-m-d H:i:s') . " - Employee ID: $employeeId - Leads found: " . count($leads) . "\n";
        $logData .= print_r($leads, true) . "\n";
        file_put_contents($logFile, $logData, FILE_APPEND);
        
        return $leads;
    }
    
    /**
     * Get leads by state
     */
    public function getLeadsByState($stateId) {
        $sql = "SELECT l.*, c.name as city_name, u.name as assigned_to_name
                FROM {$this->table} l
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.deleted_at IS NULL AND l.state_id = ?
                ORDER BY l.id DESC";
        
        return $this->db->getRows($sql, [$stateId]);
    }
    
    /**
     * Get leads by city
     */
    public function getLeadsByCity($cityId) {
        $sql = "SELECT l.*, s.name as state_name, u.name as assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.deleted_at IS NULL AND l.city_id = ?
                ORDER BY l.id DESC";
        
        return $this->db->getRows($sql, [$cityId]);
    }
    
    /**
     * Create a test lead for a specific state/city if none exists
     * This is used for debugging territory-based lead visibility
     */
    public function createTestLeadIfNoneExists($stateId, $cityId, $createdBy) {
        // Check if any leads exist for this state/city
        $checkSql = "SELECT COUNT(*) FROM {$this->table} 
                    WHERE state_id = ? AND city_id = ? AND deleted_at IS NULL";
        $count = $this->db->getValue($checkSql, [$stateId, $cityId]);
        
        // If no leads exist, create a test lead
        if ($count == 0) {
            $testData = [
                'name' => 'Test Lead for Territory',
                'email' => 'test.territory@example.com',
                'phone' => '9876543210',
                'address' => 'Test Address',
                'state_id' => $stateId,
                'city_id' => $cityId,
                'status' => 'new',
                'remarks' => 'This is a test lead created for territory testing',
                'created_by' => $createdBy,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->createLead($testData);
        }
        
        return false;
    }
    
    /**
     * Get leads by status
     */
    public function getLeadsByStatus($status) {
        $sql = "SELECT l.*, s.name as state_name, c.name as city_name, u.name as assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.deleted_at IS NULL AND l.status = ?
                ORDER BY l.id DESC";
        
        return $this->db->getRows($sql, [$status]);
    }
    
    /**
     * Update lead status
     * 
     * @param int $id Lead ID
     * @param string $status New status
     * @param string $remarks Remarks
     * @param string|null $followUpDate Follow-up date
     * @param string|null $otherReason Other reason
     * @return bool Success or failure
     */
    public function updateStatus($id, $status, $remarks = '', $followUpDate = null, $otherReason = '') {
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Update lead status
            $data = [
                'status' => $status,
                'remarks' => $remarks,
                'updated_by' => getCurrentUserId()
            ];
            
            // Handle follow-up date for all statuses that need it
            if ($status == 'follow_up' || $status == 'interested' || $status == 'not_attend' || $status == 'wrong_number') {
                if ($status == 'follow_up' && empty($followUpDate)) {
                    throw new Exception("Follow-up date is required for follow-up status");
                }
                $data['follow_up_date'] = $followUpDate;
            } else {
                $data['follow_up_date'] = null;
            }
            
            // Handle other reason
            if ($status == 'other') {
                if (empty($otherReason)) {
                    throw new Exception("Reason is required for other status");
                }
                $data['other_reason'] = $otherReason;
            } else {
                $data['other_reason'] = null;
            }
            
            // Update lead
            $this->update($id, $data);
            
            // NOTE: We're not creating a call log here anymore to avoid duplication
            // The call log is now created only in the LeadController
            
            // Commit transaction
            $this->db->commit();
            
            return true;
        } catch (Exception $e) {
            // Rollback transaction
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Import leads from CSV
     */
    public function importFromCSV($file, $assignedTo) {
        // Check if file exists
        if (!file_exists($file)) {
            throw new Exception("File not found");
        }
        
        // Open file
        $handle = fopen($file, 'r');
        if (!$handle) {
            throw new Exception("Could not open file");
        }
        
        // Read header row
        $header = fgetcsv($handle);
        $requiredColumns = ['name', 'phone', 'state', 'city'];
        $missingColumns = array_diff($requiredColumns, $header);
        
        if (!empty($missingColumns)) {
            fclose($handle);
            throw new Exception("Missing required columns: " . implode(', ', $missingColumns));
        }
        
        // Map column indexes
        $columnMap = array_flip($header);
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            $imported = 0;
            $errors = [];
            $row = 2; // Start from row 2 (after header)
            
            // Process rows
            while (($data = fgetcsv($handle)) !== false) {
                try {
                    // Extract data
                    $name = $data[$columnMap['name']] ?? '';
                    $phone = $data[$columnMap['phone']] ?? '';
                    $email = $data[$columnMap['email']] ?? '';
                    $address = $data[$columnMap['address']] ?? '';
                    $stateName = $data[$columnMap['state']] ?? '';
                    $cityName = $data[$columnMap['city']] ?? '';
                    
                    // Validate required fields
                    if (empty($name) || empty($phone) || empty($stateName) || empty($cityName)) {
                        throw new Exception("Name, phone, state, and city are required");
                    }
                    
                    // Get state ID
                    $stateId = $this->db->getValue("SELECT id FROM states WHERE name = ? AND deleted_at IS NULL", [$stateName]);
                    if (!$stateId) {
                        throw new Exception("State not found: $stateName");
                    }
                    
                    // Get city ID
                    $cityId = $this->db->getValue("SELECT id FROM cities WHERE name = ? AND state_id = ? AND deleted_at IS NULL", [$cityName, $stateId]);
                    if (!$cityId) {
                        throw new Exception("City not found: $cityName in state $stateName");
                    }
                    
                    // Create lead
                    $leadData = [
                        'name' => $name,
                        'phone' => $phone,
                        'email' => $email,
                        'address' => $address,
                        'state_id' => $stateId,
                        'city_id' => $cityId,
                        'status' => 'new',
                        'assigned_to' => $assignedTo,
                        'created_by' => getCurrentUserId()
                    ];
                    
                    $this->create($leadData);
                    $imported++;
                } catch (Exception $e) {
                    $errors[] = "Row $row: " . $e->getMessage();
                }
                
                $row++;
            }
            
            // Commit transaction
            $this->db->commit();
            
            fclose($handle);
            
            return [
                'imported' => $imported,
                'errors' => $errors
            ];
        } catch (Exception $e) {
            // Rollback transaction
            $this->db->rollback();
            
            fclose($handle);
            throw $e;
        }
    }
    
    /**
     * Create a new lead
     * 
     * @param array $data Lead data
     * @return int|bool ID of created lead or false on failure
     */
    public function createLead($data) {
        return $this->create($data);
    }
    
    /**
     * Update a lead
     * 
     * @param int $id Lead ID
     * @param array $data Lead data
     * @return bool Success or failure
     */
    public function updateLead($id, $data) {
        return $this->update($id, $data);
    }
}
