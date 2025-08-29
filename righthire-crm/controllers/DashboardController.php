<?php
/**
 * Dashboard Controller
 * 
 * This controller handles all dashboard-related actions.
 */

require_once 'models/Lead.php';
require_once 'models/CallLog.php';
require_once 'models/User.php';

class DashboardController {
    private $leadModel;
    private $callLogModel;
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->leadModel = new Lead();
        $this->callLogModel = new CallLog();
        $this->userModel = new User();
    }
    
    /**
     * Dashboard index page
     */
    public function index() {
        // Require login
        requireLogin();
        
        // Get lead stats
        $stats = $this->leadModel->getStats();
        
        // Get today's follow-ups
        $todayFollowUps = $this->leadModel->getTodayFollowUps();
        
        // Get recent calls
        $recentCalls = $this->callLogModel->getRecent(5);
        
        // Get daily call count
        $dailyCallCount = $this->leadModel->getDailyCallCount(7);
        
        // Get employee stats
        if (hasRole('administrator')) {
            $employeeStats = $this->userModel->getEmployeePerformanceStats();
        } else {
            $employeeStats = $this->userModel->getEmployeePerformanceTrend(getCurrentUserId(), 6);
        }
        
        // Set page title
        $pageTitle = 'Dashboard';
        
        // Get current route
        $route = isset($_GET['route']) ? $_GET['route'] : 'dashboard';
        
        // Include view
        include 'views/dashboard/index.php';
    }
}

