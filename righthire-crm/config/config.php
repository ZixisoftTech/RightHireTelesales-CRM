<?php
/**
 * Configuration File
 * 
 * This file contains all the configuration settings for the application.
 */

// Start session
session_start();

// Define constants
define('APP_NAME', 'Right Hire CRM');
define('APP_VERSION', '2.0.0');
define('APP_URL', 'http://localhost/righthire-crm');
define('RECORDS_PER_PAGE', 25);
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'righthire_crm');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Security settings
define('PASSWORD_HASH_COST', 12);
define('SESSION_LIFETIME', 86400); // 24 hours
define('CSRF_TOKEN_NAME', 'csrf_token');

// Include database connection
require_once 'database.php';

// Include helper functions
require_once 'helpers.php';

// Set error reporting
if (true) { // Set to false in production
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}
