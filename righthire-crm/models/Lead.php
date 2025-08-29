<?php
/**
 * Lead Model
 * 
 * This model handles all lead-related database operations.
 */

require_once 'models/Model.php';

class Lead extends Model {
    /**
     * Get leads based on filters and user role
     * 
     * @param array $filters Filter parameters
     * @param bool $paginate Whether to paginate results
     * @param int $page Current page number
     * @param int $perPage Items per page
     * @return array Leads
     */
    public function getLeads($filters = [], $paginate = true, $page = 1, $perPage = 20) {
        $db = Database::getInstance();
        
        // Base query
        $sql = "SELECT l.*, 
                s.name AS state_name, 
                c.name AS city_name, 
                u.name AS assigned_to_name 
                FROM leads l 
                LEFT JOIN states s ON l.state_id = s.id 
                LEFT JOIN cities c ON l.city_id = c.id 
                LEFT JOIN users u ON l.assigned_to = u.id 
                WHERE l.deleted_at IS NULL";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['state_id'])) {
            $sql .= " AND l.state_id = ?";
            $params[] = $filters['state_id'];
        }
        
        if (!empty($filters['city_id'])) {
            $sql .= " AND l.city_id = ?";
            $params[] = $filters['city_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['assigned_to'])) {
            $sql .= " AND l.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(l.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(l.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (l.name LIKE ? OR l.email LIKE ? OR l.phone LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Apply user role restrictions
        if (!hasRole('administrator')) {
            $userId = $_SESSION['user_id'];
            
            // Get user's territories
            $territorySql = "SELECT state_id, city_id FROM employee_territories WHERE user_id = ? AND deleted_at IS NULL";
            $territories = $db->getRows($territorySql, [$userId]);
            
            if (!empty($territories)) {
                $territoryConditions = [];
                
                foreach ($territories as $territory) {
                    if (empty($territory['city_id'])) {
                        // User has access to entire state
                        $territoryConditions[] = "(l.state_id = ?)";
                        $params[] = $territory['state_id'];
                    } else {
                        // User has access to specific city
                        $territoryConditions[] = "(l.state_id = ? AND l.city_id = ?)";
                        $params[] = $territory['state_id'];
                        $params[] = $territory['city_id'];
                    }
                }
                
                // Add assigned leads
                $territoryConditions[] = "l.assigned_to = ?";
                $params[] = $userId;
                
                $sql .= " AND (" . implode(" OR ", $territoryConditions) . ")";
            } else {
                // User has no territories, only show assigned leads
                $sql .= " AND l.assigned_to = ?";
                $params[] = $userId;
            }
        }
        
        // Order by
        $sql .= " ORDER BY l.created_at DESC";
        
        // Pagination
        if ($paginate) {
            // Get total count
            $countSql = "SELECT COUNT(*) FROM (" . $sql . ") AS count_query";
            $totalCount = $db->getVar($countSql, $params);
            
            // Calculate pagination
            $totalPages = ceil($totalCount / $perPage);
            $offset = ($page - 1) * $perPage;
            
            // Add limit
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;
            
            // Get leads
            $leads = $db->getRows($sql, $params);
            
            return [
                'leads' => $leads,
                'pagination' => [
                    'total' => $totalCount,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => $totalPages
                ]
            ];
        } else {
            // Get all leads without pagination
            return $db->getRows($sql, $params);
        }
    }
    
    /**
     * Get lead by ID
     * 
     * @param int $id Lead ID
     * @return array|false Lead data or false if not found
     */
    public function getLeadById($id) {
        $db = Database::getInstance();
        
        $sql = "SELECT l.*, 
                s.name AS state_name, 
                c.name AS city_name, 
                u.name AS assigned_to_name 
                FROM leads l 
                LEFT JOIN states s ON l.state_id = s.id 
                LEFT JOIN cities c ON l.city_id = c.id 
                LEFT JOIN users u ON l.assigned_to = u.id 
                WHERE l.id = ? AND l.deleted_at IS NULL";
        
        return $db->getRow($sql, [$id]);
    }
    
    /**
     * Create lead
     * 
     * @param array $data Lead data
     * @return int|false Lead ID or false on failure
     */
    public function createLead($data) {
        $db = Database::getInstance();
        
        // Set created_by
        $data['created_by'] = $_SESSION['user_id'];
        
        // Insert lead
        return $db->insert('leads', $data);
    }
    
    /**
     * Update lead
     * 
     * @param int $id Lead ID
     * @param array $data Lead data
     * @return bool Success or failure
     */
    public function updateLead($id, $data) {
        $db = Database::getInstance();
        
        // Set updated_by
        $data['updated_by'] = $_SESSION['user_id'];
        
        // Update lead
        return $db->update('leads', $data, 'id = ?', [$id]);
    }
    
    /**
     * Delete lead (soft delete)
     * 
     * @param int $id Lead ID
     * @return bool Success or failure
     */
    public function deleteLead($id) {
        $db = Database::getInstance();
        
        // Set deleted_at and updated_by
        $data = [
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_by' => $_SESSION['user_id']
        ];
        
        // Update lead
        return $db->update('leads', $data, 'id = ?', [$id]);
    }
    
    /**
     * Check if user has access to lead
     * 
     * @param int $leadId Lead ID
     * @param int $userId User ID
     * @return bool Whether user has access
     */
    public function checkLeadAccess($leadId, $userId) {
        $db = Database::getInstance();
        
        // Get lead
        $lead = $this->getLeadById($leadId);
        
        if (!$lead) {
            return false;
        }
        
        // Check if user is assigned to lead
        if ($lead['assigned_to'] == $userId) {
            return true;
        }
        
        // Check if lead is in user's territory
        $sql = "SELECT COUNT(*) FROM employee_territories 
                WHERE user_id = ? AND deleted_at IS NULL 
                AND (
                    (state_id = ? AND city_id IS NULL) OR 
                    (state_id = ? AND city_id = ?)
                )";
        
        $params = [
            $userId,
            $lead['state_id'],
            $lead['state_id'],
            $lead['city_id']
        ];
        
        $count = $db->getVar($sql, $params);
        
        return $count > 0;
    }
    
    /**
     * Get lead statistics
     * 
     * @return array Statistics
     */
    public function getLeadStats() {
        $db = Database::getInstance();
        
        $stats = [
            'total' => 0,
            'new_leads' => 0,
            'follow_ups' => 0,
            'not_attend' => 0,
            'wrong_number' => 0,
            'other' => 0,
            'dead' => 0,
            'interested' => 0,
            'wins' => 0
        ];
        
        // Base query
        $sql = "SELECT COUNT(*) FROM leads WHERE deleted_at IS NULL";
        $params = [];
        
        // Apply user role restrictions
        if (!hasRole('administrator')) {
            $userId = $_SESSION['user_id'];
            
            // Get user's territories
            $territorySql = "SELECT state_id, city_id FROM employee_territories WHERE user_id = ? AND deleted_at IS NULL";
            $territories = $db->getRows($territorySql, [$userId]);
            
            if (!empty($territories)) {
                $territoryConditions = [];
                
                foreach ($territories as $territory) {
                    if (empty($territory['city_id'])) {
                        // User has access to entire state
                        $territoryConditions[] = "(state_id = ?)";
                        $params[] = $territory['state_id'];
                    } else {
                        // User has access to specific city
                        $territoryConditions[] = "(state_id = ? AND city_id = ?)";
                        $params[] = $territory['state_id'];
                        $params[] = $territory['city_id'];
                    }
                }
                
                // Add assigned leads
                $territoryConditions[] = "assigned_to = ?";
                $params[] = $userId;
                
                $sql .= " AND (" . implode(" OR ", $territoryConditions) . ")";
            } else {
                // User has no territories, only show assigned leads
                $sql .= " AND assigned_to = ?";
                $params[] = $userId;
            }
        }
        
        // Get total count
        $stats['total'] = $db->getVar($sql, $params);
        
        // Get counts by status
        $statuses = ['new', 'follow_up', 'not_attend', 'wrong_number', 'other', 'dead', 'interested', 'win'];
        
        foreach ($statuses as $status) {
            $statusSql = $sql . " AND status = ?";
            $statusParams = array_merge($params, [$status]);
            
            $key = $status == 'new' ? 'new_leads' : ($status == 'follow_up' ? 'follow_ups' : ($status == 'win' ? 'wins' : $status));
            $stats[$key] = $db->getVar($statusSql, $statusParams);
        }
        
        return $stats;
    }
    
    /**
     * Get today's follow-ups
     * 
     * @return array Follow-ups
     */
    public function getTodayFollowUps() {
        $db = Database::getInstance();
        
        // Base query
        $sql = "SELECT l.id, l.name, l.phone, l.follow_up_date 
                FROM leads l 
                WHERE l.deleted_at IS NULL 
                AND l.status = 'follow_up' 
                AND DATE(l.follow_up_date) = CURDATE()";
        
        $params = [];
        
        // Apply user role restrictions
        if (!hasRole('administrator')) {
            $userId = $_SESSION['user_id'];
            
            // Get user's territories
            $territorySql = "SELECT state_id, city_id FROM employee_territories WHERE user_id = ? AND deleted_at IS NULL";
            $territories = $db->getRows($territorySql, [$userId]);
            
            if (!empty($territories)) {
                $territoryConditions = [];
                
                foreach ($territories as $territory) {
                    if (empty($territory['city_id'])) {
                        // User has access to entire state
                        $territoryConditions[] = "(l.state_id = ?)";
                        $params[] = $territory['state_id'];
                    } else {
                        // User has access to specific city
                        $territoryConditions[] = "(l.state_id = ? AND l.city_id = ?)";
                        $params[] = $territory['state_id'];
                        $params[] = $territory['city_id'];
                    }
                }
                
                // Add assigned leads
                $territoryConditions[] = "l.assigned_to = ?";
                $params[] = $userId;
                
                $sql .= " AND (" . implode(" OR ", $territoryConditions) . ")";
            } else {
                // User has no territories, only show assigned leads
                $sql .= " AND l.assigned_to = ?";
                $params[] = $userId;
            }
        }
        
        // Order by follow-up date
        $sql .= " ORDER BY l.follow_up_date ASC";
        
        return $db->getRows($sql, $params);
    }
    
    /**
     * Get daily call count
     * 
     * @param int $days Number of days
     * @return array Daily call counts
     */
    public function getDailyCallCount($days = 7) {
        $db = Database::getInstance();
        
        // Base query
        $sql = "SELECT DATE(cl.created_at) AS date, COUNT(*) AS count 
                FROM call_logs cl 
                JOIN leads l ON cl.lead_id = l.id 
                WHERE cl.deleted_at IS NULL 
                AND l.deleted_at IS NULL 
                AND DATE(cl.created_at) >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
        
        $params = [$days];
        
        // Apply user role restrictions
        if (!hasRole('administrator')) {
            $userId = $_SESSION['user_id'];
            
            // Get user's territories
            $territorySql = "SELECT state_id, city_id FROM employee_territories WHERE user_id = ? AND deleted_at IS NULL";
            $territories = $db->getRows($territorySql, [$userId]);
            
            if (!empty($territories)) {
                $territoryConditions = [];
                
                foreach ($territories as $territory) {
                    if (empty($territory['city_id'])) {
                        // User has access to entire state
                        $territoryConditions[] = "(l.state_id = ?)";
                        $params[] = $territory['state_id'];
                    } else {
                        // User has access to specific city
                        $territoryConditions[] = "(l.state_id = ? AND l.city_id = ?)";
                        $params[] = $territory['state_id'];
                        $params[] = $territory['city_id'];
                    }
                }
                
                // Add assigned leads
                $territoryConditions[] = "l.assigned_to = ?";
                $params[] = $userId;
                
                $sql .= " AND (" . implode(" OR ", $territoryConditions) . ")";
            } else {
                // User has no territories, only show assigned leads
                $sql .= " AND l.assigned_to = ?";
                $params[] = $userId;
            }
        }
        
        // Group by date and order by date
        $sql .= " GROUP BY DATE(cl.created_at) ORDER BY DATE(cl.created_at) ASC";
        
        return $db->getRows($sql, $params);
    }
    
    /**
     * Get recent call logs
     * 
     * @param int $limit Number of logs to get
     * @return array Recent call logs
     */
    public function getRecentCallLogs($limit = 5) {
        $db = Database::getInstance();
        
        // Base query
        $sql = "SELECT cl.*, l.id AS lead_id, l.name AS lead_name 
                FROM call_logs cl 
                JOIN leads l ON cl.lead_id = l.id 
                WHERE cl.deleted_at IS NULL 
                AND l.deleted_at IS NULL";
        
        $params = [];
        
        // Apply user role restrictions
        if (!hasRole('administrator')) {
            $userId = $_SESSION['user_id'];
            
            // Get user's territories
            $territorySql = "SELECT state_id, city_id FROM employee_territories WHERE user_id = ? AND deleted_at IS NULL";
            $territories = $db->getRows($territorySql, [$userId]);
            
            if (!empty($territories)) {
                $territoryConditions = [];
                
                foreach ($territories as $territory) {
                    if (empty($territory['city_id'])) {
                        // User has access to entire state
                        $territoryConditions[] = "(l.state_id = ?)";
                        $params[] = $territory['state_id'];
                    } else {
                        // User has access to specific city
                        $territoryConditions[] = "(l.state_id = ? AND l.city_id = ?)";
                        $params[] = $territory['state_id'];
                        $params[] = $territory['city_id'];
                    }
                }
                
                // Add assigned leads
                $territoryConditions[] = "l.assigned_to = ?";
                $params[] = $userId;
                
                $sql .= " AND (" . implode(" OR ", $territoryConditions) . ")";
            } else {
                // User has no territories, only show assigned leads
                $sql .= " AND l.assigned_to = ?";
                $params[] = $userId;
            }
        }
        
        // Order by created_at and limit
        $sql .= " ORDER BY cl.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        return $db->getRows($sql, $params);
    }
    
    /**
     * Get employee performance stats
     * 
     * @return array Employee stats
     */
    public function getEmployeePerformanceStats() {
        $db = Database::getInstance();
        
        // Get employees
        $sql = "SELECT id, name FROM users WHERE role = 'employee' AND status = 1 AND deleted_at IS NULL";
        $employees = $db->getRows($sql);
        
        $stats = [];
        
        foreach ($employees as $employee) {
            // Get total leads
            $totalSql = "SELECT COUNT(*) FROM leads WHERE assigned_to = ? AND deleted_at IS NULL";
            $totalLeads = $db->getVar($totalSql, [$employee['id']]);
            
            // Get wins
            $winsSql = "SELECT COUNT(*) FROM leads WHERE assigned_to = ? AND status = 'win' AND deleted_at IS NULL";
            $wins = $db->getVar($winsSql, [$employee['id']]);
            
            // Calculate conversion rate
            $conversionRate = $totalLeads > 0 ? round(($wins / $totalLeads) * 100, 2) : 0;
            
            $stats[] = [
                'id' => $employee['id'],
                'name' => $employee['name'],
                'total_leads' => $totalLeads,
                'wins' => $wins,
                'conversion_rate' => $conversionRate
            ];
        }
        
        // Sort by conversion rate (descending)
        usort($stats, function($a, $b) {
            return $b['conversion_rate'] <=> $a['conversion_rate'];
        });
        
        return $stats;
    }
}

