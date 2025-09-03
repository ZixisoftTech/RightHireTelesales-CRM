<?php
/**
 * State Model
 * 
 * This model handles all state-related database operations.
 */

require_once 'Model.php';

class State extends Model {
    protected $table = 'states';
    protected $fillable = ['name', 'status', 'created_by', 'updated_by'];
    
    /**
     * Get all states with city count
     */
    public function getAllWithCityCount($page = 1, $limit = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT s.*, COUNT(c.id) AS city_count
                FROM {$this->table} s
                LEFT JOIN cities c ON s.id = c.state_id AND c.deleted_at IS NULL
                WHERE s.deleted_at IS NULL
                GROUP BY s.id
                ORDER BY s.id DESC
                LIMIT ?, ?";
        
        return $this->db->getRows($sql, [$offset, $limit]);
    }
    
    /**
     * Get all active states
     * 
     * @return array Active states
     */
    public function getActiveStates() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 1 AND deleted_at IS NULL ORDER BY name ASC";
        return $this->db->getRows($sql);
    }
    
    /**
     * Get state by ID
     * 
     * @param int $id State ID
     * @return array|null State data or null if not found
     */
    public function getById($id) {
        return $this->find($id);
    }
    
    /**
     * Check if state name exists
     */
    public function nameExists($name, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE name = ? AND deleted_at IS NULL";
        $params = [$name];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        return $this->db->getValue($sql, $params) > 0;
    }
    
    /**
     * Get states for employee
     */
    public function getStatesForEmployee($userId) {
        $sql = "SELECT DISTINCT s.*
                FROM states s
                JOIN employee_territories et ON s.id = et.state_id
                WHERE et.user_id = ? AND s.status = 1 AND s.deleted_at IS NULL AND et.deleted_at IS NULL
                ORDER BY s.name ASC";
        
        return $this->db->getRows($sql, [$userId]);
    }
    
    /**
     * Check for soft-deleted duplicate state
     * 
     * @param array $data State data
     * @return int|null ID of soft-deleted duplicate or null if none exists
     */
    public function checkSoftDeletedDuplicate($data) {
        if (!isset($data['name'])) {
            return null;
        }
        
        $sql = "SELECT id FROM {$this->table} WHERE name = ? AND deleted_at IS NOT NULL";
        return $this->db->getValue($sql, [$data['name']]);
    }
    
    /**
     * Get count of leads by state ID
     * 
     * @param int $id State ID
     * @return int Number of leads
     */
    public function getLeadCount($id) {
        $sql = "SELECT COUNT(*) FROM leads WHERE state_id = ? AND deleted_at IS NULL";
        return $this->db->getValue($sql, [$id]);
    }
    
    /**
     * Get count of cities by state ID
     * 
     * @param int $id State ID
     * @return int Number of cities
     */
    public function getCityCount($id) {
        $sql = "SELECT COUNT(*) FROM cities WHERE state_id = ? AND deleted_at IS NULL";
        return $this->db->getValue($sql, [$id]);
    }
    
    /**
     * Get leads by state ID with limit
     * 
     * @param int $id State ID
     * @param int $limit Maximum number of leads to return
     * @return array Leads data
     */
    public function getLeadsByStateId($id, $limit = 10) {
        $sql = "SELECT l.id, l.name, l.phone, l.email, c.name as city_name
                FROM leads l
                LEFT JOIN cities c ON l.city_id = c.id
                WHERE l.state_id = ? AND l.deleted_at IS NULL
                ORDER BY l.id DESC
                LIMIT ?";
        
        return $this->db->getRows($sql, [$id, $limit]);
    }
    
    /**
     * Hard delete a state
     * 
     * This method completely removes a state from the database
     * instead of just marking it as deleted.
     * 
     * @param int $id State ID
     * @return bool Success or failure
     */
    public function hardDelete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }
}
