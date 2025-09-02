<?php
/**
 * Base Model
 * 
 * This is the base model class that all other models extend.
 * It provides common functionality for database operations.
 */

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $auditEnabled = true;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Find a record by ID
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? AND deleted_at IS NULL";
        return $this->db->getRow($sql, [$id]);
    }
    
    /**
     * Get all records
     */
    public function getAll($page = 1, $limit = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY {$this->primaryKey} DESC LIMIT ?, ?";
        return $this->db->getRows($sql, [$offset, $limit]);
    }
    
    /**
     * Get all active records
     */
    public function getAllActive() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 1 AND deleted_at IS NULL ORDER BY name ASC";
        return $this->db->getRows($sql);
    }
    
    /**
     * Count all records
     */
    public function count() {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE deleted_at IS NULL";
        return $this->db->getValue($sql);
    }
    
    /**
     * Create a record
     */
    public function create($data) {
        // Check for soft-deleted record with same unique constraint
        if (method_exists($this, 'checkSoftDeletedDuplicate')) {
            $softDeletedId = $this->checkSoftDeletedDuplicate($data);
            if ($softDeletedId) {
                // Hard delete the soft-deleted record first
                $this->hardDelete($softDeletedId);
            }
        }
        
        // Add audit trail fields
        if (isLoggedIn()) {
            $data['created_by'] = getCurrentUserId();
        }
        
        // Filter data to only include fillable fields
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        // Insert record
        $id = $this->db->insert($this->table, $filteredData);
        
        // Log audit
        if ($this->auditEnabled) {
            $this->logAudit('create', $id, null, $filteredData);
        }
        
        return $id;
    }
    
    /**
     * Update a record
     */
    public function update($id, $data) {
        // Get old values for audit
        $oldValues = $this->find($id);
        
        // Add audit trail fields
        if (isLoggedIn()) {
            $data['updated_by'] = getCurrentUserId();
        }
        
        // Filter data to only include fillable fields
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        // Update record
        $result = $this->db->update($this->table, $filteredData, "{$this->primaryKey} = ?", [$id]);
        
        // Log audit
        if ($this->auditEnabled && $result) {
            $this->logAudit('update', $id, $oldValues, $filteredData);
        }
        
        return $result;
    }
    
    /**
     * Delete a record (soft delete)
     */
    public function delete($id) {
        return $this->softDelete($id);
    }
    
    /**
     * Soft delete a record
     * 
     * This method marks a record as deleted by setting the deleted_at field
     * 
     * @param int $id Record ID
     * @return bool Success or failure
     */
    public function softDelete($id) {
        // Get old values for audit
        $oldValues = $this->find($id);
        
        // Soft delete
        $data = [
            'deleted_at' => date('Y-m-d H:i:s')
        ];
        
        if (isLoggedIn()) {
            $data['updated_by'] = getCurrentUserId();
        }
        
        // Update record
        $result = $this->db->update($this->table, $data, "{$this->primaryKey} = ?", [$id]);
        
        // Log audit
        if ($this->auditEnabled && $result) {
            $this->logAudit('delete', $id, $oldValues, $data);
        }
        
        return $result;
    }
    
    /**
     * Hard delete a record
     * 
     * This method completely removes a record from the database
     * instead of just marking it as deleted.
     * 
     * @param int $id Record ID
     * @return bool Success or failure
     */
    public function hardDelete($id) {
        // Get old values for audit
        $oldValues = $this->find($id);
        
        // Hard delete
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $result = $this->db->query($sql, [$id]);
        
        // Log audit
        if ($this->auditEnabled && $oldValues) {
            $this->logAudit('hard_delete', $id, $oldValues, null);
        }
        
        return true;
    }
    
    /**
     * Toggle status
     */
    public function toggleStatus($id) {
        // Get current status
        $record = $this->find($id);
        
        if (!$record) {
            return false;
        }
        
        // Toggle status
        $newStatus = $record['status'] == 1 ? 0 : 1;
        
        // Update record
        return $this->update($id, ['status' => $newStatus]);
    }
    
    /**
     * Log audit
     */
    protected function logAudit($action, $recordId, $oldValues, $newValues) {
        if (!isLoggedIn()) {
            return;
        }
        
        $data = [
            'user_id' => getCurrentUserId(),
            'action' => $action,
            'table_name' => $this->table,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null
        ];
        
        $this->db->insert('audit_logs', $data);
    }
}
