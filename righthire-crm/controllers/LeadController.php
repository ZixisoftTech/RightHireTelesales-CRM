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
        $this->db = Database::getInstance();
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
        
        // For employees with territories, ensure there's at least one test lead in their territory
        if (!hasRole('administrator')) {
            // Get user's territories
            $userTerritories = $this->db->getRows(
                "SELECT state_id, city_id FROM employee_territories 
                WHERE user_id = ? AND deleted_at IS NULL", 
                [getCurrentUserId()]
            );
            
            // Create test leads for each territory if none exist
            foreach ($userTerritories as $territory) {
                if (!empty($territory['state_id']) && !empty($territory['city_id'])) {
                    $this->leadModel->createTestLeadIfNoneExists(
                        $territory['state_id'], 
                        $territory['city_id'], 
                        getCurrentUserId()
                    );
                }
            }
        }
        
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
                    'created_by' => $_SESSION['user_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // If employee is creating the lead, assign it to themselves
                if (!hasRole('administrator') && empty($assigned_to)) {
                    $assigned_to = $_SESSION['user_id'];
                }
                
                // Set the assigned_to field
                $leadData['assigned_to'] = $assigned_to;
                
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
     * View lead details
     */
    public function view() {
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
        if (!hasRole('administrator')) {
            // Get user's territories
            $userTerritories = $this->db->getRows(
                "SELECT state_id, city_id FROM employee_territories 
                WHERE user_id = ? AND deleted_at IS NULL", 
                [$_SESSION['user_id']]
            );
            
            // Check if lead is in user's territory
            $hasAccess = false;
            
            // First check if lead is assigned to the user
            if ($lead['assigned_to'] == $_SESSION['user_id']) {
                $hasAccess = true;
            } else {
                // Check if lead is in user's territory
                foreach ($userTerritories as $territory) {
                    if ($territory['state_id'] == $lead['state_id']) {
                        // If territory is state-wide (no city specified) or city matches
                        if ($territory['city_id'] === null || $territory['city_id'] == $lead['city_id']) {
                            $hasAccess = true;
                            break;
                        }
                    }
                }
            }
            
            if (!$hasAccess) {
                setFlashMessage('error', 'You do not have permission to view this lead');
                redirect('leads');
                exit;
            }
        }
        
        // Get call logs
        $callLogs = $this->callLogModel->getCallLogsByLeadId($leadId);
        
        // Load view
        include 'views/leads/view.php';
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
                $updated = $this->leadModel->updateLead($leadId, $leadData);
                
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
        
        // Get lead with related data
        $lead = $this->leadModel->getLeadById($leadId);
        
        // Check if lead exists
        if (!$lead) {
            setFlashMessage('error', 'Lead not found');
            redirect('leads');
            exit;
        }
        
        // Get call logs for this lead
        $callLogs = $this->callLogModel->getByLeadId($leadId);
        $callLogCount = count($callLogs);
        
        // Check if confirmation is provided
        if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
            // HARD DELETE lead and all associated data
            $this->db->beginTransaction();
            
            try {
                // Delete call logs for this lead
                $this->db->query("DELETE FROM call_logs WHERE lead_id = ?", [$leadId]);
                
                // Delete audit logs for call logs
                $this->db->query("DELETE al FROM audit_logs al 
                                 WHERE al.table_name = 'call_logs' 
                                 AND al.record_id IN (SELECT id FROM call_logs WHERE lead_id = ?)", [$leadId]);
                
                // Delete audit logs for this lead
                $this->db->query("DELETE FROM audit_logs WHERE table_name = 'leads' AND record_id = ?", [$leadId]);
                
                // Hard delete the lead
                $deleted = $this->leadModel->hardDelete($leadId);
                
                $this->db->commit();
                
                // Set success message
                setFlashMessage('success', 'Lead and all associated data permanently deleted');
            } catch (Exception $e) {
                $this->db->rollback();
                setFlashMessage('error', 'Failed to delete lead: ' . $e->getMessage());
            }
            
            // Redirect to leads page
            redirect('leads');
            exit;
        } else {
            // Load confirmation view with call log information
            include __DIR__ . '/../views/leads/delete_confirm.php';
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
        if (!hasRole('administrator')) {
            // Get user's territories
            $userTerritories = $this->db->getRows(
                "SELECT state_id, city_id FROM employee_territories 
                WHERE user_id = ? AND deleted_at IS NULL", 
                [$_SESSION['user_id']]
            );
            
            // Check if lead is in user's territory
            $hasAccess = false;
            
            // First check if lead is assigned to the user
            if ($lead['assigned_to'] == $_SESSION['user_id']) {
                $hasAccess = true;
            } else {
                // Check if lead is in user's territory
                foreach ($userTerritories as $territory) {
                    if ($territory['state_id'] == $lead['state_id']) {
                        // If territory is state-wide (no city specified) or city matches
                        if ($territory['city_id'] === null || $territory['city_id'] == $lead['city_id']) {
                            $hasAccess = true;
                            break;
                        }
                    }
                }
            }
            
            if (!$hasAccess) {
                setFlashMessage('error', 'You do not have permission to update this lead');
                redirect('leads');
                exit;
            }
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
                // Update lead status
                $updated = $this->leadModel->updateStatus(
                    $leadId, 
                    $status, 
                    $remarks, 
                    $follow_up_date, 
                    isset($region) ? $region : null
                );
                
                if ($updated) {
                    // Create call log entry with follow-up date for all relevant statuses
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
            $assignedTo = isset($_POST['assigned_to']) && !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
            
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
                    'city_id' => $cityId, // Will be handled in the Lead model if null
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
    /**
     * Download sample import file
     */
    public function downloadSample() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="leads_import_sample.xlsx"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Start HTML output for Excel
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Sample</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        echo '</head>';
        echo '<body>';
        echo '<table border="1">';
        
        // Add header row
        echo '<tr style="font-weight: bold; background-color: #f0f0f0;">';
        echo '<td>name</td>';
        echo '<td>email</td>';
        echo '<td>phone</td>';
        echo '<td>address</td>';
        echo '<td>city</td>';
        echo '</tr>';
        
        // Add sample data rows
        echo '<tr>';
        echo '<td>John Doe</td>';
        echo '<td>john.doe@example.com</td>';
        echo '<td>1234567890</td>';
        echo '<td>123 Main St</td>';
        echo '<td>New York</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<td>Jane Smith</td>';
        echo '<td>jane.smith@example.com</td>';
        echo '<td>9876543210</td>';
        echo '<td>456 Oak Ave</td>';
        echo '<td>Los Angeles</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<td>Michael Johnson</td>';
        echo '<td>michael.j@example.com</td>';
        echo '<td>5551234567</td>';
        echo '<td>789 Pine Rd</td>';
        echo '<td>Chicago</td>';
        echo '</tr>';
        
        echo '</table>';
        echo '</body>';
        echo '</html>';
        
        exit;
    }
    
    /**
     * Export leads
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
        
        // Determine export format (Excel or CSV)
        $format = isset($_GET['format']) ? strtolower($_GET['format']) : 'excel';
        
        if ($format === 'excel') {
            // Set headers for Excel download
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="leads_export_' . date('Y-m-d') . '.xls"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Start HTML output for Excel
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head>';
            echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
            echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Leads</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
            echo '</head>';
            echo '<body>';
            echo '<table border="1">';
            
            // Add header row
            echo '<tr style="font-weight: bold; background-color: #f0f0f0;">';
            echo '<td>ID</td>';
            echo '<td>Name</td>';
            echo '<td>Email</td>';
            echo '<td>Phone</td>';
            echo '<td>Address</td>';
            echo '<td>State</td>';
            echo '<td>City</td>';
            echo '<td>Status</td>';
            echo '<td>Region</td>';
            echo '<td>Follow-up Date</td>';
            echo '<td>Remarks</td>';
            echo '<td>Assigned To</td>';
            echo '<td>Created At</td>';
            echo '<td>Updated At</td>';
            echo '</tr>';
            
            // Add data rows
            foreach ($leads as $lead) {
                // Format status for better readability
                $status = ucfirst(str_replace('_', ' ', $lead['status']));
                
                echo '<tr>';
                echo '<td>' . htmlspecialchars($lead['id']) . '</td>';
                echo '<td>' . htmlspecialchars($lead['name']) . '</td>';
                echo '<td>' . htmlspecialchars($lead['email'] ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($lead['phone']) . '</td>';
                echo '<td>' . htmlspecialchars($lead['address'] ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($lead['state_name'] ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($lead['city_name'] ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($status) . '</td>';
                echo '<td>' . htmlspecialchars($lead['region'] ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($lead['follow_up_date'] ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($lead['remarks'] ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($lead['assigned_to_name'] ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($lead['created_at']) . '</td>';
                echo '<td>' . htmlspecialchars($lead['updated_at'] ?? '') . '</td>';
                echo '</tr>';
            }
            
            echo '</table>';
            echo '</body>';
            echo '</html>';
        } else {
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
                    $lead['email'] ?? '',
                    $lead['phone'],
                    $lead['address'] ?? '',
                    $lead['state_name'] ?? '',
                    $lead['city_name'] ?? '',
                    $status,
                    $lead['region'] ?? '',
                    $lead['follow_up_date'] ?? '',
                    $lead['remarks'] ?? '',
                    $lead['assigned_to_name'] ?? '',
                    $lead['created_at'],
                    $lead['updated_at'] ?? ''
                ]);
            }
            
            fclose($output);
        }
        
        exit;
    }
}
