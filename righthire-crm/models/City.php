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
     * Get city by name and state
     * 
     * @param string $name City name
     * @param int $stateId State ID
     * @return array|null City data or null if not found
     */
    public function getCityByNameAndState($name, $stateId) {
        $sql = "SELECT * FROM {$this->table} WHERE name = ? AND state_id = ? AND deleted_at IS NULL LIMIT 1";
        return $this->db->getRow($sql, [$name, $stateId]);
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }
    
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
     * Get all cities by state ID
     * 
     * @param int $stateId State ID
     * @return array All cities belonging to the state (including soft-deleted)
     */
    public function getAllByStateId($stateId) {
        $sql = "SELECT * FROM {$this->table} WHERE state_id = ? ORDER BY name ASC";
        return $this->db->getRows($sql, [$stateId]);
    }
    
    /**
     * Get cities by state ID - Alias for getByState for backward compatibility
     * 
     * @param int $stateId State ID
     * @return array Cities belonging to the state
     */
    public function getByStateId($stateId) {
        return $this->getByState($stateId);
    }
    
    /**
     * Get cities by state - Alias for getByState for backward compatibility
     * 
     * @param int $stateId State ID
     * @return array Cities belonging to the state
     */
    public function getCitiesByState($stateId) {
        return $this->getByState($stateId);
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
     * 
     * This method checks if a city with the given name already exists in the specified state.
     * It can optionally exclude a specific city ID from the check (useful for updates).
     * 
     * @param string $name City name
     * @param int $stateId State ID
     * @param int|null $excludeId City ID to exclude from the check (optional)
     * @return bool True if city name exists, false otherwise
     */
    public function nameExistsInState($name, $stateId, $excludeId = null) {
        // Check active cities first
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE name = ? AND state_id = ? AND deleted_at IS NULL";
        $params = [$name, $stateId];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $exists = $this->db->getValue($sql, $params) > 0;
        
        // If not found in active cities, also check soft-deleted cities
        if (!$exists) {
            $sql = "SELECT id FROM {$this->table} WHERE name = ? AND state_id = ? AND deleted_at IS NOT NULL";
            $params = [$name, $stateId];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $softDeletedId = $this->db->getValue($sql, $params);
            
            if ($softDeletedId) {
                // If there are any leads associated with this soft-deleted city,
                // we need to consider it as "existing" to prevent recreation
                $leadCount = $this->getLeadCount($softDeletedId);
                if ($leadCount > 0) {
                    return true;
                }
                
                // If no leads are associated, we can safely hard delete it
                // to allow recreation with the same name
                $this->hardDeleteSoftDeleted($softDeletedId);
            }
        }
        
        return $exists;
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
    
    /**
     * Check for soft-deleted duplicate city
     * 
     * @param array $data City data
     * @return int|null ID of soft-deleted duplicate or null if none exists
     */
    public function checkSoftDeletedDuplicate($data) {
        if (!isset($data['name']) || !isset($data['state_id'])) {
            return null;
        }
        
        $sql = "SELECT id FROM {$this->table} WHERE name = ? AND state_id = ? AND deleted_at IS NOT NULL";
        return $this->db->getValue($sql, [$data['name'], $data['state_id']]);
    }
    
    /**
     * Get count of leads by city ID
     * 
     * @param int $id City ID
     * @param bool $includeDeleted Whether to include soft-deleted leads in the count
     * @return int Number of leads
     */
    public function getLeadCount($id, $includeDeleted = false) {
        if ($includeDeleted) {
            // Count all leads (including soft-deleted ones)
            $sql = "SELECT COUNT(*) FROM leads WHERE city_id = ?";
        } else {
            // Count only active leads
            $sql = "SELECT COUNT(*) FROM leads WHERE city_id = ? AND deleted_at IS NULL";
        }
        return $this->db->getValue($sql, [$id]);
    }
    
    /**
     * Get leads by city ID with limit
     * 
     * @param int $id City ID
     * @param int $limit Maximum number of leads to return
     * @return array Leads data
     */
    public function getLeadsByCityId($id, $limit = 10) {
        $sql = "SELECT l.id, l.name, l.phone, l.email
                FROM leads l
                WHERE l.city_id = ? AND l.deleted_at IS NULL
                ORDER BY l.id DESC
                LIMIT ?";
        
        return $this->db->getRows($sql, [$id, $limit]);
    }
    
    /**
     * Hard delete a city
     * 
     * This method completely removes a city from the database
     * instead of just marking it as deleted.
     * 
     * @param int $id City ID
     * @return bool Success or failure
     */
    public function hardDelete($id) {
        // Delete the city
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }
    
    /**
     * Hard delete a soft-deleted city
     * 
     * This method completely removes a soft-deleted city from the database,
     * but only if there are no leads (including soft-deleted leads) associated with it.
     * 
     * @param int $id City ID
     * @return bool Success or failure
     */
    public function hardDeleteSoftDeleted($id) {
        // First check if there are any leads associated with this city (including soft-deleted leads)
        $leadCount = $this->getLeadCount($id, true);
        if ($leadCount > 0) {
            // Cannot hard delete a city with associated leads
            return false;
        }
        
        // Safe to delete
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }
}
