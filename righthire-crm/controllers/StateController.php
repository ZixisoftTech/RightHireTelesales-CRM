<?php
/**
 * State Controller
 * 
 * This controller handles all state-related actions.
 */

require_once 'models/State.php';
require_once 'models/City.php';

class StateController {
    private $stateModel;
    private $cityModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->stateModel = new State();
        $this->cityModel = new City();
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
        
        // Check if state has cities
        $cities = $this->cityModel->getByStateId($id);
        
        if (!empty($cities)) {
            setFlashMessage('error', 'Cannot delete state with cities. Please delete cities first.');
            redirect('states');
            exit;
        }
        
        // Delete state (hard delete to allow reusing the name)
        $result = $this->stateModel->hardDelete($id);
        
        if ($result) {
            setFlashMessage('success', 'State deleted successfully');
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

