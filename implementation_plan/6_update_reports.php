<?php
/**
 * Update reports
 * 
 * This update fixes the reporting system to work with the new lead status workflow.
 */

// Update ReportController.php - leadStatus method
public function leadStatus() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        redirect('auth/login');
        exit;
    }
    
    // Get filter parameters
    $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
    $startDate = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : '';
    $endDate = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : '';
    $stateId = isset($_GET['state_id']) ? (int)$_GET['state_id'] : 0;
    $cityId = isset($_GET['city_id']) ? (int)$_GET['city_id'] : 0;
    
    // Validate employee access for non-admins
    if (!hasRole('administrator') && $employeeId !== 0 && $employeeId !== getCurrentUserId()) {
        $employeeId = getCurrentUserId();
    }
    
    // Get lead status report data
    $reportData = $this->leadModel->getLeadStatusReport($employeeId, $startDate, $endDate, $stateId, $cityId);
    
    // Get states for filter
    $states = $this->stateModel->getActiveStates();
    
    // Get cities for selected state
    $cities = [];
    if ($stateId > 0) {
        $cities = $this->cityModel->getCitiesByState($stateId);
    }
    
    // Get employees for filter (admin only)
    $employees = [];
    if (hasRole('administrator')) {
        $employees = $this->userModel->getAllActiveEmployees();
    }
    
    // Include view
    include 'views/reports/lead_status.php';
}

// Update ReportController.php - callLog method
public function callLog() {
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
    
    // Get call log report data
    $reportData = $this->leadModel->getCallLogReport($employeeId, $startDate, $endDate, $status);
    
    // Get employees for filter (admin only)
    $employees = [];
    if (hasRole('administrator')) {
        $employees = $this->userModel->getAllActiveEmployees();
    }
    
    // Include view
    include 'views/reports/call_log.php';
}

// Update ReportController.php - employeePerformance method
public function employeePerformance() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        redirect('auth/login');
        exit;
    }
    
    // Get filter parameters
    $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
    $startDate = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : '';
    $endDate = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : '';
    
    // Validate employee access for non-admins
    if (!hasRole('administrator') && $employeeId !== 0 && $employeeId !== getCurrentUserId()) {
        $employeeId = getCurrentUserId();
    }
    
    // Get employee performance report data
    $reportData = $this->leadModel->getEmployeePerformance($employeeId, $startDate, $endDate);
    
    // Get employees for filter (admin only)
    $employees = [];
    if (hasRole('administrator')) {
        $employees = $this->userModel->getAllActiveEmployees();
    }
    
    // Include view
    include 'views/reports/employee_performance.php';
}

// Add getLeadStatusReport method to Lead.php
public function getLeadStatusReport($employeeId = 0, $startDate = '', $endDate = '', $stateId = 0, $cityId = 0) {
    $sql = "SELECT 
                l.status,
                COUNT(*) as count,
                SUM(CASE WHEN DATE(l.created_at) = CURDATE() THEN 1 ELSE 0 END) as today_count,
                SUM(CASE WHEN DATE(l.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_count,
                SUM(CASE WHEN DATE(l.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as month_count
            FROM {$this->table} l
            WHERE l.deleted_at IS NULL";
    
    $params = [];
    
    // Apply employee filter
    if ($employeeId > 0) {
        $sql .= " AND l.assigned_to = ?";
        $params[] = $employeeId;
    } else if (!hasRole('administrator')) {
        // If not admin and no specific employee selected, only show assigned leads
        $sql .= " AND l.assigned_to = ?";
        $params[] = getCurrentUserId();
    }
    
    // Apply date filters
    if (!empty($startDate)) {
        $sql .= " AND DATE(l.created_at) >= ?";
        $params[] = $startDate;
    }
    
    if (!empty($endDate)) {
        $sql .= " AND DATE(l.created_at) <= ?";
        $params[] = $endDate;
    }
    
    // Apply state filter
    if ($stateId > 0) {
        $sql .= " AND l.state_id = ?";
        $params[] = $stateId;
    }
    
    // Apply city filter
    if ($cityId > 0) {
        $sql .= " AND l.city_id = ?";
        $params[] = $cityId;
    }
    
    $sql .= " GROUP BY l.status ORDER BY FIELD(l.status, 'new', 'follow_up', 'not_attend', 'wrong_number', 'other', 'dead', 'interested', 'win')";
    
    return $this->db->getRows($sql, $params);
}

// Add getCallLogReport method to Lead.php
public function getCallLogReport($employeeId = 0, $startDate = '', $endDate = '', $status = '') {
    $sql = "SELECT 
                cl.status,
                COUNT(*) as count,
                SUM(CASE WHEN DATE(cl.created_at) = CURDATE() THEN 1 ELSE 0 END) as today_count,
                SUM(CASE WHEN DATE(cl.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_count,
                SUM(CASE WHEN DATE(cl.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as month_count
            FROM call_logs cl
            JOIN leads l ON cl.lead_id = l.id
            WHERE cl.deleted_at IS NULL AND l.deleted_at IS NULL";
    
    $params = [];
    
    // Apply employee filter
    if ($employeeId > 0) {
        $sql .= " AND cl.created_by = ?";
        $params[] = $employeeId;
    } else if (!hasRole('administrator')) {
        // If not admin and no specific employee selected, only show calls made by the user
        $sql .= " AND cl.created_by = ?";
        $params[] = getCurrentUserId();
    }
    
    // Apply date filters
    if (!empty($startDate)) {
        $sql .= " AND DATE(cl.created_at) >= ?";
        $params[] = $startDate;
    }
    
    if (!empty($endDate)) {
        $sql .= " AND DATE(cl.created_at) <= ?";
        $params[] = $endDate;
    }
    
    // Apply status filter
    if (!empty($status)) {
        $sql .= " AND cl.status = ?";
        $params[] = $status;
    }
    
    $sql .= " GROUP BY cl.status ORDER BY FIELD(cl.status, 'new', 'follow_up', 'not_attend', 'wrong_number', 'other', 'dead', 'interested', 'win')";
    
    return $this->db->getRows($sql, $params);
}

