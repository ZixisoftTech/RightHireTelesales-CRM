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
        
        // Get lead statistics
        $stats = $this->leadModel->getLeadStats();
        
        // Get today's follow-ups
        $todayFollowUps = $this->leadModel->getTodayFollowUps();
        
        // Get daily call count
        $dailyCallCount = $this->leadModel->getDailyCallCount();
        
        // Get recent call logs
        $recentCalls = $this->leadModel->getRecentCallLogs();
        
        // Get employee performance stats (admin only)
        $employeeStats = [];
        if (hasRole('administrator')) {
            $employeeStats = $this->leadModel->getEmployeePerformanceStats();
        }
        
        // Include view
        include 'views/dashboard/index.php';
    }
}

