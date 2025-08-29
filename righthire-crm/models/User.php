<?php
/**
 * User Model
 * 
 * This class handles user-related database operations.
 */

require_once 'Model.php';

class User extends Model {
    protected $table = 'users';
    protected $fillable = [
        'name', 'email', 'password', 'role', 'status', 
        'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
    
    /**
     * Find user by email
     * 
     * @param string $email
     * @return array|bool
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND deleted_at IS NULL";
        
        return $this->db->fetch($sql, ['email' => $email]);
    }
    
    /**
     * Create a new user
     * 
     * @param array $data
     * @return int|bool
     */
    public function create($data) {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = hashPassword($data['password']);
        }
        
        return parent::create($data);
    }
    
    /**
     * Update a user
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = hashPassword($data['password']);
        } else {
            // Don't update password if empty
            unset($data['password']);
        }
        
        return parent::update($id, $data);
    }
    
    /**
     * Authenticate user
     * 
     * @param string $email
     * @param string $password
     * @return array|bool
     */
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        if (!verifyPassword($password, $user['password'])) {
            return false;
        }
        
        if ($user['status'] != 1) {
            return false;
        }
        
        return $user;
    }
    
    /**
     * Get all employees (role = 'employee')
     * 
     * @return array
     */
    public function getAllEmployees() {
        $sql = "SELECT * FROM {$this->table} WHERE role = 'employee' AND deleted_at IS NULL";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get employee territories
     * 
     * @param int $employeeId
     * @return array
     */
    public function getEmployeeTerritories($employeeId) {
        $sql = "SELECT et.*, c.name as city_name, s.name as state_name 
                FROM employee_territories et
                JOIN cities c ON et.city_id = c.id
                JOIN states s ON c.state_id = s.id
                WHERE et.employee_id = :employee_id AND et.deleted_at IS NULL";
        
        return $this->db->fetchAll($sql, ['employee_id' => $employeeId]);
    }
    
    /**
     * Assign territory to employee
     * 
     * @param int $employeeId
     * @param int $cityId
     * @return bool
     */
    public function assignTerritory($employeeId, $cityId) {
        // Check if assignment already exists
        $sql = "SELECT id FROM employee_territories 
                WHERE employee_id = :employee_id AND city_id = :city_id AND deleted_at IS NULL";
        
        $existing = $this->db->fetch($sql, [
            'employee_id' => $employeeId,
            'city_id' => $cityId
        ]);
        
        if ($existing) {
            return true; // Already assigned
        }
        
        $data = [
            'employee_id' => $employeeId,
            'city_id' => $cityId,
            'created_by' => $_SESSION['user_id'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ":{$field}";
        }, $fields);
        
        $sql = "INSERT INTO employee_territories (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        try {
            $this->db->query($sql);
            $this->db->bind($data);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log('Assign Territory Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove territory from employee
     * 
     * @param int $employeeId
     * @param int $cityId
     * @return bool
     */
    public function removeTerritory($employeeId, $cityId) {
        $sql = "UPDATE employee_territories 
                SET deleted_at = :deleted_at, updated_by = :updated_by 
                WHERE employee_id = :employee_id AND city_id = :city_id AND deleted_at IS NULL";
        
        $data = [
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_by' => $_SESSION['user_id'],
            'employee_id' => $employeeId,
            'city_id' => $cityId
        ];
        
        try {
            $this->db->query($sql);
            $this->db->bind($data);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log('Remove Territory Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user has access to a specific city
     * 
     * @param int $userId
     * @param int $cityId
     * @return bool
     */
    public function hasAccessToCity($userId, $cityId) {
        // Administrators have access to all cities
        $user = $this->find($userId);
        if ($user && $user['role'] == 'administrator') {
            return true;
        }
        
        // Check if employee has been assigned to this city
        $sql = "SELECT id FROM employee_territories 
                WHERE employee_id = :employee_id AND city_id = :city_id AND deleted_at IS NULL";
        
        $result = $this->db->fetch($sql, [
            'employee_id' => $userId,
            'city_id' => $cityId
        ]);
        
        return $result ? true : false;
    }
    
    /**
     * Get cities accessible to a user
     * 
     * @param int $userId
     * @return array
     */
    public function getAccessibleCities($userId) {
        // Administrators have access to all cities
        $user = $this->find($userId);
        if ($user && $user['role'] == 'administrator') {
            $sql = "SELECT c.*, s.name as state_name 
                    FROM cities c
                    JOIN states s ON c.state_id = s.id
                    WHERE c.deleted_at IS NULL";
            
            return $this->db->fetchAll($sql);
        }
        
        // Get cities assigned to employee
        $sql = "SELECT c.*, s.name as state_name 
                FROM employee_territories et
                JOIN cities c ON et.city_id = c.id
                JOIN states s ON c.state_id = s.id
                WHERE et.employee_id = :employee_id 
                AND et.deleted_at IS NULL 
                AND c.deleted_at IS NULL";
        
        return $this->db->fetchAll($sql, ['employee_id' => $userId]);
    }
}

