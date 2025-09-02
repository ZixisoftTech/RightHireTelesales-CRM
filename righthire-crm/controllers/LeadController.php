<?php
/**
 * Lead Controller
 * 
 * This controller handles all lead-related actions.
 */

require_once 'models/Lead.php';
require_once 'models/State.php';
require_once 'models/City.php';
require_once 'models/User.php';
require_once 'models/CallLog.php';

class LeadController {
    private $leadModel;
    private $stateModel;
    private $cityModel;
    private $userModel;
    private $callLogModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->leadModel = new Lead();
        $this->stateModel = new State();
        $this->cityModel = new City();
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
        $filters = [
            'state_id' => isset($_GET['state_id']) ? (int)$_GET['state_id'] : null,
            'city_id' => isset($_GET['city_id']) ? (int)$_GET['city_id'] : null,
            'status' => isset($_GET['status']) ? sanitizeInput($_GET['status']) : null,
            'assigned_to' => isset($_GET['assigned_to']) ? (int)$_GET['assigned_to'] : null,
            'date_from' => isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : null,
            'date_to' => isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : null,
            'search' => isset($_GET['search']) ? sanitizeInput($_GET['search']) : null
        ];
        
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;
        
        // Get leads
        $leads = $this->leadModel->getLeads($filters, true, $page, $perPage);
        $totalLeads = $this->leadModel->countLeads($filters);
        
        // Calculate pagination
        $totalPages = ceil($totalLeads / $perPage);
        
        // Get states for filter
        $states = $this->stateModel->getActiveStates();
        
        // Get cities for filter if state is selected
        $cities = [];
        if (isset($filters['state_id']) && !empty($filters['state_id'])) {
            $cities = $this->cityModel->getCitiesByState($filters['state_id']);
        }
        
        // Get employees for filter (admin only)
        $employees = [];
        if (hasRole('administrator')) {
            $employees = $this->userModel->getEmployees();
        }
        
        // Load view
        include 'views/leads/index.php';
    }
    
    /**
     * Create page
     */
    public function create() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        // Get states
        $states = $this->stateModel->getActiveStates();
        
        // Get cities if state is selected
        $cities = [];
        if (isset($_POST['state_id']) && !empty($_POST['state_id'])) {
            $cities = $this->cityModel->getCitiesByState($_POST['state_id']);
        }
        
        // Get employees (admin only)
        $employees = [];
        if (hasRole('administrator')) {
            $employees = $this->userModel->getEmployees();
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email']);
            $phone = sanitizeInput($_POST['phone']);
            $address = sanitizeInput($_POST['address']);
            $state_id = (int)$_POST['state_id'];
            $city_id = !empty($_POST['city_id']) ? (int)$_POST['city_id'] : null;
            // Always set status to 'new' for new leads
            $status = 'new';
            $remarks = isset($_POST['remarks']) ? sanitizeInput($_POST['remarks']) : '';
            $assigned_to = isset($_POST['assigned_to']) && !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
            
            // Validate input
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required';
            }
            
            if (empty($phone)) {
                $errors[] = 'Phone is required';
            }
            
            if (empty($state_id)) {
                $errors[] = 'State is required';
            }
            
            // If no errors, create lead
            if (empty($errors)) {
                // Prepare lead data
                $leadData = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'state_id' => $state_id,
                    'city_id' => $city_id,
                    'status' => $status,
                    'remarks' => $remarks,
                    'assigned_to' => $assigned_to,
                    'created_by' => $_SESSION['user_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // Create lead
                $leadId = $this->leadModel->createLead($leadData);
                
                if ($leadId) {
                    // Create call log entry
                    $callLogData = [
                        'lead_id' => $leadId,
                        'status' => $status,
                        'remarks' => $remarks,
                        'created_by' => $_SESSION['user_id'],
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $this->callLogModel->createCallLog($callLogData);
                    
                    // Set success message
                    setFlashMessage('success', 'Lead created successfully');
                    
                    // Redirect to leads page
                    redirect('leads');
                    exit;
                } else {
                    // Set error message
                    $errors[] = 'Failed to create lead';
                }
            }
        }
        
        // Load view
        include 'views/leads/create.php';
    }
    
    /**
     * Edit page
     */
    public function edit() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        // Check if ID is provided
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            setFlashMessage('error', 'Invalid lead ID');
            redirect('leads');
            exit;
        }
        
        // Get lead ID
        $leadId = (int)$_GET['id'];
        
        // Get lead
        $lead = $this->leadModel->getLeadById($leadId);
        
        // Check if lead exists
        if (!$lead) {
            setFlashMessage('error', 'Lead not found');
            redirect('leads');
            exit;
        }
        
        // Check if user has access to this lead
        if (!hasRole('administrator') && $lead['assigned_to'] != $_SESSION['user_id']) {
            setFlashMessage('error', 'You do not have permission to edit this lead');
            redirect('leads');
            exit;
        }
        
        // Get states
        $states = $this->stateModel->getActiveStates();
        
        // Get cities for selected state
        $cities = $this->cityModel->getCitiesByState($lead['state_id']);
        
        // Get employees (admin only)
        $employees = [];
        if (hasRole('administrator')) {
            $employees = $this->userModel->getEmployees();
        }
        
        // Get call logs
        $callLogs = $this->callLogModel->getCallLogsByLeadId($leadId);
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email']);
            $phone = sanitizeInput($_POST['phone']);
            $address = sanitizeInput($_POST['address']);
            $state_id = (int)$_POST['state_id'];
            $city_id = !empty($_POST['city_id']) ? (int)$_POST['city_id'] : null;
            $assigned_to = isset($_POST['assigned_to']) && !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
            
            // Validate input
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required';
            }
            
            if (empty($phone)) {
                $errors[] = 'Phone is required';
            }
            
            if (empty($state_id)) {
                $errors[] = 'State is required';
            }
            
            // If no errors, update lead
            if (empty($errors)) {
                // Prepare lead data
                $leadData = [
                    'id' => $leadId,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'state_id' => $state_id,
                    'city_id' => $city_id,
                    'assigned_to' => $assigned_to,
                    'updated_by' => $_SESSION['user_id'],
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Update lead
                $updated = $this->leadModel->updateLead($leadData);
                
                if ($updated) {
                    // Set success message
                    setFlashMessage('success', 'Lead updated successfully');
                    
                    // Redirect to leads page
                    redirect('leads');
                    exit;
                } else {
                    // Set error message
                    $errors[] = 'Failed to update lead';
                }
            }
        }
        
        // Load view
        include 'views/leads/edit.php';
    }
    
    /**
     * Delete lead
     */
    public function delete() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        // Check if user has admin role
        if (!hasRole('administrator')) {
            setFlashMessage('error', 'You do not have permission to delete leads');
            redirect('leads');
            exit;
        }
        
        // Check if ID is provided
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            setFlashMessage('error', 'Invalid lead ID');
            redirect('leads');
            exit;
        }
        
        // Get lead ID
        $leadId = (int)$_GET['id'];
        
        // Check if confirmation is provided
        if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
            // Delete lead
            $deleted = $this->leadModel->deleteLead($leadId);
            
            if ($deleted) {
                // Set success message
                setFlashMessage('success', 'Lead deleted successfully');
            } else {
                // Set error message
                setFlashMessage('error', 'Failed to delete lead');
            }
            
            // Redirect to leads page
            redirect('leads');
            exit;
        } else {
            // Get lead
            $lead = $this->leadModel->getLeadById($leadId);
            
            // Check if lead exists
            if (!$lead) {
                setFlashMessage('error', 'Lead not found');
                redirect('leads');
                exit;
            }
            
            // Load confirmation view
            include 'views/leads/delete_confirm.php';
        }
    }
    
    /**
     * Update lead status
     */
    public function updateStatus() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        // Check if ID is provided
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            setFlashMessage('error', 'Invalid lead ID');
            redirect('leads');
            exit;
        }
        
        // Get lead ID
        $leadId = (int)$_GET['id'];
        
        // Get lead
        $lead = $this->leadModel->getLeadById($leadId);
        
        // Check if lead exists
        if (!$lead) {
            setFlashMessage('error', 'Lead not found');
            redirect('leads');
            exit;
        }
        
        // Check if user has access to this lead
        if (!hasRole('administrator') && $lead['assigned_to'] != $_SESSION['user_id']) {
            setFlashMessage('error', 'You do not have permission to update this lead');
            redirect('leads');
            exit;
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $status = sanitizeInput($_POST['status']);
            $remarks = sanitizeInput($_POST['remarks']);
            $follow_up_date = null;
            $region = null;
            
            // Validate input
            $errors = [];
            
            if (empty($status)) {
                $errors[] = 'Status is required';
            }
            
            if (empty($remarks)) {
                $errors[] = 'Remarks are required';
            }
            
            // Status-specific validation
            if ($status === 'interested' && empty($_POST['follow_up_date'])) {
                $errors[] = 'Follow-up date is required for Interested status';
            } else if ($status === 'interested') {
                $follow_up_date = sanitizeInput($_POST['follow_up_date']);
            }
            
            // Region validation for Lost status
            if ($status === 'lost' && empty($_POST['region'])) {
                $errors[] = 'Region is required for Lost status';
            } else if ($status === 'lost') {
                $region = sanitizeInput($_POST['region']);
            }
            
            // Auto-follow-up for Not Attend and Wrong Number
            if ($status === 'not_attend' || $status === 'wrong_number') {
                // Set follow-up for next day at 10:00 AM
                $tomorrow = new DateTime('tomorrow');
                
                // If tomorrow is Sunday, set to Monday
                if ($tomorrow->format('w') == 0) { // 0 = Sunday
                    $tomorrow->modify('+1 day'); // Move to Monday
                }
                
                $follow_up_date = $tomorrow->format('Y-m-d 10:00:00');
            }
            
            // If no errors, update lead status
            if (empty($errors)) {
                // Prepare lead data
                $leadData = [
                    'id' => $leadId,
                    'status' => $status,
                    'region' => $region,
                    'follow_up_date' => $follow_up_date,
                    'updated_by' => $_SESSION['user_id'],
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Update lead
                $updated = $this->leadModel->updateLeadStatus($leadData);
                
                if ($updated) {
                    // Create call log entry
                    $callLogData = [
                        'lead_id' => $leadId,
                        'status' => $status,
                        'remarks' => $remarks,
                        'follow_up_date' => $follow_up_date,
                        'created_by' => $_SESSION['user_id'],
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $this->callLogModel->createCallLog($callLogData);
                    
                    // Set success message
                    setFlashMessage('success', 'Lead status updated successfully');
                    
                    // Redirect to leads page
                    redirect('leads');
                    exit;
                } else {
                    // Set error message
                    $errors[] = 'Failed to update lead status';
                }
            }
        }
        
        // Load view
        include 'views/leads/update_status.php';
    }
    
    /**
     * Import leads
     */
    public function import() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        // Check if user has admin role
        if (!hasRole('administrator')) {
            setFlashMessage('error', 'You do not have permission to import leads');
            redirect('leads');
            exit;
        }
        
        // Get states
        $states = $this->stateModel->getActiveStates();
        
        // Get employees
        $employees = $this->userModel->getEmployees();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if file is uploaded
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] != UPLOAD_ERR_OK) {
                setFlashMessage('error', 'Please select a CSV file to import');
                redirect('leads/import');
                exit;
            }
            
            // Get file info
            $file = $_FILES['csv_file'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];
            
            // Check file extension
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if ($fileExt != 'csv') {
                setFlashMessage('error', 'Only CSV files are allowed');
                redirect('leads/import');
                exit;
            }
            
            // Check file size (max 5MB)
            if ($fileSize > 5 * 1024 * 1024) {
                setFlashMessage('error', 'File size should be less than 5MB');
                redirect('leads/import');
                exit;
            }
            
            // Get state ID
            $stateId = isset($_POST['state_id']) ? (int)$_POST['state_id'] : null;
            
            // Get assigned to
            $assignedTo = isset($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
            
            // Open file
            $handle = fopen($fileTmpName, 'r');
            
            // Check if file is readable
            if (!$handle) {
                setFlashMessage('error', 'Unable to read the CSV file');
                redirect('leads/import');
                exit;
            }
            
            // Read header row
            $header = fgetcsv($handle);
            
            // Check required columns
            $requiredColumns = ['name', 'phone'];
            $missingColumns = [];
            
            foreach ($requiredColumns as $column) {
                if (!in_array($column, $header)) {
                    $missingColumns[] = $column;
                }
            }
            
            if (!empty($missingColumns)) {
                setFlashMessage('error', 'Missing required columns: ' . implode(', ', $missingColumns));
                redirect('leads/import');
                exit;
            }
            
            // Get column indexes
            $nameIndex = array_search('name', $header);
            $emailIndex = array_search('email', $header);
            $phoneIndex = array_search('phone', $header);
            $addressIndex = array_search('address', $header);
            $cityIndex = array_search('city', $header);
            
            // Initialize counters
            $totalRows = 0;
            $importedRows = 0;
            $skippedRows = 0;
            $errors = [];
            
            // Read data rows
            while (($row = fgetcsv($handle)) !== false) {
                $totalRows++;
                
                // Skip empty rows
                if (empty($row[$nameIndex]) || empty($row[$phoneIndex])) {
                    $skippedRows++;
                    continue;
                }
                
                // Get city ID if city name is provided
                $cityId = null;
                if ($cityIndex !== false && !empty($row[$cityIndex]) && !empty($stateId)) {
                    $cityName = $row[$cityIndex];
                    $city = $this->cityModel->getCityByNameAndState($cityName, $stateId);
                    
                    if ($city) {
                        $cityId = $city['id'];
                    }
                }
                
                // Prepare lead data
                $leadData = [
                    'name' => $row[$nameIndex],
                    'email' => $emailIndex !== false ? $row[$emailIndex] : null,
                    'phone' => $row[$phoneIndex],
                    'address' => $addressIndex !== false ? $row[$addressIndex] : null,
                    'state_id' => $stateId,
                    'city_id' => $cityId ?? null, // Ensure city_id is null if not found
                    'status' => 'new', // Default status for imported leads
                    'assigned_to' => $assignedTo,
                    'created_by' => $_SESSION['user_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // Create lead
                $leadId = $this->leadModel->createLead($leadData);
                
                if ($leadId) {
                    // Create call log entry
                    $callLogData = [
                        'lead_id' => $leadId,
                        'status' => 'new',
                        'remarks' => 'Imported from CSV',
                        'created_by' => $_SESSION['user_id'],
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $this->callLogModel->createCallLog($callLogData);
                    
                    $importedRows++;
                } else {
                    $skippedRows++;
                }
            }
            
            // Close file
            fclose($handle);
            
            // Set success message
            setFlashMessage('success', "Import completed: $importedRows leads imported, $skippedRows skipped out of $totalRows total rows");
            
            // Redirect to leads page
            redirect('leads');
            exit;
        }
        
        // Load view
        include 'views/leads/import.php';
    }
    
    /**
     * Export leads to CSV
     */
    public function export() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        // Get filter parameters
        $filters = [
            'state_id' => isset($_GET['state_id']) ? (int)$_GET['state_id'] : null,
            'city_id' => isset($_GET['city_id']) ? (int)$_GET['city_id'] : null,
            'status' => isset($_GET['status']) ? sanitizeInput($_GET['status']) : null,
            'assigned_to' => isset($_GET['assigned_to']) ? (int)$_GET['assigned_to'] : null,
            'date_from' => isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : null,
            'date_to' => isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : null,
            'search' => isset($_GET['search']) ? sanitizeInput($_GET['search']) : null
        ];
        
        // Get all leads for export (no pagination)
        $leads = $this->leadModel->getLeads($filters, false);
        
        if (empty($leads)) {
            setFlashMessage('error', 'No leads found to export');
            redirect('leads');
            exit;
        }
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="leads_export_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add header row
        fputcsv($output, [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Address',
            'State',
            'City',
            'Status',
            'Region',
            'Follow-up Date',
            'Remarks',
            'Assigned To',
            'Created At',
            'Updated At'
        ]);
        
        // Add data rows
        foreach ($leads as $lead) {
            // Format status for better readability
            $status = ucfirst(str_replace('_', ' ', $lead['status']));
            
            fputcsv($output, [
                $lead['id'],
                $lead['name'],
                $lead['email'],
                $lead['phone'],
                $lead['address'],
                $lead['state_name'],
                $lead['city_name'],
                $status,
                $lead['region'] ?? '',
                $lead['follow_up_date'] ?? '',
                $lead['remarks'] ?? '',
                $lead['assigned_to_name'],
                $lead['created_at'],
                $lead['updated_at']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
