<?php
/**
 * Authentication Controller
 * 
 * This class handles user authentication and related actions.
 */

require_once 'models/User.php';

class AuthController {
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Login page
     */
    public function login() {
        // If already logged in, redirect to dashboard
        if (isLoggedIn()) {
            redirect('dashboard');
            exit;
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $email = sanitizeInput($_POST['email']);
            $password = $_POST['password']; // Don't sanitize password
            
            // Validate input
            $errors = [];
            
            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!validateEmail($email)) {
                $errors[] = 'Invalid email format';
            }
            
            if (empty($password)) {
                $errors[] = 'Password is required';
            }
            
            // If no errors, attempt to authenticate
            if (empty($errors)) {
                $user = $this->userModel->authenticate($email, $password);
                
                if ($user) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Regenerate session ID for security
                    regenerateSession();
                    
                    // Redirect to dashboard
                    redirect('dashboard');
                    exit;
                } else {
                    $errors[] = 'Invalid email or password';
                }
            }
            
            // If we get here, there were errors
            include 'views/auth/login.php';
        } else {
            // Display login form
            include 'views/auth/login.php';
        }
    }
    
    /**
     * Logout action
     */
    public function logout() {
        // Destroy session
        destroySession();
        
        // Redirect to login page
        redirect('auth/login');
        exit;
    }
    
    /**
     * Forgot password page
     */
    public function forgotPassword() {
        // If already logged in, redirect to dashboard
        if (isLoggedIn()) {
            redirect('dashboard');
            exit;
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $email = sanitizeInput($_POST['email']);
            
            // Validate input
            $errors = [];
            
            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!validateEmail($email)) {
                $errors[] = 'Invalid email format';
            }
            
            // If no errors, check if email exists
            if (empty($errors)) {
                $user = $this->userModel->findByEmail($email);
                
                if ($user) {
                    // Generate reset token
                    $token = bin2hex(random_bytes(32));
                    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    // Store token in database
                    // In a real application, you would have a password_resets table
                    // For simplicity, we'll just show a success message
                    
                    // Send email with reset link
                    // In a real application, you would send an email with the reset link
                    // For simplicity, we'll just show a success message
                    
                    setFlashMessage('success', 'Password reset instructions have been sent to your email');
                    redirect('auth/login');
                    exit;
                } else {
                    $errors[] = 'Email not found';
                }
            }
            
            // If we get here, there were errors
            include 'views/auth/forgot_password.php';
        } else {
            // Display forgot password form
            include 'views/auth/forgot_password.php';
        }
    }
    
    /**
     * Reset password page
     */
    public function resetPassword() {
        // If already logged in, redirect to dashboard
        if (isLoggedIn()) {
            redirect('dashboard');
            exit;
        }
        
        // Get token from URL
        $token = isset($_GET['token']) ? $_GET['token'] : '';
        
        if (empty($token)) {
            setFlashMessage('error', 'Invalid or expired token');
            redirect('auth/login');
            exit;
        }
        
        // Verify token
        // In a real application, you would check if the token exists and is not expired
        // For simplicity, we'll just show the reset password form
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $password = $_POST['password']; // Don't sanitize password
            $confirmPassword = $_POST['confirm_password']; // Don't sanitize password
            
            // Validate input
            $errors = [];
            
            if (empty($password)) {
                $errors[] = 'Password is required';
            } elseif (strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters long';
            }
            
            if (empty($confirmPassword)) {
                $errors[] = 'Confirm password is required';
            } elseif ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }
            
            // If no errors, update password
            if (empty($errors)) {
                // In a real application, you would update the user's password
                // For simplicity, we'll just show a success message
                
                setFlashMessage('success', 'Password has been reset successfully');
                redirect('auth/login');
                exit;
            }
            
            // If we get here, there were errors
            include 'views/auth/reset_password.php';
        } else {
            // Display reset password form
            include 'views/auth/reset_password.php';
        }
    }
}

