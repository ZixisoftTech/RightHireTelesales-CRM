<?php
/**
 * Dashboard Controller
 * 
 * This class handles dashboard-related actions.
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
        // Get user role and ID
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];
        
        // Get dashboard data based on role
        if ($role == 'administrator') {
            // Get overall statistics
            $stats = [
                'total_leads' => $this->leadModel->count(),
                'new_leads' => $this->leadModel->countByStatus('new'),
                'follow_ups' => $this->leadModel->countByStatus('follow_up'),
                'interested' => $this->leadModel->countByStatus('interested'),
                'wins' => $this->leadModel->countByStatus('win')
            ];
            
            // Get today's follow-ups for all employees
            $todayFollowUps = $this->leadModel->getTodayFollowUps();
            
            // Get recent call logs
            $recentCalls = $this->callLogModel->getRecent(10);
            
            // Get conversion rates by employee
            $employeeStats = $this->leadModel->getEmployeeConversionRates();
            
            // Get daily call count for the last 7 days
            $dailyCallCount = $this->callLogModel->getDailyCallCount(7);
        } else {
            // Get employee-specific statistics
            $stats = [
                'total_leads' => $this->leadModel->countByEmployee($userId),
                'new_leads' => $this->leadModel->countByEmployeeAndStatus($userId, 'new'),
                'follow_ups' => $this->leadModel->countByEmployeeAndStatus($userId, 'follow_up'),
                'interested' => $this->leadModel->countByEmployeeAndStatus($userId, 'interested'),
                'wins' => $this->leadModel->countByEmployeeAndStatus($userId, 'win')
            ];
            
            // Get today's follow-ups for this employee
            $todayFollowUps = $this->leadModel->getTodayFollowUpsByEmployee($userId);
            
            // Get recent call logs for this employee
            $recentCalls = $this->callLogModel->getRecentByEmployee($userId, 10);
            
            // Get personal conversion rate over time
            $employeeStats = $this->leadModel->getEmployeeConversionTrend($userId);
            
            // Get daily call count for this employee for the last 7 days
            $dailyCallCount = $this->callLogModel->getDailyCallCountByEmployee($userId, 7);
        }
        
        // Include dashboard view
        include 'views/dashboard/index.php';
    }
}

