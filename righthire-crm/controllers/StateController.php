<?php
/**
 * State Controller
 * 
 * This controller handles all state-related actions.
 */

require_once 'models/State.php';

class StateController {
    private $stateModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->stateModel = new State();
    }
    
    /**
     * States index page
     */
    public function index() {
        // Require admin
        requireAdmin();
        
        // Get states with city count
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $states = $this->stateModel->getAllWithCityCount($page);
        $totalStates = $this->stateModel->count();
        $totalPages = ceil($totalStates / RECORDS_PER_PAGE);
        
        // Set page title
        $pageTitle = 'Manage States';
        
        // Get current route
        $route = isset($_GET['route']) ? $_GET['route'] : 'states';
        
        // Include view
        include 'views/states/index.php';
    }
    
    /**
     * Create state page
     */
    public function create() {
        // Require admin
        requireAdmin();
        
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
                    'status' => 1
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
            
            // If we get here, there were errors
            $pageTitle = 'Create State';
            $route = 'states/create';
            include 'views/states/create.php';
        } else {
            // Display create form
            $pageTitle = 'Create State';
            $route = 'states/create';
            include 'views/states/create.php';
        }
    }
    
    /**
     * Edit state page
     */
    public function edit() {
        // Require admin
        requireAdmin();
        
        // Get state ID from URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            setFlashMessage('error', 'Invalid state ID');
            redirect('states');
            exit;
        }
        
        // Get state
        $state = $this->stateModel->find($id);
        
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
                    'name' => $name
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
            
            // If we get here, there were errors
            $pageTitle = 'Edit State';
            $route = 'states/edit';
            include 'views/states/edit.php';
        } else {
            // Display edit form
            $pageTitle = 'Edit State';
            $route = 'states/edit';
            include 'views/states/edit.php';
        }
    }
    
    /**
     * Delete state
     */
    public function delete() {
        // Require admin
        requireAdmin();
        
        // Get state ID from URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            setFlashMessage('error', 'Invalid state ID');
            redirect('states');
            exit;
        }
        
        // Delete state
        $result = $this->stateModel->delete($id);
        
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
        // Require admin
        requireAdmin();
        
        // Get state ID from URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            setFlashMessage('error', 'Invalid state ID');
            redirect('states');
            exit;
        }
        
        // Toggle status
        $result = $this->stateModel->toggleStatus($id);
        
        if ($result) {
            setFlashMessage('success', 'State status updated successfully');
        } else {
            setFlashMessage('error', 'Failed to update state status');
        }
        
        redirect('states');
        exit;
    }
}

