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
     * Users index page
     */
    public function index() {
        // Require admin
        requireAdmin();
        
        // Get users with lead count
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $users = $this->userModel->getAllWithLeadCount($page);
        $totalUsers = $this->userModel->count();
        $totalPages = ceil($totalUsers / RECORDS_PER_PAGE);
        
        // Set page title
        $pageTitle = 'Manage Users';
        
        // Include view
        include 'views/users/index.php';
    }
    
    /**
     * Create user page
     */
    public function create() {
        // Require admin
        requireAdmin();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email']);
            $password = $_POST['password']; // Don't sanitize password
            $confirmPassword = $_POST['confirm_password']; // Don't sanitize password
            $role = sanitizeInput($_POST['role']);
            
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
            }
            
            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }
            
            if (empty($role)) {
                $errors[] = 'Role is required';
            } elseif (!in_array($role, ['administrator', 'employee'])) {
                $errors[] = 'Invalid role';
            }
            
            // If no errors, create user
            if (empty($errors)) {
                $data = [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'role' => $role,
                    'status' => 1
                ];
                
                $result = $this->userModel->createUser($data);
                
                if ($result) {
                    setFlashMessage('success', 'User created successfully');
                    redirect('users');
                    exit;
                } else {
                    $errors[] = 'Failed to create user';
                }
            }
            
            // If we get here, there were errors
            $pageTitle = 'Create User';
            include 'views/users/create.php';
        } else {
            // Display create form
            $pageTitle = 'Create User';
            include 'views/users/create.php';
        }
    }
    
    /**
     * Edit user page
     */
    public function edit() {
        // Require admin
        requireAdmin();
        
        // Get user ID from URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            setFlashMessage('error', 'Invalid user ID');
            redirect('users');
            exit;
        }
        
        // Get user
        $user = $this->userModel->find($id);
        
        if (!$user) {
            setFlashMessage('error', 'User not found');
            redirect('users');
            exit;
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email']);
            $password = $_POST['password']; // Don't sanitize password
            $confirmPassword = $_POST['confirm_password']; // Don't sanitize password
            $role = sanitizeInput($_POST['role']);
            
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
            }
            
            if (!empty($password) && $password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }
            
            if (empty($role)) {
                $errors[] = 'Role is required';
            } elseif (!in_array($role, ['administrator', 'employee'])) {
                $errors[] = 'Invalid role';
            }
            
            // If no errors, update user
            if (empty($errors)) {
                $data = [
                    'name' => $name,
                    'email' => $email,
                    'role' => $role
                ];
                
                if (!empty($password)) {
                    $data['password'] = $password;
                }
                
                $result = $this->userModel->updateUser($id, $data);
                
                if ($result) {
                    setFlashMessage('success', 'User updated successfully');
                    redirect('users');
                    exit;
                } else {
                    $errors[] = 'Failed to update user';
                }
            }
            
            // If we get here, there were errors
            $pageTitle = 'Edit User';
            include 'views/users/edit.php';
        } else {
            // Display edit form
            $pageTitle = 'Edit User';
            include 'views/users/edit.php';
        }
    }
    
    /**
     * Delete user
     */
    public function delete() {
        // Require admin
        requireAdmin();
        
        // Get user ID from URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            setFlashMessage('error', 'Invalid user ID');
            redirect('users');
            exit;
        }
        
        // Check if user is current user
        if ($id === getCurrentUserId()) {
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
        // Require admin
        requireAdmin();
        
        // Get user ID from URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            setFlashMessage('error', 'Invalid user ID');
            redirect('users');
            exit;
        }
        
        // Check if user is current user
        if ($id === getCurrentUserId()) {
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
     * Manage territories page
     */
    public function territories() {
        // Require admin
        requireAdmin();
        
        // Get user ID from URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            setFlashMessage('error', 'Invalid user ID');
            redirect('users');
            exit;
        }
        
        // Get user
        $user = $this->userModel->find($id);
        
        if (!$user) {
            setFlashMessage('error', 'User not found');
            redirect('users');
            exit;
        }
        
        // Get user territories
        $territories = $this->userModel->getEmployeeTerritories($id);
        
        // Get all active states
        $states = $this->stateModel->getAllActive();
        
        // Set page title
        $pageTitle = 'Manage Territories';
        
        // Include view
        include 'views/users/territories.php';
    }
    
    /**
     * Add territory
     */
    public function addTerritory() {
        // Require admin
        requireAdmin();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $userId = (int)$_POST['user_id'];
            $stateId = (int)$_POST['state_id'];
            $cityId = isset($_POST['city_id']) && !empty($_POST['city_id']) ? (int)$_POST['city_id'] : null;
            
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
                $result = $this->userModel->addEmployeeTerritory($userId, $stateId, $cityId);
                
                if ($result) {
                    setFlashMessage('success', 'Territory added successfully');
                } else {
                    setFlashMessage('error', 'Failed to add territory');
                }
                
                redirect('users/territories?id=' . $userId);
                exit;
            }
            
            // If we get here, there were errors
            setFlashMessage('error', implode('<br>', $errors));
            redirect('users/territories?id=' . $userId);
            exit;
        } else {
            // Redirect to territories page
            redirect('users');
            exit;
        }
    }
    
    /**
     * Remove territory
     */
    public function removeTerritory() {
        // Require admin
        requireAdmin();
        
        // Get territory ID from URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
        
        if (!$id || !$userId) {
            setFlashMessage('error', 'Invalid territory ID');
            redirect('users');
            exit;
        }
        
        // Remove territory
        $result = $this->userModel->removeEmployeeTerritory($id);
        
        if ($result) {
            setFlashMessage('success', 'Territory removed successfully');
        } else {
            setFlashMessage('error', 'Failed to remove territory');
        }
        
        redirect('users/territories?id=' . $userId);
        exit;
    }
    
    /**
     * Profile page
     */
    public function profile() {
        // Require login
        requireLogin();
        
        // Get current user
        $user = $this->userModel->find(getCurrentUserId());
        
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
            $currentPassword = $_POST['current_password']; // Don't sanitize password
            $newPassword = $_POST['new_password']; // Don't sanitize password
            $confirmPassword = $_POST['confirm_password']; // Don't sanitize password
            
            // Validate input
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required';
            }
            
            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            } elseif ($this->userModel->emailExists($email, getCurrentUserId())) {
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
                }
                
                if ($newPassword !== $confirmPassword) {
                    $errors[] = 'Passwords do not match';
                }
            }
            
            // If no errors, update user
            if (empty($errors)) {
                $data = [
                    'name' => $name,
                    'email' => $email
                ];
                
                if (!empty($newPassword)) {
                    $data['password'] = $newPassword;
                }
                
                $result = $this->userModel->updateUser(getCurrentUserId(), $data);
                
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
            
            // If we get here, there were errors
            $pageTitle = 'My Profile';
            include 'views/users/profile.php';
        } else {
            // Display profile form
            $pageTitle = 'My Profile';
            include 'views/users/profile.php';
        }
    }
}

