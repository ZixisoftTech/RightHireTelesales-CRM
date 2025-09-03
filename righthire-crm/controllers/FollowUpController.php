<?php
/**
 * Follow Up Controller
 * 
 * This controller handles the follow-ups section.
 */

require_once 'models/Lead.php';
require_once 'models/User.php';
require_once 'models/State.php';
require_once 'models/City.php';

class FollowUpController {
    private $leadModel;
    private $userModel;
    private $stateModel;
    private $cityModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->leadModel = new Lead();
        $this->userModel = new User();
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
        
        // Get filter parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
        $stateId = isset($_GET['state_id']) ? (int)$_GET['state_id'] : 0;
        $cityId = isset($_GET['city_id']) ? (int)$_GET['city_id'] : 0;
        $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
        $startDate = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : '';
        $endDate = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : '';
        
        // Validate employee access for non-admins
        if (!hasRole('administrator') && $employeeId !== 0 && $employeeId !== getCurrentUserId()) {
            $employeeId = getCurrentUserId();
        }
        
        // Get follow-ups with filters
        $followUps = $this->leadModel->getFollowUps(
            $page,
            $status,
            $stateId,
            $cityId,
            $employeeId,
            $startDate,
            $endDate
        );
        
        // Get total count for pagination
        $totalCount = $this->leadModel->countFollowUps(
            $status,
            $stateId,
            $cityId,
            $employeeId,
            $startDate,
            $endDate
        );
        
        // Calculate pagination
        $totalPages = ceil($totalCount / RECORDS_PER_PAGE);
        
        // Get all active states for filter dropdown
        $states = $this->stateModel->getActiveStates();
        
        // Get cities for selected state
        $cities = [];
        if ($stateId > 0) {
            $cities = $this->cityModel->getActiveCitiesByState($stateId);
        }
        
        // Get all active employees for filter dropdown (admin only)
        $employees = [];
        if (hasRole('administrator')) {
            $employees = $this->userModel->getAllActiveEmployees();
        }
        
        // Include view
        include 'views/followups/index.php';
    }
}
