<?php
/**
 * User Controller
 * 
 * This controller handles all user-related actions.
 */

require_once 'models/User.php';
require_once 'models/State.php';
require_once 'models/City.php';

class UserController {
    private $userModel;
    private $stateModel;
    private $cityModel;
    
    /**
     * Constructor
     */
    public function __construct() {
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
        
        // Check if user is admin
        if (!hasRole('administrator')) {
            setFlashMessage('error', 'You do not have permission to access this page');
            redirect('dashboard');
            exit;
        }
        
        // Get page number
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get users
        $users = $this->userModel->getAllWithRoleName($page);
        
        // Get total count for pagination
        $totalCount = $this->userModel->count();
        $totalPages = ceil($totalCount / RECORDS_PER_PAGE);
        
        // Include view
        include 'views/users/index.php';
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
        
        // Get roles
        $roles = $this->userModel->getRoles();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            $roleId = (int)$_POST['role_id'];
            
            // Validate input
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required';
            }
            
            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            } elseif ($this->userModel->emailExists($email)) {
                $errors[] = 'Email already exists';
            }
            
            if (empty($password)) {
                $errors[] = 'Password is required';
            } elseif (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            } elseif ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }
            
            if (empty($roleId)) {
                $errors[] = 'Role is required';
            }
            
            // If no errors, create user
            if (empty($errors)) {
                $data = [
                    'name' => $name,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT, ['cost' => PASSWORD_HASH_COST]),
                    'role_id' => $roleId,
                    'status' => 1,
                    'created_by' => $_SESSION['user_id']
                ];
                
                $result = $this->userModel->create($data);
                
                if ($result) {
                    setFlashMessage('success', 'User created successfully');
                    redirect('users');
                    exit;
                } else {
                    $errors[] = 'Failed to create user';
                }
            }
        }
        
        // Include view
        include 'views/users/create.php';
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
            setFlashMessage('error', 'User ID is required');
            redirect('users');
            exit;
        }
        
        $id = (int)$_GET['id'];
        
        // Get user
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            setFlashMessage('error', 'User not found');
            redirect('users');
            exit;
        }
        
        // Get roles
        $roles = $this->userModel->getRoles();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            $roleId = (int)$_POST['role_id'];
            
            // Validate input
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required';
            }
            
            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            } elseif ($this->userModel->emailExists($email, $id)) {
                $errors[] = 'Email already exists';
            }
            
            if (!empty($password) && strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            } elseif (!empty($password) && $password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }
            
            if (empty($roleId)) {
                $errors[] = 'Role is required';
            }
            
            // If no errors, update user
            if (empty($errors)) {
                $data = [
                    'name' => $name,
                    'email' => $email,
                    'role_id' => $roleId,
                    'updated_by' => $_SESSION['user_id']
                ];
                
                // Update password if provided
                if (!empty($password)) {
                    $data['password'] = password_hash($password, PASSWORD_DEFAULT, ['cost' => PASSWORD_HASH_COST]);
                }
                
                $result = $this->userModel->update($id, $data);
                
                if ($result) {
                    setFlashMessage('success', 'User updated successfully');
                    redirect('users');
                    exit;
                } else {
                    $errors[] = 'Failed to update user';
                }
            }
        }
        
        // Include view
        include 'views/users/edit.php';
    }
    
    /**
     * Delete user
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
            setFlashMessage('error', 'User ID is required');
            redirect('users');
            exit;
        }
        
        $id = (int)$_GET['id'];
        
        // Prevent deleting self
        if ($id === (int)$_SESSION['user_id']) {
            setFlashMessage('error', 'You cannot delete your own account');
            redirect('users');
            exit;
        }
        
        // Delete user
        $result = $this->userModel->delete($id);
        
        if ($result) {
            setFlashMessage('success', 'User deleted successfully');
        } else {
            setFlashMessage('error', 'Failed to delete user');
        }
        
        redirect('users');
        exit;
    }
    
    /**
     * Toggle user status
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
            setFlashMessage('error', 'User ID is required');
            redirect('users');
            exit;
        }
        
        $id = (int)$_GET['id'];
        
        // Prevent toggling self
        if ($id === (int)$_SESSION['user_id']) {
            setFlashMessage('error', 'You cannot change your own status');
            redirect('users');
            exit;
        }
        
        // Toggle status
        $result = $this->userModel->toggleStatus($id);
        
        if ($result) {
            setFlashMessage('success', 'User status updated successfully');
        } else {
            setFlashMessage('error', 'Failed to update user status');
        }
        
        redirect('users');
        exit;
    }
    
    /**
     * User territories page
     */
    public function territories() {
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
            setFlashMessage('error', 'User ID is required');
            redirect('users');
            exit;
        }
        
        $id = (int)$_GET['id'];
        
        // Get user
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            setFlashMessage('error', 'User not found');
            redirect('users');
            exit;
        }
        
        // Get states
        $states = $this->stateModel->getActiveStates();
        
        // Get user territories
        $territories = $this->userModel->getTerritories($id);
        
        // Include view
        include 'views/users/territories.php';
    }
    
    /**
     * Add territory
     */
    public function addTerritory() {
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
            $userId = (int)$_POST['user_id'];
            $stateId = (int)$_POST['state_id'];
            $cityId = isset($_POST['city_id']) ? (int)$_POST['city_id'] : null;
            
            // Validate input
            $errors = [];
            
            if (empty($userId)) {
                $errors[] = 'User ID is required';
            }
            
            if (empty($stateId)) {
                $errors[] = 'State is required';
            }
            
            // If no errors, add territory
            if (empty($errors)) {
                $result = $this->userModel->addTerritory($userId, $stateId, $cityId);
                
                if ($result) {
                    setFlashMessage('success', 'Territory added successfully');
                } else {
                    setFlashMessage('error', 'Failed to add territory');
                }
            } else {
                setFlashMessage('error', implode('<br>', $errors));
            }
            
            redirect('users/territories?id=' . $userId);
            exit;
        } else {
            setFlashMessage('error', 'Invalid request');
            redirect('users');
            exit;
        }
    }
    
    /**
     * Remove territory
     */
    public function removeTerritory() {
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
            setFlashMessage('error', 'Territory ID is required');
            redirect('users');
            exit;
        }
        
        $id = (int)$_GET['id'];
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
        
        // Remove territory
        $result = $this->userModel->removeTerritory($id);
        
        if ($result) {
            setFlashMessage('success', 'Territory removed successfully');
        } else {
            setFlashMessage('error', 'Failed to remove territory');
        }
        
        redirect('users/territories?id=' . $userId);
        exit;
    }
    
    /**
     * User profile page
     */
    public function profile() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
            exit;
        }
        
        $id = (int)$_SESSION['user_id'];
        
        // Get user
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            setFlashMessage('error', 'User not found');
            redirect('dashboard');
            exit;
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email']);
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            // Validate input
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required';
            }
            
            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            } elseif ($this->userModel->emailExists($email, $id)) {
                $errors[] = 'Email already exists';
            }
            
            // Check if password change is requested
            if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
                if (empty($currentPassword)) {
                    $errors[] = 'Current password is required';
                } elseif (!password_verify($currentPassword, $user['password'])) {
                    $errors[] = 'Current password is incorrect';
                }
                
                if (empty($newPassword)) {
                    $errors[] = 'New password is required';
                } elseif (strlen($newPassword) < 6) {
                    $errors[] = 'New password must be at least 6 characters';
                } elseif ($newPassword !== $confirmPassword) {
                    $errors[] = 'New passwords do not match';
                }
            }
            
            // If no errors, update user
            if (empty($errors)) {
                $data = [
                    'name' => $name,
                    'email' => $email,
                    'updated_by' => $id
                ];
                
                // Update password if provided
                if (!empty($newPassword)) {
                    $data['password'] = password_hash($newPassword, PASSWORD_DEFAULT, ['cost' => PASSWORD_HASH_COST]);
                }
                
                $result = $this->userModel->update($id, $data);
                
                if ($result) {
                    // Update session
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    
                    setFlashMessage('success', 'Profile updated successfully');
                    redirect('users/profile');
                    exit;
                } else {
                    $errors[] = 'Failed to update profile';
                }
            }
        }
        
        // Include view
        include 'views/users/profile.php';
    }
}

