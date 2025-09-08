<?php
/**
 * User Model
 * 
 * This model handles all user-related database operations.
 */

require_once 'Model.php';

class User extends Model {
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password', 'phone', 'address', 'role', 'status', 'created_by', 'updated_by'];
    
    /**
     * Create a new user
     */
    public function createUser($data) {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
        }
        
        return $this->create($data);
    }
    
    /**
     * Update a user
     */
    public function updateUser($id, $data) {
        // Hash password
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
        } elseif (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = ? AND deleted_at IS NULL";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        return $this->db->getValue($sql, $params) > 0;
    }
    
    /**
     * Get user by email
     */
    public function getByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? AND deleted_at IS NULL";
        return $this->db->getRow($sql, [$email]);
    }
    
    /**
     * Get user by ID
     */
    public function getById($id) {
        return $this->find($id);
    }
    
    /**
     * Get all users with lead count
     */
    public function getAllWithLeadCount($page = 1, $limit = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT u.*, COUNT(l.id) AS lead_count
                FROM {$this->table} u
                LEFT JOIN leads l ON u.id = l.assigned_to AND l.deleted_at IS NULL
                WHERE u.deleted_at IS NULL
                GROUP BY u.id
                ORDER BY u.id DESC
                LIMIT ?, ?";
        
        return $this->db->getRows($sql, [$offset, $limit]);
    }
    
    /**
     * Get all users with role name
     */
    public function getAllWithRoleName($page = 1, $limit = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT u.*, 
                CASE 
                    WHEN u.role = 'administrator' THEN 'Administrator' 
                    WHEN u.role = 'employee' THEN 'Employee' 
                    ELSE u.role 
                END AS role_name,
                (SELECT COUNT(*) FROM leads l WHERE l.assigned_to = u.id AND l.deleted_at IS NULL) AS lead_count
                FROM {$this->table} u
                WHERE u.deleted_at IS NULL
                ORDER BY u.id DESC
                LIMIT ?, ?";
        
        return $this->db->getRows($sql, [$offset, $limit]);
    }
    
    /**
     * Get all active employees
     */
    public function getAllActiveEmployees() {
        $sql = "SELECT * FROM {$this->table} WHERE role = 'employee' AND status = 1 AND deleted_at IS NULL ORDER BY name ASC";
        return $this->db->getRows($sql);
    }
    
    /**
     * Get all employees (active and inactive)
     */
    public function getEmployees() {
        $sql = "SELECT * FROM {$this->table} WHERE role = 'employee' AND deleted_at IS NULL ORDER BY name ASC";
        return $this->db->getRows($sql);
    }
    
    /**
     * Get available roles
     */
    public function getRoles() {
        return [
            ['id' => 'administrator', 'name' => 'Administrator'],
            ['id' => 'employee', 'name' => 'Employee']
        ];
    }
    
    /**
     * Get employee territories
     */
    public function getEmployeeTerritories($userId) {
        $sql = "SELECT et.*, s.name AS state_name, c.name AS city_name
                FROM employee_territories et
                JOIN states s ON et.state_id = s.id
                LEFT JOIN cities c ON et.city_id = c.id
                WHERE et.user_id = ? AND et.deleted_at IS NULL
                ORDER BY s.name ASC, c.name ASC";
        
        return $this->db->getRows($sql, [$userId]);
    }
    
    /**
     * Get territories for a user
     * Alias for getEmployeeTerritories for backward compatibility
     * 
     * @param int $userId User ID
     * @return array Territories assigned to the user
     */
    public function getTerritories($userId) {
        return $this->getEmployeeTerritories($userId);
    }
    
    /**
     * Add territory to a user
     * Alias for addEmployeeTerritory for backward compatibility
     * 
     * @param int $userId User ID
     * @param int $stateId State ID
     * @param int|null $cityId City ID (optional)
     * @return int|bool ID of created territory or false on failure
     */
    public function addTerritory($userId, $stateId, $cityId = null) {
        return $this->addEmployeeTerritory($userId, $stateId, $cityId);
    }
    
    /**
     * Add employee territory
     * 
     * @param int $userId User ID
     * @param int $stateId State ID
     * @param int|null $cityId City ID (optional)
     * @return int|bool ID of created territory or false on failure
     */
    public function addEmployeeTerritory($userId, $stateId, $cityId = null) {
        // Check if a soft-deleted territory exists with the same combination
        $checkSql = "SELECT id FROM employee_territories 
                    WHERE user_id = ? AND state_id = ? AND (city_id = ? OR (city_id IS NULL AND ? IS NULL))
                    AND deleted_at IS NOT NULL";
        $checkParams = [$userId, $stateId, $cityId, $cityId];
        $existingId = $this->db->getValue($checkSql, $checkParams);
        
        if ($existingId) {
            // Hard delete the existing soft-deleted record first
            $this->hardDeleteTerritory($existingId);
        }
        
        // Ensure city_id is NULL if not provided to avoid foreign key constraint issues
        $data = [
            'user_id' => $userId,
            'state_id' => $stateId,
            'city_id' => $cityId === 0 ? null : $cityId,
            'created_by' => getCurrentUserId()
        ];
        
        return $this->db->insert('employee_territories', $data);
    }
    
    /**
     * Remove employee territory (soft delete)
     */
    public function removeEmployeeTerritory($id) {
        $sql = "UPDATE employee_territories SET deleted_at = NOW(), updated_by = ? WHERE id = ?";
        $this->db->query($sql, [getCurrentUserId(), $id]);
        return true;
    }
    
    /**
     * Remove territory
     * Alias for removeEmployeeTerritory for backward compatibility
     * 
     * @param int $id Territory ID
     * @return bool Success or failure
     */
    public function removeTerritory($id) {
        return $this->removeEmployeeTerritory($id);
    }
    
    /**
     * Hard delete a territory
     * 
     * This method completely removes a territory from the database
     * instead of just marking it as deleted.
     * 
     * @param int $id Territory ID
     * @return bool Success or failure
     */
    public function hardDeleteTerritory($id) {
        $sql = "DELETE FROM employee_territories WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }
    
    /**
     * Check if user has access to a state
     */
    public function hasAccessToState($userId, $stateId) {
        if (hasRole('administrator')) {
            return true;
        }
        
        $sql = "SELECT COUNT(*) FROM employee_territories
                WHERE user_id = ? AND state_id = ? AND deleted_at IS NULL";
        
        return $this->db->getValue($sql, [$userId, $stateId]) > 0;
    }
    
    /**
     * Check if user has access to a city
     */
    public function hasAccessToCity($userId, $cityId) {
        if (hasRole('administrator')) {
            return true;
        }
        
        $sql = "SELECT c.state_id FROM cities c WHERE c.id = ?";
        $stateId = $this->db->getValue($sql, [$cityId]);
        
        if (!$stateId) {
            return false;
        }
        
        $sql = "SELECT COUNT(*) FROM employee_territories
                WHERE user_id = ? AND state_id = ? AND (city_id IS NULL OR city_id = ?) AND deleted_at IS NULL";
        
        return $this->db->getValue($sql, [$userId, $stateId, $cityId]) > 0;
    }
    
    /**
     * Get employee performance stats
     */
    public function getEmployeePerformanceStats() {
        $sql = "SELECT u.id, u.name,
                COUNT(l.id) AS total_leads,
                SUM(CASE WHEN l.status = 'win' THEN 1 ELSE 0 END) AS wins,
                ROUND(SUM(CASE WHEN l.status = 'win' THEN 1 ELSE 0 END) / COUNT(l.id) * 100, 2) AS conversion_rate
                FROM users u
                LEFT JOIN leads l ON u.id = l.assigned_to AND l.deleted_at IS NULL
                WHERE u.role = 'employee' AND u.status = 1 AND u.deleted_at IS NULL
                GROUP BY u.id
                ORDER BY conversion_rate DESC, total_leads DESC";
        
        return $this->db->getRows($sql);
    }
    
    /**
     * Get employee performance trend
     */
    public function getEmployeePerformanceTrend($userId, $months = 6) {
        $sql = "SELECT u.id, u.name,
                DATE_FORMAT(l.created_at, '%Y-%m') AS month,
                DATE_FORMAT(l.created_at, '%b %Y') AS month_name,
                COUNT(l.id) AS total_leads,
                SUM(CASE WHEN l.status = 'win' THEN 1 ELSE 0 END) AS wins,
                ROUND(SUM(CASE WHEN l.status = 'win' THEN 1 ELSE 0 END) / COUNT(l.id) * 100, 2) AS conversion_rate
                FROM users u
                LEFT JOIN leads l ON u.id = l.assigned_to AND l.deleted_at IS NULL
                WHERE u.id = ? AND u.deleted_at IS NULL
                AND l.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL ? MONTH)
                GROUP BY u.id, month
                ORDER BY month ASC";
        
        return $this->db->getRows($sql, [$userId, $months]);
    }
}
