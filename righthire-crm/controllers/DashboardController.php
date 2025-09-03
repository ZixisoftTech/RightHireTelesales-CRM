<?php
/**
 * Dashboard Controller
 * 
 * This controller handles the dashboard page.
 */

require_once 'models/Lead.php';
require_once 'models/User.php';
require_once 'models/CallLog.php';

class DashboardController {
    private $leadModel;
    private $userModel;
    private $callLogModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->leadModel = new Lead();
        $this->userModel = new User();
        $this->callLogModel = new CallLog();
    }
    
    /**
     * Index page
     */
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
        $recentCalls = $this->callLogModel->getRecentCallLogs($employeeId, 10);
        
        // Get employee performance stats (for all users)
        $employeeStats = $this->leadModel->getEmployeePerformanceStats($employeeId, $startDate, $endDate);
        
        // Get all active employees for filter dropdown (admin only)
        $employees = [];
        if (hasRole('administrator')) {
            $employees = $this->userModel->getAllActiveEmployees();
        }
        
        // Include view
        include 'views/dashboard/index.php';
    }
}
