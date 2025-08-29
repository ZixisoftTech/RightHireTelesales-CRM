<?php
/**
 * Auth Controller
 * 
 * This controller handles all authentication-related actions.
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
        // Check if already logged in
        if (isLoggedIn()) {
            redirect('dashboard');
            exit;
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $email = sanitizeInput($_POST['email']);
            $password = $_POST['password']; // Don't sanitize password
            $remember = isset($_POST['remember']) ? true : false;
            
            // Validate input
            $errors = [];
            
            if (empty($email)) {
                $errors[] = 'Email is required';
            }
            
            if (empty($password)) {
                $errors[] = 'Password is required';
            }
            
            // If no errors, attempt login
            if (empty($errors)) {
                $user = $this->userModel->getByEmail($email);
                
                if ($user && password_verify($password, $user['password'])) {
                    // Check if user is active
                    if ($user['status'] != 1) {
                        $errors[] = 'Your account is inactive. Please contact the administrator.';
                    } else {
                        // Set session variables
                        $_SESSION['authenticated'] = true;
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        
                        // Set session lifetime
                        if ($remember) {
                            ini_set('session.cookie_lifetime', SESSION_LIFETIME);
                            ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
                        }
                        
                        // Redirect to dashboard
                        redirect('dashboard');
                        exit;
                    }
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
     * Logout
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page
        redirect('auth/login');
        exit;
    }
    
    /**
     * Forgot password page
     */
    public function forgotPassword() {
        // Check if already logged in
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
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }
            
            // If no errors, process forgot password
            if (empty($errors)) {
                $user = $this->userModel->getByEmail($email);
                
                if ($user) {
                    // Generate token
                    $token = bin2hex(random_bytes(32));
                    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    // Save token to database
                    $db = Database::getInstance();
                    $db->insert('password_reset_tokens', [
                        'user_id' => $user['id'],
                        'token' => $token,
                        'expires_at' => $expiresAt
                    ]);
                    
                    // Send email (in a real application)
                    // For now, just show the reset link
                    $resetLink = APP_URL . '/auth/reset-password?token=' . $token;
                    
                    setFlashMessage('success', 'Password reset link has been sent to your email. <br>Reset Link: <a href="' . $resetLink . '">' . $resetLink . '</a>');
                    redirect('auth/login');
                    exit;
                } else {
                    // Don't reveal that the email doesn't exist
                    setFlashMessage('success', 'If your email is registered, you will receive a password reset link.');
                    redirect('auth/login');
                    exit;
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
        // Check if already logged in
        if (isLoggedIn()) {
            redirect('dashboard');
            exit;
        }
        
        // Check if token is provided
        if (!isset($_GET['token']) || empty($_GET['token'])) {
            setFlashMessage('error', 'Invalid or missing token');
            redirect('auth/login');
            exit;
        }
        
        $token = $_GET['token'];
        
        // Check if token is valid
        $db = Database::getInstance();
        $sql = "SELECT * FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()";
        $tokenData = $db->getRow($sql, [$token]);
        
        if (!$tokenData) {
            setFlashMessage('error', 'Invalid or expired token');
            redirect('auth/login');
            exit;
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $password = $_POST['password']; // Don't sanitize password
            $confirmPassword = $_POST['confirm_password']; // Don't sanitize password
            
            // Validate input
            $errors = [];
            
            if (empty($password)) {
                $errors[] = 'Password is required';
            } elseif (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            }
            
            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }
            
            // If no errors, reset password
            if (empty($errors)) {
                // Update user password
                $this->userModel->updateUser($tokenData['user_id'], [
                    'password' => $password
                ]);
                
                // Delete token
                $db->delete('password_reset_tokens', 'id = ?', [$tokenData['id']]);
                
                setFlashMessage('success', 'Password has been reset successfully. You can now login with your new password.');
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

