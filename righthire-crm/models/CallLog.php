<?php
/**
 * Call Log Model
 * 
 * This class handles call log-related database operations.
 */

require_once 'Model.php';

class CallLog extends Model {
    protected $table = 'call_logs';
    protected $fillable = [
        'lead_id', 'status', 'remarks', 'follow_up_date', 'created_by', 'created_at'
    ];
    protected $auditEnabled = false; // Disable audit trail for call logs
    
    /**
     * Get call logs by lead
     * 
     * @param int $leadId
     * @return array
     */
    public function getByLead($leadId) {
        $sql = "SELECT cl.*, u.name as created_by_name 
                FROM {$this->table} cl
                JOIN users u ON cl.created_by = u.id
                WHERE cl.lead_id = :lead_id
                ORDER BY cl.created_at DESC";
        
        return $this->db->fetchAll($sql, ['lead_id' => $leadId]);
    }
    
    /**
     * Get recent call logs
     * 
     * @param int $limit
     * @return array
     */
    public function getRecent($limit = 10) {
        $sql = "SELECT cl.*, 
                l.name as lead_name, 
                l.phone as lead_phone,
                u.name as created_by_name
                FROM {$this->table} cl
                JOIN leads l ON cl.lead_id = l.id
                JOIN users u ON cl.created_by = u.id
                ORDER BY cl.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get recent call logs by employee
     * 
     * @param int $employeeId
     * @param int $limit
     * @return array
     */
    public function getRecentByEmployee($employeeId, $limit = 10) {
        $sql = "SELECT cl.*, 
                l.name as lead_name, 
                l.phone as lead_phone
                FROM {$this->table} cl
                JOIN leads l ON cl.lead_id = l.id
                WHERE cl.created_by = :employee_id
                ORDER BY cl.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, ['employee_id' => $employeeId]);
    }
    
    /**
     * Get call logs by date range
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getByDateRange($startDate, $endDate) {
        $sql = "SELECT cl.*, 
                l.name as lead_name, 
                l.phone as lead_phone,
                u.name as created_by_name
                FROM {$this->table} cl
                JOIN leads l ON cl.lead_id = l.id
                JOIN users u ON cl.created_by = u.id
                WHERE cl.created_at BETWEEN :start_date AND :end_date
                ORDER BY cl.created_at DESC";
        
        return $this->db->fetchAll($sql, [
            'start_date' => $startDate . ' 00:00:00',
            'end_date' => $endDate . ' 23:59:59'
        ]);
    }
    
    /**
     * Get call logs by employee and date range
     * 
     * @param int $employeeId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getByEmployeeAndDateRange($employeeId, $startDate, $endDate) {
        $sql = "SELECT cl.*, 
                l.name as lead_name, 
                l.phone as lead_phone
                FROM {$this->table} cl
                JOIN leads l ON cl.lead_id = l.id
                WHERE cl.created_by = :employee_id
                AND cl.created_at BETWEEN :start_date AND :end_date
                ORDER BY cl.created_at DESC";
        
        return $this->db->fetchAll($sql, [
            'employee_id' => $employeeId,
            'start_date' => $startDate . ' 00:00:00',
            'end_date' => $endDate . ' 23:59:59'
        ]);
    }
    
    /**
     * Count call logs by status
     * 
     * @param string $status
     * @return int
     */
    public function countByStatus($status) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = :status";
        
        $result = $this->db->fetch($sql, ['status' => $status]);
        
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * Count call logs by employee and status
     * 
     * @param int $employeeId
     * @param string $status
     * @return int
     */
    public function countByEmployeeAndStatus($employeeId, $status) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE created_by = :employee_id AND status = :status";
        
        $result = $this->db->fetch($sql, [
            'employee_id' => $employeeId,
            'status' => $status
        ]);
        
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * Get daily call count for the last n days
     * 
     * @param int $days
     * @return array
     */
    public function getDailyCallCount($days = 7) {
        $result = [];
        $currentDate = new DateTime();
        
        for ($i = 0; $i < $days; $i++) {
            $date = clone $currentDate;
            $date->modify("-{$i} day");
            $dateStr = $date->format('Y-m-d');
            
            $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE DATE(created_at) = :date";
            
            $count = $this->db->fetch($sql, ['date' => $dateStr]);
            
            $result[] = [
                'date' => $date->format('M d'),
                'count' => $count ? (int)$count['total'] : 0
            ];
        }
        
        // Reverse to get chronological order
        return array_reverse($result);
    }
    
    /**
     * Get daily call count by employee for the last n days
     * 
     * @param int $employeeId
     * @param int $days
     * @return array
     */
    public function getDailyCallCountByEmployee($employeeId, $days = 7) {
        $result = [];
        $currentDate = new DateTime();
        
        for ($i = 0; $i < $days; $i++) {
            $date = clone $currentDate;
            $date->modify("-{$i} day");
            $dateStr = $date->format('Y-m-d');
            
            $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE DATE(created_at) = :date AND created_by = :employee_id";
            
            $count = $this->db->fetch($sql, [
                'date' => $dateStr,
                'employee_id' => $employeeId
            ]);
            
            $result[] = [
                'date' => $date->format('M d'),
                'count' => $count ? (int)$count['total'] : 0
            ];
        }
        
        // Reverse to get chronological order
        return array_reverse($result);
    }
}

