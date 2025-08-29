<?php
/**
 * Report Controller
 * 
 * This controller handles all report-related actions.
 */

require_once 'models/Lead.php';
require_once 'models/State.php';
require_once 'models/City.php';
require_once 'models/User.php';

class ReportController {
    private $leadModel;
    private $stateModel;
    private $cityModel;
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->leadModel = new Lead();
        $this->stateModel = new State();
        $this->cityModel = new City();
        $this->userModel = new User();
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
        
        // Include view
        include 'views/reports/index.php';
    }
    
    /**
     * Lead status report
     */
    public function leadStatus() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        // Get filter parameters
        $stateId = isset($_GET['state_id']) ? (int)$_GET['state_id'] : 0;
        $cityId = isset($_GET['city_id']) ? (int)$_GET['city_id'] : 0;
        $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
        $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
        $startDate = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : '';
        $endDate = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : '';
        
        // Get page number
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get leads
        $leads = $this->leadModel->getFilteredLeads(
            $page,
            $stateId,
            $cityId,
            $status,
            $employeeId,
            $startDate,
            $endDate
        );
        
        // Get total count for pagination
        $totalCount = $this->leadModel->countFilteredLeads(
            $stateId,
            $cityId,
            $status,
            $employeeId,
            $startDate,
            $endDate
        );
        
        $totalPages = ceil($totalCount / RECORDS_PER_PAGE);
        
        // Get filter options
        if (hasRole('administrator')) {
            $states = $this->stateModel->getActiveStates();
            $employees = $this->userModel->getAllActiveEmployees();
        } else {
            $states = $this->stateModel->getStatesForEmployee($_SESSION['user_id']);
            $employees = [];
        }
        
        // Include view
        include 'views/reports/lead_status.php';
    }
    
    /**
     * Call log report
     */
    public function callLog() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        // Get filter parameters
        $leadId = isset($_GET['lead_id']) ? (int)$_GET['lead_id'] : 0;
        $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
        $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
        $startDate = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : '';
        $endDate = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : '';
        
        // Get page number
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get call logs
        $callLogs = $this->leadModel->getFilteredCallLogs(
            $page,
            $leadId,
            $status,
            $employeeId,
            $startDate,
            $endDate
        );
        
        // Get total count for pagination
        $totalCount = $this->leadModel->countFilteredCallLogs(
            $leadId,
            $status,
            $employeeId,
            $startDate,
            $endDate
        );
        
        $totalPages = ceil($totalCount / RECORDS_PER_PAGE);
        
        // Get filter options
        if (hasRole('administrator')) {
            $employees = $this->userModel->getAllActiveEmployees();
        } else {
            $employees = [];
        }
        
        // Include view
        include 'views/reports/call_log.php';
    }
    
    /**
     * Employee performance report
     */
    public function employeePerformance() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        // Check if user is admin
        if (!hasRole('administrator')) {
            setFlashMessage('error', 'You do not have permission to access this page');
            redirect('dashboard');
            exit;
        }
        
        // Get filter parameters
        $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
        $startDate = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : '';
        $endDate = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : '';
        
        // Get employees
        $employees = $this->userModel->getAllActiveEmployees();
        
        // Get performance data
        $performanceData = $this->leadModel->getEmployeePerformance(
            $employeeId,
            $startDate,
            $endDate
        );
        
        // Include view
        include 'views/reports/employee_performance.php';
    }
    
    /**
     * Export lead status report
     */
    public function exportLeadStatus() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        // Get filter parameters
        $stateId = isset($_GET['state_id']) ? (int)$_GET['state_id'] : 0;
        $cityId = isset($_GET['city_id']) ? (int)$_GET['city_id'] : 0;
        $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
        $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
        $startDate = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : '';
        $endDate = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : '';
        
        // Get leads (no pagination for export)
        $leads = $this->leadModel->getFilteredLeadsForExport(
            $stateId,
            $cityId,
            $status,
            $employeeId,
            $startDate,
            $endDate
        );
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="lead_status_report.csv"');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add header row
        fputcsv($output, [
            'ID',
            'Name',
            'Phone',
            'Email',
            'State',
            'City',
            'Status',
            'Assigned To',
            'Created At',
            'Last Updated'
        ]);
        
        // Add data rows
        foreach ($leads as $lead) {
            fputcsv($output, [
                $lead['id'],
                $lead['name'],
                $lead['phone'],
                $lead['email'],
                $lead['state_name'],
                $lead['city_name'],
                $lead['status'],
                $lead['employee_name'],
                $lead['created_at'],
                $lead['updated_at']
            ]);
        }
        
        // Close output stream
        fclose($output);
        exit;
    }
    
    /**
     * Export call log report
     */
    public function exportCallLog() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        // Get filter parameters
        $leadId = isset($_GET['lead_id']) ? (int)$_GET['lead_id'] : 0;
        $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
        $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
        $startDate = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : '';
        $endDate = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : '';
        
        // Get call logs (no pagination for export)
        $callLogs = $this->leadModel->getFilteredCallLogsForExport(
            $leadId,
            $status,
            $employeeId,
            $startDate,
            $endDate
        );
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="call_log_report.csv"');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add header row
        fputcsv($output, [
            'ID',
            'Lead',
            'Status',
            'Remarks',
            'Follow-up Date',
            'Employee',
            'Created At'
        ]);
        
        // Add data rows
        foreach ($callLogs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['lead_name'],
                $log['status'],
                $log['remarks'],
                $log['follow_up_date'],
                $log['employee_name'],
                $log['created_at']
            ]);
        }
        
        // Close output stream
        fclose($output);
        exit;
    }
    
    /**
     * Export employee performance report
     */
    public function exportEmployeePerformance() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        // Check if user is admin
        if (!hasRole('administrator')) {
            setFlashMessage('error', 'You do not have permission to access this page');
            redirect('dashboard');
            exit;
        }
        
        // Get filter parameters
        $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
        $startDate = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : '';
        $endDate = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : '';
        
        // Get performance data
        $performanceData = $this->leadModel->getEmployeePerformance(
            $employeeId,
            $startDate,
            $endDate
        );
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="employee_performance_report.csv"');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add header row
        fputcsv($output, [
            'Employee',
            'Total Leads',
            'New',
            'Follow-up',
            'Not Attend',
            'Wrong Number',
            'Other',
            'Dead',
            'Interested',
            'Win',
            'Win Rate'
        ]);
        
        // Add data rows
        foreach ($performanceData as $data) {
            fputcsv($output, [
                $data['name'],
                $data['total_leads'],
                $data['new'],
                $data['follow_up'],
                $data['not_attend'],
                $data['wrong_number'],
                $data['other'],
                $data['dead'],
                $data['interested'],
                $data['win'],
                $data['win_rate']
            ]);
        }
        
        // Close output stream
        fclose($output);
        exit;
    }
}

