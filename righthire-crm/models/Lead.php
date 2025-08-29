<?php
/**
 * Lead Model
 * 
 * This class handles lead-related database operations.
 */

require_once 'Model.php';

class Lead extends Model {
    protected $table = 'leads';
    protected $fillable = [
        'name', 'email', 'phone', 'address', 'state_id', 'city_id', 
        'status', 'other_reason', 'assigned_to', 'follow_up_date',
        'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
    
    /**
     * Get lead with related data
     * 
     * @param int $id
     * @return array|bool
     */
    public function getWithRelations($id) {
        $sql = "SELECT l.*, 
                s.name as state_name, 
                c.name as city_name, 
                u.name as assigned_to_name,
                cu.name as created_by_name,
                uu.name as updated_by_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                LEFT JOIN users cu ON l.created_by = cu.id
                LEFT JOIN users uu ON l.updated_by = uu.id
                WHERE l.id = :id AND l.deleted_at IS NULL";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get all leads with related data
     * 
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getAllWithRelations($page = 1, $perPage = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT l.*, 
                s.name as state_name, 
                c.name as city_name, 
                u.name as assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.deleted_at IS NULL
                ORDER BY l.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Filter leads by criteria
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function filter($filters, $page = 1, $perPage = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        $conditions = [];
        $params = [];
        
        // Build conditions
        if (!empty($filters['state_id'])) {
            $conditions[] = "l.state_id = :state_id";
            $params['state_id'] = $filters['state_id'];
        }
        
        if (!empty($filters['city_id'])) {
            $conditions[] = "l.city_id = :city_id";
            $params['city_id'] = $filters['city_id'];
        }
        
        if (!empty($filters['status'])) {
            $conditions[] = "l.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['assigned_to'])) {
            $conditions[] = "l.assigned_to = :assigned_to";
            $params['assigned_to'] = $filters['assigned_to'];
        }
        
        if (!empty($filters['date_from'])) {
            $conditions[] = "l.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $conditions[] = "l.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (!empty($filters['search'])) {
            $conditions[] = "(l.name LIKE :search OR l.email LIKE :search OR l.phone LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        // Build WHERE clause
        $whereClause = "l.deleted_at IS NULL";
        if (!empty($conditions)) {
            $whereClause .= " AND " . implode(" AND ", $conditions);
        }
        
        // Build SQL
        $sql = "SELECT l.*, 
                s.name as state_name, 
                c.name as city_name, 
                u.name as assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE {$whereClause}
                ORDER BY l.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Count filtered leads
     * 
     * @param array $filters
     * @return int
     */
    public function countFiltered($filters) {
        $conditions = [];
        $params = [];
        
        // Build conditions
        if (!empty($filters['state_id'])) {
            $conditions[] = "state_id = :state_id";
            $params['state_id'] = $filters['state_id'];
        }
        
        if (!empty($filters['city_id'])) {
            $conditions[] = "city_id = :city_id";
            $params['city_id'] = $filters['city_id'];
        }
        
        if (!empty($filters['status'])) {
            $conditions[] = "status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['assigned_to'])) {
            $conditions[] = "assigned_to = :assigned_to";
            $params['assigned_to'] = $filters['assigned_to'];
        }
        
        if (!empty($filters['date_from'])) {
            $conditions[] = "created_at >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $conditions[] = "created_at <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (!empty($filters['search'])) {
            $conditions[] = "(name LIKE :search OR email LIKE :search OR phone LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        // Build WHERE clause
        $whereClause = "deleted_at IS NULL";
        if (!empty($conditions)) {
            $whereClause .= " AND " . implode(" AND ", $conditions);
        }
        
        // Build SQL
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
        
        $result = $this->db->fetch($sql, $params);
        
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * Get leads by status
     * 
     * @param string $status
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getByStatus($status, $page = 1, $perPage = RECORDS_PER_PAGE) {
        return $this->filter(['status' => $status], $page, $perPage);
    }
    
    /**
     * Count leads by status
     * 
     * @param string $status
     * @return int
     */
    public function countByStatus($status) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE status = :status AND deleted_at IS NULL";
        
        $result = $this->db->fetch($sql, ['status' => $status]);
        
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * Get leads by employee
     * 
     * @param int $employeeId
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getByEmployee($employeeId, $page = 1, $perPage = RECORDS_PER_PAGE) {
        return $this->filter(['assigned_to' => $employeeId], $page, $perPage);
    }
    
    /**
     * Count leads by employee
     * 
     * @param int $employeeId
     * @return int
     */
    public function countByEmployee($employeeId) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE assigned_to = :employee_id AND deleted_at IS NULL";
        
        $result = $this->db->fetch($sql, ['employee_id' => $employeeId]);
        
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * Count leads by employee and status
     * 
     * @param int $employeeId
     * @param string $status
     * @return int
     */
    public function countByEmployeeAndStatus($employeeId, $status) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE assigned_to = :employee_id AND status = :status AND deleted_at IS NULL";
        
        $result = $this->db->fetch($sql, [
            'employee_id' => $employeeId,
            'status' => $status
        ]);
        
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * Get today's follow-ups
     * 
     * @return array
     */
    public function getTodayFollowUps() {
        $today = date('Y-m-d');
        
        $sql = "SELECT l.*, 
                s.name as state_name, 
                c.name as city_name, 
                u.name as assigned_to_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.status = 'follow_up' 
                AND DATE(l.follow_up_date) = :today
                AND l.deleted_at IS NULL
                ORDER BY l.follow_up_date ASC";
        
        return $this->db->fetchAll($sql, ['today' => $today]);
    }
    
    /**
     * Get today's follow-ups by employee
     * 
     * @param int $employeeId
     * @return array
     */
    public function getTodayFollowUpsByEmployee($employeeId) {
        $today = date('Y-m-d');
        
        $sql = "SELECT l.*, 
                s.name as state_name, 
                c.name as city_name
                FROM {$this->table} l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN cities c ON l.city_id = c.id
                WHERE l.status = 'follow_up' 
                AND DATE(l.follow_up_date) = :today
                AND l.assigned_to = :employee_id
                AND l.deleted_at IS NULL
                ORDER BY l.follow_up_date ASC";
        
        return $this->db->fetchAll($sql, [
            'today' => $today,
            'employee_id' => $employeeId
        ]);
    }
    
    /**
     * Get employee conversion rates
     * 
     * @return array
     */
    public function getEmployeeConversionRates() {
        $sql = "SELECT 
                u.id, 
                u.name,
                COUNT(l.id) as total_leads,
                SUM(CASE WHEN l.status = 'win' THEN 1 ELSE 0 END) as wins,
                ROUND((SUM(CASE WHEN l.status = 'win' THEN 1 ELSE 0 END) / COUNT(l.id)) * 100, 2) as conversion_rate
                FROM users u
                LEFT JOIN {$this->table} l ON u.id = l.assigned_to AND l.deleted_at IS NULL
                WHERE u.role = 'employee' AND u.deleted_at IS NULL
                GROUP BY u.id, u.name
                ORDER BY conversion_rate DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get employee conversion trend
     * 
     * @param int $employeeId
     * @param int $months
     * @return array
     */
    public function getEmployeeConversionTrend($employeeId, $months = 6) {
        $trend = [];
        $currentDate = new DateTime();
        
        for ($i = 0; $i < $months; $i++) {
            $date = clone $currentDate;
            $date->modify("-{$i} month");
            $month = $date->format('Y-m');
            $monthStart = $date->format('Y-m-01');
            $monthEnd = $date->format('Y-m-t');
            
            $sql = "SELECT 
                    COUNT(id) as total_leads,
                    SUM(CASE WHEN status = 'win' THEN 1 ELSE 0 END) as wins
                    FROM {$this->table}
                    WHERE assigned_to = :employee_id
                    AND created_at BETWEEN :month_start AND :month_end
                    AND deleted_at IS NULL";
            
            $result = $this->db->fetch($sql, [
                'employee_id' => $employeeId,
                'month_start' => $monthStart . ' 00:00:00',
                'month_end' => $monthEnd . ' 23:59:59'
            ]);
            
            $totalLeads = $result ? (int)$result['total_leads'] : 0;
            $wins = $result ? (int)$result['wins'] : 0;
            $conversionRate = $totalLeads > 0 ? round(($wins / $totalLeads) * 100, 2) : 0;
            
            $trend[] = [
                'month' => $date->format('M Y'),
                'total_leads' => $totalLeads,
                'wins' => $wins,
                'conversion_rate' => $conversionRate
            ];
        }
        
        // Reverse to get chronological order
        return array_reverse($trend);
    }
    
    /**
     * Bulk import leads
     * 
     * @param array $leads
     * @return array
     */
    public function bulkImport($leads) {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($leads as $index => $lead) {
            $result = $this->create($lead);
            
            if ($result) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Row " . ($index + 1) . ": Failed to import lead";
            }
        }
        
        return $results;
    }
    
    /**
     * Validate lead data
     * 
     * @param array $data
     * @return array
     */
    public function validate($data) {
        $errors = [];
        
        // Required fields
        if (empty($data['name'])) {
            $errors[] = "Name is required";
        }
        
        if (empty($data['phone'])) {
            $errors[] = "Phone is required";
        } elseif (!validatePhone($data['phone'])) {
            $errors[] = "Phone number is invalid";
        }
        
        if (!empty($data['email']) && !validateEmail($data['email'])) {
            $errors[] = "Email is invalid";
        }
        
        if (empty($data['state_id'])) {
            $errors[] = "State is required";
        }
        
        if (empty($data['city_id'])) {
            $errors[] = "City is required";
        }
        
        // Status-specific validations
        if (isset($data['status']) && $data['status'] == 'follow_up' && empty($data['follow_up_date'])) {
            $errors[] = "Follow-up date is required for follow-up status";
        }
        
        if (isset($data['status']) && $data['status'] == 'other' && empty($data['other_reason'])) {
            $errors[] = "Reason is required for Other status";
        }
        
        return $errors;
    }
}

