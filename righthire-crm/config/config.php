<?php
/**
 * Right Hire CRM Configuration File
 * 
 * This file contains all the configuration settings for the Right Hire CRM application.
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'righthire_crm');
define('DB_USER', 'db_username'); // Change this to your database username
define('DB_PASS', 'db_password'); // Change this to your database password

// Application configuration
define('APP_NAME', 'Right Hire CRM');
define('APP_URL', 'http://localhost/righthire-crm'); // Change this to your domain
define('APP_ROOT', dirname(dirname(__FILE__)));
define('RECORDS_PER_PAGE', 50);
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

// Security configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('CSRF_TOKEN_NAME', 'righthire_csrf_token');

// Default admin credentials (only used for initial setup)
define('DEFAULT_ADMIN_EMAIL', 'sales@getrigthhire.com');
define('DEFAULT_ADMIN_PASSWORD', 'Sales@112233');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

