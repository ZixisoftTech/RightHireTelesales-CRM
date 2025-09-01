<?php
/**
 * Add dashboard filters (Employee Selector, Date Range, Status)
 * 
 * This update adds filtering capabilities to the dashboard.
 */

// Update DashboardController.php - index method
public function index() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        redirect('auth/login');
        exit;
    }
    
    // Get filter parameters
    $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
    $startDate = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : '';
    $endDate = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : '';
    $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
    
    // Validate employee access for non-admins
    if (!hasRole('administrator') && $employeeId !== 0 && $employeeId !== getCurrentUserId()) {
        $employeeId = getCurrentUserId();
    }
    
    // Get lead statistics with filters
    $stats = $this->leadModel->getLeadStats($employeeId, $startDate, $endDate, $status);
    
    // Get today's follow-ups with filters
    $todayFollowUps = $this->leadModel->getTodayFollowUps($employeeId);
    
    // Get missed follow-ups
    $missedFollowUps = $this->leadModel->getMissedFollowUps($employeeId);
    
    // Get daily call count with filters
    $dailyCallCount = $this->leadModel->getDailyCallCount($employeeId, $startDate, $endDate);
    
    // Get recent call logs with filters
    $recentCalls = $this->leadModel->getRecentCallLogs($employeeId, $startDate, $endDate);
    
    // Get employee performance stats (admin only)
    $employeeStats = [];
    if (hasRole('administrator')) {
        $employeeStats = $this->leadModel->getEmployeePerformanceStats($employeeId, $startDate, $endDate);
    }
    
    // Get all active employees for filter dropdown (admin only)
    $employees = [];
    if (hasRole('administrator')) {
        $employees = $this->userModel->getAllActiveEmployees();
    }
    
    // Include view
    include 'views/dashboard/index.php';
}

// Update Lead.php - add filter parameters to methods
public function getLeadStats($employeeId = 0, $startDate = '', $endDate = '', $status = '') {
    $userId = getCurrentUserId();
    $isAdmin = hasRole('administrator');
    
    // Base SQL for counting leads by status
    $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_leads,
                SUM(CASE WHEN status = 'follow_up' THEN 1 ELSE 0 END) as follow_ups,
                SUM(CASE WHEN status = 'not_attend' THEN 1 ELSE 0 END) as not_attend,
                SUM(CASE WHEN status = 'wrong_number' THEN 1 ELSE 0 END) as wrong_number,
                SUM(CASE WHEN status = 'other' THEN 1 ELSE 0 END) as other,
                SUM(CASE WHEN status = 'dead' THEN 1 ELSE 0 END) as dead,
                SUM(CASE WHEN status = 'interested' THEN 1 ELSE 0 END) as interested,
                SUM(CASE WHEN status = 'win' THEN 1 ELSE 0 END) as wins
            FROM {$this->table}
            WHERE deleted_at IS NULL";
    
    $params = [];
    
    // Apply employee filter
    if ($employeeId > 0) {
        $sql .= " AND assigned_to = ?";
        $params[] = $employeeId;
    } else if (!$isAdmin) {
        // If not admin and no specific employee selected, only show assigned leads
        $sql .= " AND assigned_to = ?";
        $params[] = $userId;
    }
    
    // Apply date filters
    if (!empty($startDate)) {
        $sql .= " AND DATE(created_at) >= ?";
        $params[] = $startDate;
    }
    
    if (!empty($endDate)) {
        $sql .= " AND DATE(created_at) <= ?";
        $params[] = $endDate;
    }
    
    // Apply status filter
    if (!empty($status)) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    
    return $this->db->getRow($sql, $params);
}

// Add getMissedFollowUps method to Lead.php
public function getMissedFollowUps($employeeId = 0) {
    $userId = getCurrentUserId();
    $isAdmin = hasRole('administrator');
    $today = date('Y-m-d');
    
    $sql = "SELECT l.*, s.name as state_name, c.name as city_name, u.name as assigned_to_name
            FROM {$this->table} l
            LEFT JOIN states s ON l.state_id = s.id
            LEFT JOIN cities c ON l.city_id = c.id
            LEFT JOIN users u ON l.assigned_to = u.id
            WHERE l.deleted_at IS NULL 
            AND l.status = 'follow_up' 
            AND DATE(l.follow_up_date) < ?
            AND l.follow_up_date IS NOT NULL";
    
    $params = [$today];
    
    // Apply employee filter
    if ($employeeId > 0) {
        $sql .= " AND l.assigned_to = ?";
        $params[] = $employeeId;
    } else if (!$isAdmin) {
        // If not admin and no specific employee selected, only show assigned leads
        $sql .= " AND l.assigned_to = ?";
        $params[] = $userId;
    }
    
    $sql .= " ORDER BY l.follow_up_date ASC";
    
    return $this->db->getRows($sql, $params);
}

// Update getEmployeePerformanceStats method in Lead.php to accept filters
public function getEmployeePerformanceStats($employeeId = 0, $startDate = '', $endDate = '') {
    $sql = "SELECT 
                u.id, 
                u.name,
                COUNT(DISTINCT l.id) as total_leads,
                COUNT(DISTINCT cl.id) as total_calls,
                SUM(CASE WHEN l.status = 'win' THEN 1 ELSE 0 END) as wins,
                SUM(CASE WHEN l.status = 'interested' THEN 1 ELSE 0 END) as total_interested,
                ROUND(CASE WHEN COUNT(DISTINCT l.id) > 0 THEN 
                    (SUM(CASE WHEN l.status = 'win' THEN 1 ELSE 0 END) / COUNT(DISTINCT l.id)) * 100 
                ELSE 0 END, 2) as conversion_rate
            FROM users u
            LEFT JOIN leads l ON u.id = l.assigned_to AND l.deleted_at IS NULL
            LEFT JOIN call_logs cl ON l.id = cl.lead_id AND cl.deleted_at IS NULL
            WHERE u.role = 'employee' AND u.deleted_at IS NULL";
    
    $params = [];
    
    // Apply employee filter
    if ($employeeId > 0) {
        $sql .= " AND u.id = ?";
        $params[] = $employeeId;
    }
    
    // Apply date filters
    if (!empty($startDate)) {
        $sql .= " AND (DATE(l.created_at) >= ? OR DATE(cl.created_at) >= ?)";
        $params[] = $startDate;
        $params[] = $startDate;
    }
    
    if (!empty($endDate)) {
        $sql .= " AND (DATE(l.created_at) <= ? OR DATE(cl.created_at) <= ?)";
        $params[] = $endDate;
        $params[] = $endDate;
    }
    
    $sql .= " GROUP BY u.id, u.name
              ORDER BY wins DESC, total_interested DESC
              LIMIT 5";
    
    return $this->db->getRows($sql, $params);
}

