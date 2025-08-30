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
        
        // Get page number
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get leads based on user role and filters
        $leads = $this->leadModel->getLeads($filters, true, $page);
        
        // Get total count for pagination
        $totalCount = $this->leadModel->countLeads($filters);
        $totalPages = ceil($totalCount / RECORDS_PER_PAGE);
        
        // Get states for filter
        $states = $this->stateModel->getActiveStates();
        
        // Get cities if state is selected
        $cities = [];
        
        // Pass pagination variables to view
        if (!empty($filters['state_id'])) {
            $cities = $this->cityModel->getCitiesByState($filters['state_id']);
        }
        
        // Get employees for filter (admin only)
        $employees = [];
        if (hasRole('administrator')) {
            $employees = $this->userModel->getEmployees();
        }
        
        // Pass pagination variables to view
        $data = [
            'leads' => $leads,
            'states' => $states,
            'cities' => $cities,
            'employees' => $employees,
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages
        ];
        
        // Include view
        extract($data);
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
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'new';
            $other_reason = isset($_POST['other_reason']) ? sanitizeInput($_POST['other_reason']) : null;
            $follow_up_date = isset($_POST['follow_up_date']) ? sanitizeInput($_POST['follow_up_date']) : null;
            $remarks = sanitizeInput($_POST['remarks']);
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
            
            if (empty($status)) {
                $errors[] = 'Status is required';
            }
            
            if ($status === 'follow_up' && empty($follow_up_date)) {
                $errors[] = 'Follow-up date is required for follow-up status';
            }
            
            if ($status === 'other' && empty($other_reason)) {
                $errors[] = 'Other reason is required for other status';
            }
            
            // If no errors, create lead
            if (empty($errors)) {
                try {
                    // Create lead
                    $leadData = [
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'address' => $address,
                        'state_id' => $state_id,
                        'city_id' => $city_id,
                        'status' => $status,
                        'other_reason' => $other_reason,
                        'follow_up_date' => $follow_up_date,
                        'remarks' => $remarks,
                        'assigned_to' => $assigned_to
                    ];
                    
                    $leadId = $this->leadModel->createLead($leadData);
                    
                    if ($leadId) {
                        // Create call log
                        $callLogData = [
                            'lead_id' => $leadId,
                            'status' => $status,
                            'other_reason' => $other_reason,
                            'follow_up_date' => $follow_up_date,
                            'remarks' => $remarks
                        ];
                        
                        $this->callLogModel->createCallLog($callLogData);
                        
                        setFlashMessage('success', 'Lead created successfully');
                        redirect('leads');
                        exit;
                    } else {
                        $errors[] = 'Failed to create lead';
                    }
                } catch (PDOException $e) {
                    // Handle database errors
                    if ($e->getCode() == '23000') {
                        // Foreign key constraint error
                        if (strpos($e->getMessage(), 'leads_assigned_to_fk') !== false) {
                            $errors[] = 'The selected employee does not exist or has been deleted';
                        } else if (strpos($e->getMessage(), 'leads_state_id_fk') !== false) {
                            $errors[] = 'The selected state does not exist or has been deleted';
                        } else if (strpos($e->getMessage(), 'leads_city_id_fk') !== false) {
                            $errors[] = 'The selected city does not exist or has been deleted';
                        } else {
                            $errors[] = 'A database constraint error occurred: ' . $e->getMessage();
                        }
                    } else {
                        $errors[] = 'Database error: ' . $e->getMessage();
                    }
                }
            }
        }
        
        // Include view
        include 'views/leads/create.php';
    }
    
    /**
     * View page
     */
    public function view() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        // Check if ID is provided
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            setFlashMessage('error', 'Lead ID is required');
            redirect('leads');
            exit;
        }
        
        $id = (int)$_GET['id'];
        
        // Get lead
        $lead = $this->leadModel->getLeadById($id);
        
        if (!$lead) {
            setFlashMessage('error', 'Lead not found');
            redirect('leads');
            exit;
        }
        
        // Check if user has access to this lead
        if (!hasRole('administrator') && $lead['assigned_to'] != $_SESSION['user_id']) {
            // Check if lead is in user's territory
            $hasAccess = $this->leadModel->checkLeadAccess($id, $_SESSION['user_id']);
            
            if (!$hasAccess) {
                setFlashMessage('error', 'You do not have access to this lead');
                redirect('leads');
                exit;
            }
        }
        
        // Get call logs
        $callLogs = $this->callLogModel->getCallLogsByLeadId($id);
        
        // Include view
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
            setFlashMessage('error', 'Lead ID is required');
            redirect('leads');
            exit;
        }
        
        $id = (int)$_GET['id'];
        
        // Get lead
        $lead = $this->leadModel->getLeadById($id);
        
        if (!$lead) {
            setFlashMessage('error', 'Lead not found');
            redirect('leads');
            exit;
        }
        
        // Check if user has access to this lead
        if (!hasRole('administrator') && $lead['assigned_to'] != $_SESSION['user_id']) {
            // Check if lead is in user's territory
            $hasAccess = $this->leadModel->checkLeadAccess($id, $_SESSION['user_id']);
            
            if (!$hasAccess) {
                setFlashMessage('error', 'You do not have access to this lead');
                redirect('leads');
                exit;
            }
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
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email']);
            $phone = sanitizeInput($_POST['phone']);
            $address = sanitizeInput($_POST['address']);
            $state_id = (int)$_POST['state_id'];
            $city_id = !empty($_POST['city_id']) ? (int)$_POST['city_id'] : null;
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'new';
            $other_reason = isset($_POST['other_reason']) ? sanitizeInput($_POST['other_reason']) : null;
            $follow_up_date = isset($_POST['follow_up_date']) ? sanitizeInput($_POST['follow_up_date']) : null;
            $remarks = sanitizeInput($_POST['remarks']);
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
                try {
                    // Update lead
                    $leadData = [
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'address' => $address,
                        'state_id' => $state_id,
                        'city_id' => $city_id,
                        'remarks' => $remarks
                    ];
                    
                    // Only admin can update assigned_to
                    if (hasRole('administrator')) {
                        $leadData['assigned_to'] = $assigned_to;
                    }
                    
                    $result = $this->leadModel->updateLead($id, $leadData);
                    
                    if ($result) {
                        setFlashMessage('success', 'Lead updated successfully');
                        redirect('leads/view?id=' . $id);
                        exit;
                    } else {
                        $errors[] = 'Failed to update lead';
                    }
                } catch (PDOException $e) {
                    // Handle database errors
                    if ($e->getCode() == '23000') {
                        // Foreign key constraint error
                        if (strpos($e->getMessage(), 'leads_assigned_to_fk') !== false) {
                            $errors[] = 'The selected employee does not exist or has been deleted';
                        } else if (strpos($e->getMessage(), 'leads_state_id_fk') !== false) {
                            $errors[] = 'The selected state does not exist or has been deleted';
                        } else if (strpos($e->getMessage(), 'leads_city_id_fk') !== false) {
                            $errors[] = 'The selected city does not exist or has been deleted';
                        } else {
                            $errors[] = 'A database constraint error occurred: ' . $e->getMessage();
                        }
                    } else {
                        $errors[] = 'Database error: ' . $e->getMessage();
                    }
                }
            }
            
            // If there are errors, get cities for the selected state
            if (!empty($errors) && !empty($state_id)) {
                $cities = $this->cityModel->getCitiesByState($state_id);
            }
        }
        
        // Include view
        include 'views/leads/edit.php';
    }
    
    /**
     * Update status page
     */
    public function updateStatus() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        // Check if ID is provided
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            setFlashMessage('error', 'Lead ID is required');
            redirect('leads');
            exit;
        }
        
        $id = (int)$_GET['id'];
        
        // Get lead
        $lead = $this->leadModel->getLeadById($id);
        
        if (!$lead) {
            setFlashMessage('error', 'Lead not found');
            redirect('leads');
            exit;
        }
        
        // Check if user has access to this lead
        if (!hasRole('administrator') && $lead['assigned_to'] != $_SESSION['user_id']) {
            // Check if lead is in user's territory
            $hasAccess = $this->leadModel->checkLeadAccess($id, $_SESSION['user_id']);
            
            if (!$hasAccess) {
                setFlashMessage('error', 'You do not have access to this lead');
                redirect('leads');
                exit;
            }
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'new';
            $other_reason = isset($_POST['other_reason']) ? sanitizeInput($_POST['other_reason']) : null;
            $follow_up_date = isset($_POST['follow_up_date']) ? sanitizeInput($_POST['follow_up_date']) : null;
            $remarks = sanitizeInput($_POST['remarks']);
            
            // Validate input
            $errors = [];
            
            if (empty($status)) {
                $errors[] = 'Status is required';
            }
            
            if ($status === 'follow_up' && empty($follow_up_date)) {
                $errors[] = 'Follow-up date is required for follow-up status';
            }
            
            if ($status === 'other' && empty($other_reason)) {
                $errors[] = 'Other reason is required for other status';
            }
            
            // If no errors, update lead status
            if (empty($errors)) {
                try {
                    // Start transaction
                    $db = Database::getInstance();
                    $db->beginTransaction();
                    
                    // Update lead status
                    $leadData = [
                        'status' => $status,
                        'other_reason' => $other_reason,
                        'follow_up_date' => $follow_up_date
                    ];
                    
                    $result = $this->leadModel->updateLead($id, $leadData);
                    
                    if ($result) {
                        // Create call log
                        $callLogData = [
                            'lead_id' => $id,
                            'status' => $status,
                            'other_reason' => $other_reason,
                            'follow_up_date' => $follow_up_date,
                            'remarks' => $remarks
                        ];
                        
                        $this->callLogModel->createCallLog($callLogData);
                        
                        // Commit transaction
                        $db->commit();
                        
                        setFlashMessage('success', 'Lead status updated successfully');
                        redirect('leads/view?id=' . $id);
                        exit;
                    } else {
                        // Rollback transaction
                        $db->rollBack();
                        
                        $errors[] = 'Failed to update lead status';
                    }
                } catch (Exception $e) {
                    // Rollback transaction
                    $db->rollBack();
                    
                    $errors[] = 'Error: ' . $e->getMessage();
                }
            }
        }
        
        // Include view
        include 'views/leads/update_status.php';
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
        
        // Check if user is admin
        if (!hasRole('administrator')) {
            setFlashMessage('error', 'You do not have permission to delete leads');
            redirect('leads');
            exit;
        }
        
        // Check if ID is provided
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            setFlashMessage('error', 'Lead ID is required');
            redirect('leads');
            exit;
        }
        
        $id = (int)$_GET['id'];
        
        // Delete lead
        $result = $this->leadModel->deleteLead($id);
        
        if ($result) {
            setFlashMessage('success', 'Lead deleted successfully');
        } else {
            setFlashMessage('error', 'Failed to delete lead');
        }
        
        redirect('leads');
        exit;
    }
    
    /**
     * Import page
     */
    public function import() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        // Check if user is admin
        if (!hasRole('administrator')) {
            setFlashMessage('error', 'You do not have permission to import leads');
            redirect('leads');
            exit;
        }
        
        // Get states
        $states = $this->stateModel->getActiveStates();
        
        // Get cities if state is selected
        $cities = [];
        if (isset($_POST['state_id']) && !empty($_POST['state_id'])) {
            $cities = $this->cityModel->getCitiesByState($_POST['state_id']);
        }
        
        // Get employees
        $employees = $this->userModel->getEmployees();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if file is uploaded
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                setFlashMessage('error', 'Please select a CSV file to import');
                redirect('leads/import');
                exit;
            }
            
            // Get state and city IDs
            $state_id = isset($_POST['state_id']) ? (int)$_POST['state_id'] : null;
            $city_id = isset($_POST['city_id']) && !empty($_POST['city_id']) ? (int)$_POST['city_id'] : null;
            $assigned_to = isset($_POST['assigned_to']) && !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
            
            // Validate state
            if (empty($state_id)) {
                setFlashMessage('error', 'Please select a state');
                redirect('leads/import');
                exit;
            }
            
            // Process CSV file
            $file = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($file, 'r');
            
            if ($handle !== false) {
                // Get header row
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
                
                // Process data rows
                $importCount = 0;
                $errorCount = 0;
                $errors = [];
                
                try {
                    // Start transaction
                    $db = Database::getInstance();
                    $db->beginTransaction();
                    
                    while (($data = fgetcsv($handle)) !== false) {
                        // Map data to columns
                        $rowData = [];
                        foreach ($header as $index => $column) {
                            $rowData[$column] = isset($data[$index]) ? $data[$index] : '';
                        }
                        
                        // Validate required fields
                        if (empty($rowData['name']) || empty($rowData['phone'])) {
                            $errorCount++;
                            continue;
                        }
                        
                        // Prepare lead data
                        $leadData = [
                            'name' => $rowData['name'],
                            'email' => isset($rowData['email']) ? $rowData['email'] : null,
                            'phone' => $rowData['phone'],
                            'address' => isset($rowData['address']) ? $rowData['address'] : null,
                            'state_id' => $state_id,
                            'city_id' => $city_id,
                            'status' => 'new',
                            'assigned_to' => $assigned_to
                        ];
                        
                        // Create lead
                        $leadId = $this->leadModel->createLead($leadData);
                        
                        if ($leadId) {
                            // Create call log
                            $callLogData = [
                                'lead_id' => $leadId,
                                'status' => 'new',
                                'remarks' => 'Imported from CSV'
                            ];
                            
                            $this->callLogModel->createCallLog($callLogData);
                            
                            $importCount++;
                        } else {
                            $errorCount++;
                        }
                    }
                    
                    // Commit transaction
                    $db->commit();
                    
                    fclose($handle);
                    
                    if ($importCount > 0) {
                        setFlashMessage('success', $importCount . ' leads imported successfully' . ($errorCount > 0 ? ' (' . $errorCount . ' errors)' : ''));
                        redirect('leads');
                        exit;
                    } else {
                        setFlashMessage('error', 'No leads imported' . ($errorCount > 0 ? ' (' . $errorCount . ' errors)' : ''));
                        redirect('leads/import');
                        exit;
                    }
                } catch (Exception $e) {
                    // Rollback transaction
                    $db->rollBack();
                    
                    setFlashMessage('error', 'Error importing leads: ' . $e->getMessage());
                    redirect('leads/import');
                    exit;
                }
            } else {
                setFlashMessage('error', 'Failed to open CSV file');
                redirect('leads/import');
                exit;
            }
        }
        
        // Include view
        include 'views/leads/import.php';
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
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="leads_export_' . date('Y-m-d') . '.csv"');
        
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
            'Other Reason',
            'Follow-up Date',
            'Assigned To',
            'Created At',
            'Updated At'
        ]);
        
        // Add data rows
        foreach ($leads as $lead) {
            fputcsv($output, [
                $lead['id'],
                $lead['name'],
                $lead['email'],
                $lead['phone'],
                $lead['address'],
                $lead['state_name'],
                $lead['city_name'],
                $lead['status'],
                $lead['other_reason'],
                $lead['follow_up_date'],
                $lead['assigned_to_name'],
                $lead['created_at'],
                $lead['updated_at']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
