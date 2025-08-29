<?php
/**
 * Direct Admin Login
 * 
 * This file provides a direct login for the administrator.
 * It bypasses the regular login process and sets the session variables directly.
 * 
 * IMPORTANT: This file should be removed in production.
 */

// Start session
session_start();

// Set session variables for admin
$_SESSION['authenticated'] = true;
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Administrator';
$_SESSION['user_email'] = 'sales@getrigthhire.com';
$_SESSION['role'] = 'administrator';

// Redirect to dashboard
header('Location: index.php?route=dashboard');
exit;

