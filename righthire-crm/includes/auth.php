<?php
/**
 * Authentication Functions
 * 
 * This file contains all the authentication related functions.
 */

/**
 * Check if user is logged in
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

/**
 * Require login
 * 
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'You must be logged in to access this page');
        redirect('auth/login');
        exit;
    }
}

/**
 * Require admin role
 * 
 * @return void
 */
function requireAdmin() {
    requireLogin();
    
    if (!hasRole('administrator')) {
        setFlashMessage('error', 'You do not have permission to access this page');
        redirect('dashboard');
        exit;
    }
}

/**
 * Get current user ID
 * 
 * @return int|null
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Get current user name
 * 
 * @return string|null
 */
function getCurrentUserName() {
    return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
}

/**
 * Get current user email
 * 
 * @return string|null
 */
function getCurrentUserEmail() {
    return isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null;
}

/**
 * Get current user role
 * 
 * @return string|null
 */
function getCurrentUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

/**
 * Login user
 * 
 * @param array $user
 * @return void
 */
function loginUser($user) {
    $_SESSION['authenticated'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
}

/**
 * Logout user
 * 
 * @return void
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
    
    // Regenerate session ID
    session_start();
    session_regenerate_id(true);
}

