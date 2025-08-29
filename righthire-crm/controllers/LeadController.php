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
     * Leads index page
     */
    public function index() {
        // Require login
        requireLogin();
        
        // Get filters from URL
        $filters = [
            'status' => isset($_GET['status']) ? sanitizeInput($_GET['status']) : '',
            'state_id' => isset($_GET['state_id']) ? (int)$_GET['state_id'] : '',
            'city_id' => isset($_GET['city_id']) ? (int)$_GET['city_id'] : '',
            'assigned_to' => isset($_GET['assigned_to']) ? (int)$_GET['assigned_to'] : '',
            'date_from' => isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : '',
            'date_to' => isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : '',
            'search' => isset($_GET['search']) ? sanitizeInput($_GET['search']) : ''
        ];
        
        // Get page number
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get leads with filters
        $leads = $this->leadModel->getAllWithRelations($filters, $page);
        $totalLeads = $this->leadModel->countFiltered($filters);
        $totalPages = ceil($totalLeads / RECORDS_PER_PAGE);
        
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
        
        // Set page title
        $pageTitle = 'Manage Leads';
        
        // Get current route
        $route = isset($_GET['route']) ? $_GET['route'] : 'leads';
        
        // Include view
        include 'views/leads/index.php';
    }
    
    /**
     * Create lead page
     */
    public function create() {
        // Require login
        requireLogin();
        
        // Get all active states
        if (hasRole('administrator')) {
            $states = $this->stateModel->getAllActive();
        } else {
            $states = $this->stateModel->getStatesForEmployee(getCurrentUserId());
        }
        
        // Get cities for selected state
        $cities = [];
        if (isset($_POST['state_id']) && !empty($_POST['state_id'])) {
            if (hasRole('administrator')) {
                $cities = $this->cityModel->getActiveByState($_POST['state_id']);
            } else {
                $cities = $this->cityModel->getCitiesForEmployee(getCurrentUserId(), $_POST['state_id']);
            }
        }
        
        // Get all active employees
        if (hasRole('administrator')) {
            $employees = $this->userModel->getAllActiveEmployees();
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email']);
            $phone = sanitizeInput($_POST['phone']);
            $address = sanitizeInput($_POST['address']);
            $stateId = (int)$_POST['state_id'];
            $cityId = (int)$_POST['city_id'];
            $assignedTo = isset($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
            $remarks = sanitizeInput($_POST['remarks']);
            
            // Validate input
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required';
            }
            
            if (empty($phone)) {
                $errors[] = 'Phone is required';
            }
            
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }
            
            if (empty($stateId)) {
                $errors[] = 'State is required';
            } elseif (!hasRole('administrator') && !$this->userModel->hasAccessToState(getCurrentUserId(), $stateId)) {
                $errors[] = 'You do not have access to this state';
            }
            
            if (empty($cityId)) {
                $errors[] = 'City is required';
            } elseif (!hasRole('administrator') && !$this->userModel->hasAccessToCity(getCurrentUserId(), $cityId)) {
                $errors[] = 'You do not have access to this city';
            }
            
            // If no errors, create lead
            if (empty($errors)) {
                $data = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'state_id' => $stateId,
                    'city_id' => $cityId,
                    'assigned_to' => $assignedTo,
                    'remarks' => $remarks,
                    'status' => 'new'
                ];
                
                $result = $this->leadModel->create($data);
                
                if ($result) {
                    setFlashMessage('success', 'Lead created successfully');
                    redirect('leads');
                    exit;
                } else {
                    $errors[] = 'Failed to create lead';
                }
            }
            
            // If we get here, there were errors
            $pageTitle = 'Create Lead';
            $route = 'leads/create';
            include 'views/leads/create.php';
        } else {
            // Display create form
            $pageTitle = 'Create Lead';
            $route = 'leads/create';
            include 'views/leads/create.php';
        }
    }
    
    /**
     * Edit lead page
     */
    public function edit() {
        // Require login
        requireLogin();
        
        // Get lead ID from URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            setFlashMessage('error', 'Invalid lead ID');
            redirect('leads');
            exit;
        }
        
        // Get lead
        $lead = $this->leadModel->getWithRelations($id);
        
        if (!$lead) {
            setFlashMessage('error', 'Lead not found');
            redirect('leads');
            exit;
        }
        
        // Check if user has access to this lead
        if (!hasRole('administrator')) {
            if (!$this->userModel->hasAccessToState(getCurrentUserId(), $lead['state_id']) ||
                !$this->userModel->hasAccessToCity(getCurrentUserId(), $lead['city_id'])) {
                setFlashMessage('error', 'You do not have access to this lead');
                redirect('leads');
                exit;
            }
        }
        
        // Get all active states
        if (hasRole('administrator')) {
            $states = $this->stateModel->getAllActive();
        } else {
            $states = $this->stateModel->getStatesForEmployee(getCurrentUserId());
        }
        
        // Get cities for selected state
        if (hasRole('administrator')) {
            $cities = $this->cityModel->getActiveByState($lead['state_id']);
        } else {
            $cities = $this->cityModel->getCitiesForEmployee(getCurrentUserId(), $lead['state_id']);
        }
        
        // Get all active employees
        if (hasRole('administrator')) {
            $employees = $this->userModel->getAllActiveEmployees();
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email']);
            $phone = sanitizeInput($_POST['phone']);
            $address = sanitizeInput($_POST['address']);
            $stateId = (int)$_POST['state_id'];
            $cityId = (int)$_POST['city_id'];
            $assignedTo = isset($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
            $remarks = sanitizeInput($_POST['remarks']);
            
            // Validate input
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required';
            }
            
            if (empty($phone)) {
                $errors[] = 'Phone is required';
            }
            
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }
            
            if (empty($stateId)) {
                $errors[] = 'State is required';
            } elseif (!hasRole('administrator') && !$this->userModel->hasAccessToState(getCurrentUserId(), $stateId)) {
                $errors[] = 'You do not have access to this state';
            }
            
            if (empty($cityId)) {
                $errors[] = 'City is required';
            } elseif (!hasRole('administrator') && !$this->userModel->hasAccessToCity(getCurrentUserId(), $cityId)) {
                $errors[] = 'You do not have access to this city';
            }
            
            // If no errors, update lead
            if (empty($errors)) {
                $data = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'state_id' => $stateId,
                    'city_id' => $cityId,
                    'remarks' => $remarks
                ];
                
                // Only administrators can assign leads
                if (hasRole('administrator')) {
                    $data['assigned_to'] = $assignedTo;
                }
                
                $result = $this->leadModel->update($id, $data);
                
                if ($result) {
                    setFlashMessage('success', 'Lead updated successfully');
                    redirect('leads');
                    exit;
                } else {
                    $errors[] = 'Failed to update lead';
                }
            }
            
            // If we get here, there were errors
            $pageTitle = 'Edit Lead';
            $route = 'leads/edit';
            include 'views/leads/edit.php';
        } else {
            // Display edit form
            $pageTitle = 'Edit Lead';
            $route = 'leads/edit';
            include 'views/leads/edit.php';
        }
    }
    
    /**
     * View lead page
     */
    public function view() {
        // Require login
        requireLogin();
        
        // Get lead ID from URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            setFlashMessage('error', 'Invalid lead ID');
            redirect('leads');
            exit;
        }
        
        // Get lead
        $lead = $this->leadModel->getWithRelations($id);
        
        if (!$lead) {
            setFlashMessage('error', 'Lead not found');
            redirect('leads');
            exit;
        }
        
        // Check if user has access to this lead
        if (!hasRole('administrator')) {
            if (!$this->userModel->hasAccessToState(getCurrentUserId(), $lead['state_id']) ||
                !$this->userModel->hasAccessToCity(getCurrentUserId(), $lead['city_id'])) {
                setFlashMessage('error', 'You do not have access to this lead');
                redirect('leads');
                exit;
            }
        }
        
        // Get call logs
        $callLogs = $this->callLogModel->getByLeadId($id);
        
        // Set page title
        $pageTitle = 'View Lead';
        
        // Get current route
        $route = 'leads/view';
        
        // Include view
        include 'views/leads/view.php';
    }
    
    /**
     * Delete lead
     */
    public function delete() {
        // Require login
        requireLogin();
        
        // Get lead ID from URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            setFlashMessage('error', 'Invalid lead ID');
            redirect('leads');
            exit;
        }
        
        // Get lead
        $lead = $this->leadModel->find($id);
        
        if (!$lead) {
            setFlashMessage('error', 'Lead not found');
            redirect('leads');
            exit;
        }
        
        // Check if user has access to this lead
        if (!hasRole('administrator')) {
            if (!$this->userModel->hasAccessToState(getCurrentUserId(), $lead['state_id']) ||
                !$this->userModel->hasAccessToCity(getCurrentUserId(), $lead['city_id'])) {
                setFlashMessage('error', 'You do not have access to this lead');
                redirect('leads');
                exit;
            }
        }
        
        // Delete lead
        $result = $this->leadModel->delete($id);
        
        if ($result) {
            setFlashMessage('success', 'Lead deleted successfully');
        } else {
            setFlashMessage('error', 'Failed to delete lead');
        }
        
        redirect('leads');
        exit;
    }
    
    /**
     * Update lead status page
     */
    public function updateStatus() {
        // Require login
        requireLogin();
        
        // Get lead ID from URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            setFlashMessage('error', 'Invalid lead ID');
            redirect('leads');
            exit;
        }
        
        // Get lead
        $lead = $this->leadModel->getWithRelations($id);
        
        if (!$lead) {
            setFlashMessage('error', 'Lead not found');
            redirect('leads');
            exit;
        }
        
        // Check if user has access to this lead
        if (!hasRole('administrator')) {
            if (!$this->userModel->hasAccessToState(getCurrentUserId(), $lead['state_id']) ||
                !$this->userModel->hasAccessToCity(getCurrentUserId(), $lead['city_id'])) {
                setFlashMessage('error', 'You do not have access to this lead');
                redirect('leads');
                exit;
            }
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $status = sanitizeInput($_POST['status']);
            $otherReason = isset($_POST['other_reason']) ? sanitizeInput($_POST['other_reason']) : null;
            $followUpDate = isset($_POST['follow_up_date']) ? sanitizeInput($_POST['follow_up_date']) : null;
            $remarks = sanitizeInput($_POST['remarks']);
            
            // Validate input
            $errors = [];
            
            if (empty($status)) {
                $errors[] = 'Status is required';
            }
            
            if ($status === 'other' && empty($otherReason)) {
                $errors[] = 'Other reason is required';
            }
            
            if ($status === 'follow_up' && empty($followUpDate)) {
                $errors[] = 'Follow-up date is required';
            }
            
            // If no errors, update lead status
            if (empty($errors)) {
                $result = $this->leadModel->updateStatus($id, $status, $otherReason, $followUpDate, $remarks);
                
                if ($result) {
                    setFlashMessage('success', 'Lead status updated successfully');
                    redirect('leads/view?id=' . $id);
                    exit;
                } else {
                    $errors[] = 'Failed to update lead status';
                }
            }
            
            // If we get here, there were errors
            $pageTitle = 'Update Lead Status';
            $route = 'leads/update-status';
            include 'views/leads/update_status.php';
        } else {
            // Display update status form
            $pageTitle = 'Update Lead Status';
            $route = 'leads/update-status';
            include 'views/leads/update_status.php';
        }
    }
    
    /**
     * Import leads page
     */
    public function import() {
        // Require admin
        requireAdmin();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if file is uploaded
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                setFlashMessage('error', 'Please select a CSV file to upload');
                redirect('leads/import');
                exit;
            }
            
            // Check file size
            if ($_FILES['csv_file']['size'] > MAX_UPLOAD_SIZE) {
                setFlashMessage('error', 'File size exceeds the maximum limit of ' . (MAX_UPLOAD_SIZE / 1024 / 1024) . 'MB');
                redirect('leads/import');
                exit;
            }
            
            // Check file extension
            $fileExtension = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
            if ($fileExtension !== 'csv') {
                setFlashMessage('error', 'Only CSV files are allowed');
                redirect('leads/import');
                exit;
            }
            
            // Read CSV file
            $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
            $headers = fgetcsv($file);
            $data = [];
            
            // Get column mapping
            $mapping = $_POST['mapping'];
            
            // Validate mapping
            $requiredFields = ['name', 'phone', 'state_id', 'city_id'];
            foreach ($requiredFields as $field) {
                if (!isset($mapping[$field]) || $mapping[$field] === '') {
                    setFlashMessage('error', 'Mapping for ' . $field . ' is required');
                    redirect('leads/import');
                    exit;
                }
            }
            
            // Process CSV data
            $rowCount = 0;
            $errors = [];
            
            while (($row = fgetcsv($file)) !== false) {
                $rowCount++;
                
                // Skip empty rows
                if (count(array_filter($row)) === 0) {
                    continue;
                }
                
                // Map columns
                $leadData = [
                    'name' => isset($row[$mapping['name']]) ? $row[$mapping['name']] : '',
                    'email' => isset($mapping['email']) && isset($row[$mapping['email']]) ? $row[$mapping['email']] : '',
                    'phone' => isset($row[$mapping['phone']]) ? $row[$mapping['phone']] : '',
                    'address' => isset($mapping['address']) && isset($row[$mapping['address']]) ? $row[$mapping['address']] : '',
                    'state_id' => isset($row[$mapping['state_id']]) ? (int)$row[$mapping['state_id']] : 0,
                    'city_id' => isset($row[$mapping['city_id']]) ? (int)$row[$mapping['city_id']] : 0,
                    'remarks' => isset($mapping['remarks']) && isset($row[$mapping['remarks']]) ? $row[$mapping['remarks']] : ''
                ];
                
                // Validate data
                if (empty($leadData['name'])) {
                    $errors[] = 'Row ' . $rowCount . ': Name is required';
                    continue;
                }
                
                if (empty($leadData['phone'])) {
                    $errors[] = 'Row ' . $rowCount . ': Phone is required';
                    continue;
                }
                
                if (empty($leadData['state_id'])) {
                    $errors[] = 'Row ' . $rowCount . ': State ID is required';
                    continue;
                }
                
                if (empty($leadData['city_id'])) {
                    $errors[] = 'Row ' . $rowCount . ': City ID is required';
                    continue;
                }
                
                // Check if state exists
                $state = $this->stateModel->find($leadData['state_id']);
                if (!$state) {
                    $errors[] = 'Row ' . $rowCount . ': State ID ' . $leadData['state_id'] . ' does not exist';
                    continue;
                }
                
                // Check if city exists
                $city = $this->cityModel->find($leadData['city_id']);
                if (!$city) {
                    $errors[] = 'Row ' . $rowCount . ': City ID ' . $leadData['city_id'] . ' does not exist';
                    continue;
                }
                
                // Check if city belongs to state
                if ($city['state_id'] != $leadData['state_id']) {
                    $errors[] = 'Row ' . $rowCount . ': City ID ' . $leadData['city_id'] . ' does not belong to State ID ' . $leadData['state_id'];
                    continue;
                }
                
                // Add to data array
                $data[] = $leadData;
            }
            
            fclose($file);
            
            // Check if there are any errors
            if (!empty($errors)) {
                setFlashMessage('error', implode('<br>', $errors));
                redirect('leads/import');
                exit;
            }
            
            // Check if there is any data
            if (empty($data)) {
                setFlashMessage('error', 'No valid data found in the CSV file');
                redirect('leads/import');
                exit;
            }
            
            // Import leads
            try {
                $count = $this->leadModel->importFromCSV($data);
                setFlashMessage('success', $count . ' leads imported successfully');
                redirect('leads');
                exit;
            } catch (Exception $e) {
                setFlashMessage('error', 'Failed to import leads: ' . $e->getMessage());
                redirect('leads/import');
                exit;
            }
        } else {
            // Display import form
            $pageTitle = 'Import Leads';
            $route = 'leads/import';
            include 'views/leads/import.php';
        }
    }
    
    /**
     * Export leads
     */
    public function export() {
        // Require login
        requireLogin();
        
        // Get filters from URL
        $filters = [
            'status' => isset($_GET['status']) ? sanitizeInput($_GET['status']) : '',
            'state_id' => isset($_GET['state_id']) ? (int)$_GET['state_id'] : '',
            'city_id' => isset($_GET['city_id']) ? (int)$_GET['city_id'] : '',
            'assigned_to' => isset($_GET['assigned_to']) ? (int)$_GET['assigned_to'] : '',
            'date_from' => isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : '',
            'date_to' => isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : '',
            'search' => isset($_GET['search']) ? sanitizeInput($_GET['search']) : ''
        ];
        
        // Export leads to CSV
        $result = $this->leadModel->exportToCSV($filters);
        
        // Set filename
        $filename = 'leads_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Export to CSV
        exportToCSV($result['data'], $result['headers'], $filename);
    }
}

