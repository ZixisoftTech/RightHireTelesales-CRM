<?php
/**
 * Report Controller
 * 
 * This controller handles all report-related actions.
 */

require_once 'models/Lead.php';
require_once 'models/CallLog.php';
require_once 'models/User.php';
require_once 'models/State.php';
require_once 'models/City.php';

class ReportController {
    private $leadModel;
    private $callLogModel;
    private $userModel;
    private $stateModel;
    private $cityModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->leadModel = new Lead();
        $this->callLogModel = new CallLog();
        $this->userModel = new User();
        $this->stateModel = new State();
        $this->cityModel = new City();
    }
    
    /**
     * Reports index page
     */
    public function index() {
        // Require login
        requireLogin();
        
        // Set page title
        $pageTitle = 'Reports';
        
        // Include view
        include 'views/reports/index.php';
    }
    
    /**
     * Lead status report
     */
    public function leadStatus() {
        // Require login
        requireLogin();
        
        // Get filters from URL
        $filters = [
            'state_id' => isset($_GET['state_id']) ? (int)$_GET['state_id'] : '',
            'city_id' => isset($_GET['city_id']) ? (int)$_GET['city_id'] : '',
            'assigned_to' => isset($_GET['assigned_to']) ? (int)$_GET['assigned_to'] : '',
            'date_from' => isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : date('Y-m-d', strtotime('-30 days')),
            'date_to' => isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : date('Y-m-d')
        ];
        
        // Get all active states for filter
        if (hasRole('administrator')) {
            $states = $this->stateModel->getAllActive();
        } else {
            $states = $this->stateModel->getStatesForEmployee(getCurrentUserId());
        }
        
        // Get cities for selected state
        $cities = [];
        if (!empty($filters['state_id'])) {
            if (hasRole('administrator')) {
                $cities = $this->cityModel->getActiveByState($filters['state_id']);
            } else {
                $cities = $this->cityModel->getCitiesForEmployee(getCurrentUserId(), $filters['state_id']);
            }
        }
        
        // Get all active employees for filter
        if (hasRole('administrator')) {
            $employees = $this->userModel->getAllActiveEmployees();
        }
        
        // Get lead stats
        $stats = $this->leadModel->getStats();
        
        // Get daily call count
        $dailyCallCount = $this->leadModel->getDailyCallCount(30);
        
        // Set page title
        $pageTitle = 'Lead Status Report';
        
        // Include view
        include 'views/reports/lead_status.php';
    }
    
    /**
     * Call log report
     */
    public function callLog() {
        // Require login
        requireLogin();
        
        // Get filters from URL
        $filters = [
            'date_from' => isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : date('Y-m-d', strtotime('-30 days')),
            'date_to' => isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : date('Y-m-d'),
            'user_id' => isset($_GET['user_id']) ? (int)$_GET['user_id'] : ''
        ];
        
        // Get page number
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get call logs by date range
        $callLogs = $this->callLogModel->getByDateRange($filters['date_from'], $filters['date_to'], $page);
        $totalCallLogs = $this->callLogModel->countByDateRange($filters['date_from'], $filters['date_to']);
        $totalPages = ceil($totalCallLogs / RECORDS_PER_PAGE);
        
        // Get call log stats
        $stats = $this->callLogModel->getStatsByDateRange($filters['date_from'], $filters['date_to']);
        
        // Get all active employees for filter
        if (hasRole('administrator')) {
            $employees = $this->userModel->getAllActiveEmployees();
        }
        
        // Set page title
        $pageTitle = 'Call Log Report';
        
        // Include view
        include 'views/reports/call_log.php';
    }
    
    /**
     * Employee performance report
     */
    public function employeePerformance() {
        // Require admin
        requireAdmin();
        
        // Get filters from URL
        $filters = [
            'date_from' => isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : date('Y-m-d', strtotime('-30 days')),
            'date_to' => isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : date('Y-m-d'),
            'user_id' => isset($_GET['user_id']) ? (int)$_GET['user_id'] : ''
        ];
        
        // Get all active employees for filter
        $employees = $this->userModel->getAllActiveEmployees();
        
        // Get employee performance stats
        $employeeStats = $this->userModel->getEmployeePerformanceStats();
        
        // Get employee performance trend
        $employeeTrend = [];
        
        if (!empty($filters['user_id'])) {
            $employeeTrend = $this->userModel->getEmployeePerformanceTrend($filters['user_id'], 6);
        }
        
        // Set page title
        $pageTitle = 'Employee Performance Report';
        
        // Include view
        include 'views/reports/employee_performance.php';
    }
    
    /**
     * Export lead status report
     */
    public function exportLeadStatus() {
        // Require login
        requireLogin();
        
        // Get filters from URL
        $filters = [
            'state_id' => isset($_GET['state_id']) ? (int)$_GET['state_id'] : '',
            'city_id' => isset($_GET['city_id']) ? (int)$_GET['city_id'] : '',
            'assigned_to' => isset($_GET['assigned_to']) ? (int)$_GET['assigned_to'] : '',
            'date_from' => isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : date('Y-m-d', strtotime('-30 days')),
            'date_to' => isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : date('Y-m-d')
        ];
        
        // Export leads to CSV
        $result = $this->leadModel->exportToCSV($filters);
        
        // Set filename
        $filename = 'lead_status_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Export to CSV
        exportToCSV($result['data'], $result['headers'], $filename);
    }
    
    /**
     * Export call log report
     */
    public function exportCallLog() {
        // Require login
        requireLogin();
        
        // Get filters from URL
        $filters = [
            'date_from' => isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : date('Y-m-d', strtotime('-30 days')),
            'date_to' => isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : date('Y-m-d'),
            'user_id' => isset($_GET['user_id']) ? (int)$_GET['user_id'] : ''
        ];
        
        // Get call logs by date range
        $callLogs = $this->callLogModel->getByDateRange($filters['date_from'], $filters['date_to'], 1, 1000);
        
        // Prepare data for CSV
        $data = [];
        $headers = ['ID', 'Lead', 'Status', 'Other Reason', 'Follow-up Date', 'Remarks', 'Created By', 'Created At'];
        
        foreach ($callLogs as $callLog) {
            $data[] = [
                $callLog['id'],
                $callLog['lead_name'],
                ucfirst(str_replace('_', ' ', $callLog['status'])),
                $callLog['other_reason'],
                $callLog['follow_up_date'] ? formatDateTime($callLog['follow_up_date']) : '',
                $callLog['remarks'],
                $callLog['created_by_name'],
                formatDateTime($callLog['created_at'])
            ];
        }
        
        // Set filename
        $filename = 'call_log_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Export to CSV
        exportToCSV($data, $headers, $filename);
    }
    
    /**
     * Export employee performance report
     */
    public function exportEmployeePerformance() {
        // Require admin
        requireAdmin();
        
        // Get employee performance stats
        $employeeStats = $this->userModel->getEmployeePerformanceStats();
        
        // Prepare data for CSV
        $data = [];
        $headers = ['ID', 'Name', 'Total Leads', 'Wins', 'Conversion Rate (%)'];
        
        foreach ($employeeStats as $employee) {
            $data[] = [
                $employee['id'],
                $employee['name'],
                $employee['total_leads'],
                $employee['wins'],
                $employee['conversion_rate']
            ];
        }
        
        // Set filename
        $filename = 'employee_performance_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Export to CSV
        exportToCSV($data, $headers, $filename);
    }
}

