<?php
/**
 * Main Entry Point
 * 
 * This file serves as the main entry point for the application.
 * It handles routing and controller instantiation.
 */

// Include configuration
require_once 'config/config.php';

// Define routes
$routes = [
    // Auth routes
    'auth/login' => ['controller' => 'AuthController', 'action' => 'login'],
    'auth/logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    'auth/forgot-password' => ['controller' => 'AuthController', 'action' => 'forgotPassword'],
    'auth/reset-password' => ['controller' => 'AuthController', 'action' => 'resetPassword'],
    
    // Dashboard routes
    'dashboard' => ['controller' => 'DashboardController', 'action' => 'index'],
    
    // State routes
    'states' => ['controller' => 'StateController', 'action' => 'index'],
    'states/create' => ['controller' => 'StateController', 'action' => 'create'],
    'states/edit' => ['controller' => 'StateController', 'action' => 'edit'],
    'states/delete' => ['controller' => 'StateController', 'action' => 'delete'],
    'states/toggle-status' => ['controller' => 'StateController', 'action' => 'toggleStatus'],
    
    // City routes
    'cities' => ['controller' => 'CityController', 'action' => 'index'],
    'cities/create' => ['controller' => 'CityController', 'action' => 'create'],
    'cities/edit' => ['controller' => 'CityController', 'action' => 'edit'],
    'cities/delete' => ['controller' => 'CityController', 'action' => 'delete'],
    'cities/toggle-status' => ['controller' => 'CityController', 'action' => 'toggleStatus'],
    'cities/get-by-state' => ['controller' => 'CityController', 'action' => 'getByState'],
    
    // Lead routes
    'leads' => ['controller' => 'LeadController', 'action' => 'index'],
    'leads/create' => ['controller' => 'LeadController', 'action' => 'create'],
    'leads/edit' => ['controller' => 'LeadController', 'action' => 'edit'],
    'leads/view' => ['controller' => 'LeadController', 'action' => 'view'],
    'leads/delete' => ['controller' => 'LeadController', 'action' => 'delete'],
    'leads/update-status' => ['controller' => 'LeadController', 'action' => 'updateStatus'],
    'leads/import' => ['controller' => 'LeadController', 'action' => 'import'],
    'leads/export' => ['controller' => 'LeadController', 'action' => 'export'],
    
    // User routes
    'users' => ['controller' => 'UserController', 'action' => 'index'],
    'users/create' => ['controller' => 'UserController', 'action' => 'create'],
    'users/edit' => ['controller' => 'UserController', 'action' => 'edit'],
    'users/delete' => ['controller' => 'UserController', 'action' => 'delete'],
    'users/toggle-status' => ['controller' => 'UserController', 'action' => 'toggleStatus'],
    'users/territories' => ['controller' => 'UserController', 'action' => 'territories'],
    'users/add-territory' => ['controller' => 'UserController', 'action' => 'addTerritory'],
    'users/remove-territory' => ['controller' => 'UserController', 'action' => 'removeTerritory'],
    'users/profile' => ['controller' => 'UserController', 'action' => 'profile'],
    
    // Report routes
    'reports' => ['controller' => 'ReportController', 'action' => 'index'],
    'reports/lead-status' => ['controller' => 'ReportController', 'action' => 'leadStatus'],
    'reports/call-log' => ['controller' => 'ReportController', 'action' => 'callLog'],
    'reports/employee-performance' => ['controller' => 'ReportController', 'action' => 'employeePerformance'],
    'reports/export-lead-status' => ['controller' => 'ReportController', 'action' => 'exportLeadStatus'],
    'reports/export-call-log' => ['controller' => 'ReportController', 'action' => 'exportCallLog'],
    'reports/export-employee-performance' => ['controller' => 'ReportController', 'action' => 'exportEmployeePerformance']
];

// Get route from URL
$route = isset($_GET['route']) ? $_GET['route'] : 'dashboard';

// Check if route exists
if (isset($routes[$route])) {
    $controllerName = $routes[$route]['controller'];
    $actionName = $routes[$route]['action'];
    
    // Include controller
    require_once 'controllers/' . $controllerName . '.php';
    
    // Instantiate controller
    $controller = new $controllerName();
    
    // Call action
    $controller->$actionName();
} else {
    // Route not found
    header('HTTP/1.0 404 Not Found');
    echo '404 - Page not found';
    exit;
}

