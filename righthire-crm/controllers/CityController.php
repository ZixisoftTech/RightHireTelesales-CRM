<?php
/**
 * City Controller
 * 
 * This controller handles all city-related actions.
 */

require_once 'models/City.php';
require_once 'models/State.php';
require_once 'config/database.php';

class CityController {
    private $cityModel;
    private $stateModel;
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->cityModel = new City();
        $this->stateModel = new State();
        $this->db = Database::getInstance();
    }
    
    /**
     * Cities index page
     */
    public function index() {
        // Require admin
        requireAdmin();
        
        // Get cities with state name
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $cities = $this->cityModel->getAllWithStateName($page);
        $totalCities = $this->cityModel->count();
        $totalPages = ceil($totalCities / RECORDS_PER_PAGE);
        
        // Set page title
        $pageTitle = 'Manage Cities';
        
        // Get current route
        $route = isset($_GET['route']) ? $_GET['route'] : 'cities';
        
        // Include view
        include 'views/cities/index.php';
    }
    
    /**
     * Create city page
     */
    public function create() {
        // Require admin
        requireAdmin();
        
        // Get all active states
        $states = $this->stateModel->getActiveStates();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $stateId = (int)$_POST['state_id'];
            $name = sanitizeInput($_POST['name']);
            
            // Validate input
            $errors = [];
            
            if (empty($stateId)) {
                $errors[] = 'State is required';
            }
            
            if (empty($name)) {
                $errors[] = 'Name is required';
            } elseif ($this->cityModel->nameExistsInState($name, $stateId)) {
                $errors[] = 'City name already exists in this state';
            }
            
            // If no errors, create city
            if (empty($errors)) {
                $data = [
                    'state_id' => $stateId,
                    'name' => $name,
                    'status' => 1,
                    'created_by' => $_SESSION['user_id']
                ];
                
                $result = $this->cityModel->create($data);
                
                if ($result) {
                    setFlashMessage('success', 'City created successfully');
                    redirect('cities');
                    exit;
                } else {
                    $errors[] = 'Failed to create city';
                }
            }
            
            // If we get here, there were errors
            $pageTitle = 'Create City';
            $route = 'cities/create';
            include 'views/cities/create.php';
        } else {
            // Display create form
            $pageTitle = 'Create City';
            $route = 'cities/create';
            include 'views/cities/create.php';
        }
    }
    
    /**
     * Edit city page
     */
    public function edit() {
        // Require admin
        requireAdmin();
        
        // Get city ID from URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            setFlashMessage('error', 'Invalid city ID');
            redirect('cities');
            exit;
        }
        
        // Get city
        $city = $this->cityModel->find($id);
        
        if (!$city) {
            setFlashMessage('error', 'City not found');
            redirect('cities');
            exit;
        }
        
        // Get all active states
        $states = $this->stateModel->getActiveStates();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $stateId = (int)$_POST['state_id'];
            $name = sanitizeInput($_POST['name']);
            
            // Validate input
            $errors = [];
            
            if (empty($stateId)) {
                $errors[] = 'State is required';
            }
            
            if (empty($name)) {
                $errors[] = 'Name is required';
            } elseif ($this->cityModel->nameExistsInState($name, $stateId, $id)) {
                $errors[] = 'City name already exists in this state';
            }
            
            // If no errors, update city
            if (empty($errors)) {
                $data = [
                    'state_id' => $stateId,
                    'name' => $name,
                    'updated_by' => $_SESSION['user_id']
                ];
                
                $result = $this->cityModel->update($id, $data);
                
                if ($result) {
                    setFlashMessage('success', 'City updated successfully');
                    redirect('cities');
                    exit;
                } else {
                    $errors[] = 'Failed to update city';
                }
            }
            
            // If we get here, there were errors
            $pageTitle = 'Edit City';
            $route = 'cities/edit';
            include 'views/cities/edit.php';
        } else {
            // Display edit form
            $pageTitle = 'Edit City';
            $route = 'cities/edit';
            include 'views/cities/edit.php';
        }
    }
    
    /**
     * Delete city
     */
    public function delete() {
        // Require admin
        requireAdmin();
        
        // Get city ID from URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            setFlashMessage('error', 'Invalid city ID');
            redirect('cities');
            exit;
        }
        
        // Check if city has leads
        $leadCount = $this->cityModel->getLeadCount($id);
        
        // If there are leads associated with this city
        if ($leadCount > 0) {
            // If this is a confirmation request
            if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
                // Delete all associated leads first (cascade delete)
                $this->db->beginTransaction();
                
                try {
                    // Soft delete leads associated with this city
                    $this->db->query("UPDATE leads SET deleted_at = NOW(), updated_by = ? WHERE city_id = ? AND deleted_at IS NULL", [getCurrentUserId(), $id]);
                    
                    // Now delete the city
                    $this->cityModel->hardDelete($id);
                    
                    $this->db->commit();
                    setFlashMessage('success', 'City and all associated data deleted successfully');
                    redirect('cities');
                    exit;
                } catch (Exception $e) {
                    $this->db->rollback();
                    setFlashMessage('error', 'Failed to delete city: ' . $e->getMessage());
                    redirect('cities');
                    exit;
                }
            } else {
                // Show confirmation page with counts
                $city = $this->cityModel->find($id);
                $state = $this->stateModel->find($city['state_id']);
                include 'views/cities/delete_confirm.php';
                exit;
            }
        }
        
        // Delete city (hard delete to allow reusing the name)
        $result = $this->cityModel->hardDelete($id);
        
        if ($result) {
            setFlashMessage('success', 'City deleted successfully');
        } else {
            setFlashMessage('error', 'Failed to delete city');
        }
        
        redirect('cities');
        exit;
    }
    
    /**
     * Toggle city status
     */
    public function toggleStatus() {
        // Require admin
        requireAdmin();
        
        // Get city ID from URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            setFlashMessage('error', 'Invalid city ID');
            redirect('cities');
            exit;
        }
        
        // Toggle status
        $result = $this->cityModel->toggleStatus($id);
        
        if ($result) {
            setFlashMessage('success', 'City status updated successfully');
        } else {
            setFlashMessage('error', 'Failed to update city status');
        }
        
        redirect('cities');
        exit;
    }
    
    /**
     * Get cities by state (AJAX)
     */
    public function getByState() {
        // Require login
        requireLogin();
        
        // Get state ID from URL
        $stateId = isset($_GET['state_id']) ? (int)$_GET['state_id'] : 0;
        
        if (!$stateId) {
            echo json_encode(['error' => 'Invalid state ID']);
            exit;
        }
        
        // Get cities by state
        if (hasRole('administrator')) {
            $cities = $this->cityModel->getActiveByState($stateId);
        } else {
            $cities = $this->cityModel->getCitiesForEmployee(getCurrentUserId(), $stateId);
        }
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode(['cities' => $cities]);
        exit;
    }
}
