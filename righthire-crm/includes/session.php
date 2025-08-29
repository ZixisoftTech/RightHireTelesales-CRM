<?php
/**
 * Session Management
 * 
 * This file handles session initialization and security.
 */

// Start session with secure settings
function secureSessionStart() {
    $sessionName = 'righthire_session';
    $secure = false; // Set to true if using HTTPS
    $httpOnly = true;
    
    // Force session to use cookies only
    ini_set('session.use_only_cookies', 1);
    
    // Get current cookie params
    $cookieParams = session_get_cookie_params();
    
    // Set secure cookie parameters
    session_set_cookie_params(
        SESSION_LIFETIME,
        $cookieParams['path'],
        $cookieParams['domain'],
        $secure,
        $httpOnly
    );
    
    // Set session name
    session_name($sessionName);
    
    // Start session
    session_start();
    
    // Regenerate session ID periodically to prevent session fixation
    if (!isset($_SESSION['last_regeneration'])) {
        regenerateSession();
    } else {
        // Regenerate session ID every 30 minutes
        $interval = 30 * 60;
        if ($_SESSION['last_regeneration'] + $interval < time()) {
            regenerateSession();
        }
    }
}

/**
 * Regenerate session ID and update last regeneration time
 */
function regenerateSession() {
    // Regenerate session ID
    session_regenerate_id(true);
    
    // Update last regeneration time
    $_SESSION['last_regeneration'] = time();
}

/**
 * Destroy session and clean up
 */
function destroySession() {
    // Unset all session variables
    $_SESSION = [];
    
    // Get session cookie parameters
    $params = session_get_cookie_params();
    
    // Delete the session cookie
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
    
    // Destroy session
    session_destroy();
}

// Initialize secure session
secureSessionStart();

