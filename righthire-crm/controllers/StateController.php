<?php
/**
 * State Controller
 * 
 * This controller handles all state-related actions.
 */

require_once 'models/State.php';
require_once 'models/City.php';
require_once 'config/database.php';

class StateController {
    private $stateModel;
    private $cityModel;
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->stateModel = new State();
        $this->cityModel = new City();
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
        
        // Check if user is admin
        if (!hasRole('administrator')) {
            setFlashMessage('error', 'You do not have permission to access this page');
            redirect('dashboard');
            exit;
        }
        
        // Get page number
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get states with city count
        $states = $this->stateModel->getAllWithCityCount($page);
        
        // Get total count for pagination
        $totalCount = $this->stateModel->count();
        $totalPages = ceil($totalCount / RECORDS_PER_PAGE);
        
        // Include view
        include 'views/states/index.php';
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
        
        // Check if user is admin
        if (!hasRole('administrator')) {
            setFlashMessage('error', 'You do not have permission to access this page');
            redirect('dashboard');
            exit;
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $name = sanitizeInput($_POST['name']);
            
            // Validate input
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required';
            } elseif ($this->stateModel->nameExists($name)) {
                $errors[] = 'State name already exists';
            }
            
            // If no errors, create state
            if (empty($errors)) {
                $data = [
                    'name' => $name,
                    'status' => 1,
                    'created_by' => $_SESSION['user_id']
                ];
                
                $result = $this->stateModel->create($data);
                
                if ($result) {
                    setFlashMessage('success', 'State created successfully');
                    redirect('states');
                    exit;
                } else {
                    $errors[] = 'Failed to create state';
                }
            }
        }
        
        // Include view
        include 'views/states/create.php';
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
        
        // Check if user is admin
        if (!hasRole('administrator')) {
            setFlashMessage('error', 'You do not have permission to access this page');
            redirect('dashboard');
            exit;
        }
        
        // Check if ID is provided
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            setFlashMessage('error', 'State ID is required');
            redirect('states');
            exit;
        }
        
        $id = (int)$_GET['id'];
        
        // Get state
        $state = $this->stateModel->getById($id);
        
        if (!$state) {
            setFlashMessage('error', 'State not found');
            redirect('states');
            exit;
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $name = sanitizeInput($_POST['name']);
            
            // Validate input
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required';
            } elseif ($this->stateModel->nameExists($name, $id)) {
                $errors[] = 'State name already exists';
            }
            
            // If no errors, update state
            if (empty($errors)) {
                $data = [
                    'name' => $name,
                    'updated_by' => $_SESSION['user_id']
                ];
                
                $result = $this->stateModel->update($id, $data);
                
                if ($result) {
                    setFlashMessage('success', 'State updated successfully');
                    redirect('states');
                    exit;
                } else {
                    $errors[] = 'Failed to update state';
                }
            }
        }
        
        // Include view
        include 'views/states/edit.php';
    }
    
    /**
     * Delete state
     */
    public function delete() {
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
        
        // Check if ID is provided
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            setFlashMessage('error', 'State ID is required');
            redirect('states');
            exit;
        }
        
        $id = (int)$_GET['id'];
        
        // Check if state has cities and leads
        $cityCount = $this->stateModel->getCityCount($id);
        $leadCount = $this->stateModel->getLeadCount($id);
        
        // Get cities and leads for display in confirmation popup
        $cities = [];
        $leads = [];
        
        if ($cityCount > 0) {
            $cities = $this->cityModel->getAllByStateId($id);
        }
        
        if ($leadCount > 0) {
            $leads = $this->stateModel->getLeadsByStateId($id, 10); // Limit to 10 for display
        }
        
        // If there are cities or leads associated with this state
        if ($cityCount > 0 || $leadCount > 0) {
            // If this is a confirmation request
            if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
                // HARD DELETE all associated data
                $this->db->beginTransaction();
                
                try {
                    // First, get all cities in this state
                    $cities = $this->cityModel->getAllByStateId($id);
                    
                    // For each city, delete all associated data
                    foreach ($cities as $city) {
                        // Delete call logs for leads in this city
                        $this->db->query("DELETE cl FROM call_logs cl 
                                         INNER JOIN leads l ON cl.lead_id = l.id 
                                         WHERE l.city_id = ?", [$city['id']]);
                        
                        // Delete audit logs for leads in this city
                        $this->db->query("DELETE al FROM audit_logs al 
                                         WHERE al.table_name = 'leads' 
                                         AND al.record_id IN (SELECT id FROM leads WHERE city_id = ?)", [$city['id']]);
                        
                        // Delete leads associated with this city
                        $this->db->query("DELETE FROM leads WHERE city_id = ?", [$city['id']]);
                        
                        // Delete audit logs for this city
                        $this->db->query("DELETE FROM audit_logs WHERE table_name = 'cities' AND record_id = ?", [$city['id']]);
                    }
                    
                    // Delete leads directly associated with this state (without city)
                    $this->db->query("DELETE FROM leads WHERE state_id = ?", [$id]);
                    
                    // Delete cities associated with this state
                    $this->db->query("DELETE FROM cities WHERE state_id = ?", [$id]);
                    
                    // Delete employee territories associated with this state
                    $this->db->query("DELETE FROM employee_territories WHERE state_id = ?", [$id]);
                    
                    // Delete audit logs for this state
                    $this->db->query("DELETE FROM audit_logs WHERE table_name = 'states' AND record_id = ?", [$id]);
                    
                    // Now hard delete the state
                    $this->stateModel->hardDelete($id);
                    
                    $this->db->commit();
                    setFlashMessage('success', 'State and all associated data permanently deleted');
                    redirect('states');
                    exit;
                } catch (Exception $e) {
                    $this->db->rollback();
                    setFlashMessage('error', 'Failed to delete state: ' . $e->getMessage());
                    redirect('states');
                    exit;
                }
            } else {
                // Show confirmation page with counts and data
                $state = $this->stateModel->getById($id);
                include __DIR__ . '/../views/states/delete_confirm.php';
                exit;
            }
        }
        
        // Hard delete state with no associated data
        $result = $this->stateModel->hardDelete($id);
        
        if ($result) {
            // Delete audit logs for this state
            $this->db->query("DELETE FROM audit_logs WHERE table_name = 'states' AND record_id = ?", [$id]);
            setFlashMessage('success', 'State permanently deleted');
        } else {
            setFlashMessage('error', 'Failed to delete state');
        }
        
        redirect('states');
        exit;
    }
    
    /**
     * Toggle state status
     */
    public function toggleStatus() {
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
        
        // Check if ID is provided
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            setFlashMessage('error', 'State ID is required');
            redirect('states');
            exit;
        }
        
        $id = (int)$_GET['id'];
        
        // Get state
        $state = $this->stateModel->getById($id);
        
        if (!$state) {
            setFlashMessage('error', 'State not found');
            redirect('states');
            exit;
        }
        
        // Toggle status
        $newStatus = $state['status'] == 1 ? 0 : 1;
        
        $data = [
            'status' => $newStatus,
            'updated_by' => $_SESSION['user_id']
        ];
        
        $result = $this->stateModel->update($id, $data);
        
        if ($result) {
            $statusText = $newStatus == 1 ? 'activated' : 'deactivated';
            setFlashMessage('success', "State {$statusText} successfully");
        } else {
            setFlashMessage('error', 'Failed to update state status');
        }
        
        redirect('states');
        exit;
    }
}
