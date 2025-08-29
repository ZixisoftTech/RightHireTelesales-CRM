<?php
/**
 * City Model
 * 
 * This model handles all city-related database operations.
 */

require_once 'Model.php';

class City extends Model {
    protected $table = 'cities';
    protected $fillable = ['state_id', 'name', 'status', 'created_by', 'updated_by'];
    
    /**
     * Get all cities with state name
     */
    public function getAllWithStateName($page = 1, $limit = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT c.*, s.name AS state_name
                FROM {$this->table} c
                JOIN states s ON c.state_id = s.id
                WHERE c.deleted_at IS NULL
                ORDER BY c.id DESC
                LIMIT ?, ?";
        
        return $this->db->getRows($sql, [$offset, $limit]);
    }
    
    /**
     * Get cities by state
     */
    public function getByState($stateId) {
        $sql = "SELECT * FROM {$this->table} WHERE state_id = ? AND deleted_at IS NULL ORDER BY name ASC";
        return $this->db->getRows($sql, [$stateId]);
    }
    
    /**
     * Get active cities by state
     */
    public function getActiveByState($stateId) {
        $sql = "SELECT * FROM {$this->table} WHERE state_id = ? AND status = 1 AND deleted_at IS NULL ORDER BY name ASC";
        return $this->db->getRows($sql, [$stateId]);
    }
    
    /**
     * Check if city name exists in state
     */
    public function nameExistsInState($name, $stateId, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE name = ? AND state_id = ? AND deleted_at IS NULL";
        $params = [$name, $stateId];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        return $this->db->getValue($sql, $params) > 0;
    }
    
    /**
     * Get cities for employee
     */
    public function getCitiesForEmployee($userId, $stateId) {
        $sql = "SELECT c.*
                FROM cities c
                LEFT JOIN employee_territories et ON c.id = et.city_id AND et.user_id = ? AND et.deleted_at IS NULL
                WHERE c.state_id = ? AND c.status = 1 AND c.deleted_at IS NULL
                AND (et.id IS NOT NULL OR EXISTS (
                    SELECT 1 FROM employee_territories et2
                    WHERE et2.user_id = ? AND et2.state_id = ? AND et2.city_id IS NULL AND et2.deleted_at IS NULL
                ))
                ORDER BY c.name ASC";
        
        return $this->db->getRows($sql, [$userId, $stateId, $userId, $stateId]);
    }
}

