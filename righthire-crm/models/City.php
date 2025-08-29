<?php
/**
 * City Model
 * 
 * This class handles city-related database operations.
 */

require_once 'Model.php';

class City extends Model {
    protected $table = 'cities';
    protected $fillable = [
        'state_id', 'name', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
    
    /**
     * Get all active cities
     * 
     * @return array
     */
    public function getAllActive() {
        $sql = "SELECT c.*, s.name as state_name 
                FROM {$this->table} c
                JOIN states s ON c.state_id = s.id
                WHERE c.status = 1 AND c.deleted_at IS NULL 
                ORDER BY s.name ASC, c.name ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get cities by state
     * 
     * @param int $stateId
     * @return array
     */
    public function getByState($stateId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE state_id = :state_id AND deleted_at IS NULL 
                ORDER BY name ASC";
        
        return $this->db->fetchAll($sql, ['state_id' => $stateId]);
    }
    
    /**
     * Get active cities by state
     * 
     * @param int $stateId
     * @return array
     */
    public function getActiveByState($stateId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE state_id = :state_id AND status = 1 AND deleted_at IS NULL 
                ORDER BY name ASC";
        
        return $this->db->fetchAll($sql, ['state_id' => $stateId]);
    }
    
    /**
     * Get city with state
     * 
     * @param int $id
     * @return array|bool
     */
    public function getWithState($id) {
        $sql = "SELECT c.*, s.name as state_name 
                FROM {$this->table} c
                JOIN states s ON c.state_id = s.id
                WHERE c.id = :id AND c.deleted_at IS NULL";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get all cities with state
     * 
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getAllWithState($page = 1, $perPage = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT c.*, s.name as state_name 
                FROM {$this->table} c
                JOIN states s ON c.state_id = s.id
                WHERE c.deleted_at IS NULL 
                ORDER BY s.name ASC, c.name ASC 
                LIMIT {$perPage} OFFSET {$offset}";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Count cities by state
     * 
     * @param int $stateId
     * @return int
     */
    public function countByState($stateId) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE state_id = :state_id AND deleted_at IS NULL";
        
        $result = $this->db->fetch($sql, ['state_id' => $stateId]);
        
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * Toggle city status
     * 
     * @param int $id
     * @return bool
     */
    public function toggleStatus($id) {
        $city = $this->find($id);
        
        if (!$city) {
            return false;
        }
        
        $newStatus = $city['status'] == 1 ? 0 : 1;
        
        return $this->update($id, ['status' => $newStatus]);
    }
    
    /**
     * Check if city has leads
     * 
     * @param int $id
     * @return bool
     */
    public function hasLeads($id) {
        $sql = "SELECT COUNT(*) as count FROM leads WHERE city_id = :city_id AND deleted_at IS NULL";
        $result = $this->db->fetch($sql, ['city_id' => $id]);
        
        return $result && $result['count'] > 0;
    }
    
    /**
     * Get city with lead count
     * 
     * @param int $id
     * @return array|bool
     */
    public function getWithLeadCount($id) {
        $city = $this->getWithState($id);
        
        if (!$city) {
            return false;
        }
        
        $sql = "SELECT COUNT(*) as lead_count FROM leads WHERE city_id = :city_id AND deleted_at IS NULL";
        $result = $this->db->fetch($sql, ['city_id' => $id]);
        
        $city['lead_count'] = $result ? (int)$result['lead_count'] : 0;
        
        return $city;
    }
    
    /**
     * Get all cities with lead count
     * 
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getAllWithLeadCount($page = 1, $perPage = RECORDS_PER_PAGE) {
        $cities = $this->getAllWithState($page, $perPage);
        
        foreach ($cities as &$city) {
            $sql = "SELECT COUNT(*) as lead_count FROM leads WHERE city_id = :city_id AND deleted_at IS NULL";
            $result = $this->db->fetch($sql, ['city_id' => $city['id']]);
            
            $city['lead_count'] = $result ? (int)$result['lead_count'] : 0;
        }
        
        return $cities;
    }
}

