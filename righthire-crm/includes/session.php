<?php
/**
 * Session Management
 * 
 * This file contains functions for session management.
 */

// Set session cookie parameters
$lifetime = 86400; // 24 hours
$path = '/';
$domain = '';
$secure = isset($_SERVER['HTTPS']);
$httponly = true;

// Set session cookie parameters
session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);

// Regenerate session ID periodically
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 3600) {
    // Regenerate session ID every hour
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Set session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $lifetime)) {
    // Last activity was more than 24 hours ago
    session_unset();
    session_destroy();
    session_start();
}

// Update last activity time
$_SESSION['last_activity'] = time();

