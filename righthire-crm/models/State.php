<?php
/**
 * State Model
 * 
 * This class handles state-related database operations.
 */

require_once 'Model.php';

class State extends Model {
    protected $table = 'states';
    protected $fillable = [
        'name', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
    
    /**
     * Get all active states
     * 
     * @return array
     */
    public function getAllActive() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 1 AND deleted_at IS NULL ORDER BY name ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get state with cities
     * 
     * @param int $stateId
     * @return array
     */
    public function getWithCities($stateId) {
        $state = $this->find($stateId);
        
        if (!$state) {
            return null;
        }
        
        $sql = "SELECT * FROM cities WHERE state_id = :state_id AND deleted_at IS NULL ORDER BY name ASC";
        $cities = $this->db->fetchAll($sql, ['state_id' => $stateId]);
        
        $state['cities'] = $cities;
        
        return $state;
    }
    
    /**
     * Get all states with cities
     * 
     * @return array
     */
    public function getAllWithCities() {
        $states = $this->getAllActive();
        
        foreach ($states as &$state) {
            $sql = "SELECT * FROM cities WHERE state_id = :state_id AND deleted_at IS NULL ORDER BY name ASC";
            $cities = $this->db->fetchAll($sql, ['state_id' => $state['id']]);
            
            $state['cities'] = $cities;
        }
        
        return $states;
    }
    
    /**
     * Toggle state status
     * 
     * @param int $id
     * @return bool
     */
    public function toggleStatus($id) {
        $state = $this->find($id);
        
        if (!$state) {
            return false;
        }
        
        $newStatus = $state['status'] == 1 ? 0 : 1;
        
        return $this->update($id, ['status' => $newStatus]);
    }
    
    /**
     * Check if state has cities
     * 
     * @param int $id
     * @return bool
     */
    public function hasCities($id) {
        $sql = "SELECT COUNT(*) as count FROM cities WHERE state_id = :state_id AND deleted_at IS NULL";
        $result = $this->db->fetch($sql, ['state_id' => $id]);
        
        return $result && $result['count'] > 0;
    }
    
    /**
     * Get state with city count
     * 
     * @param int $id
     * @return array|bool
     */
    public function getWithCityCount($id) {
        $state = $this->find($id);
        
        if (!$state) {
            return false;
        }
        
        $sql = "SELECT COUNT(*) as city_count FROM cities WHERE state_id = :state_id AND deleted_at IS NULL";
        $result = $this->db->fetch($sql, ['state_id' => $id]);
        
        $state['city_count'] = $result ? (int)$result['city_count'] : 0;
        
        return $state;
    }
    
    /**
     * Get all states with city count
     * 
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getAllWithCityCount($page = 1, $perPage = RECORDS_PER_PAGE) {
        $states = $this->getAll($page, $perPage);
        
        foreach ($states as &$state) {
            $sql = "SELECT COUNT(*) as city_count FROM cities WHERE state_id = :state_id AND deleted_at IS NULL";
            $result = $this->db->fetch($sql, ['state_id' => $state['id']]);
            
            $state['city_count'] = $result ? (int)$result['city_count'] : 0;
        }
        
        return $states;
    }
}

