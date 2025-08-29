<?php
/**
 * Right Hire CRM
 * 
 * Main entry point for the application.
 */

// Load configuration
require_once 'config/config.php';

// Load includes
require_once 'includes/session.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Get route and action from URL
$route = isset($_GET['route']) ? $_GET['route'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Security check and authorization
if (!isLoggedIn() && $route != 'auth') {
    header('Location: index.php?route=auth&action=login');
    exit;
}

// Route to appropriate controller
switch ($route) {
    case 'auth':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        break;
    
    case 'users':
        require_once 'controllers/UserController.php';
        $controller = new UserController();
        
        // Check if administrator for user management
        if ($action != 'profile' && !hasRole('administrator')) {
            setFlashMessage('error', 'You do not have permission to access user management');
            redirect('dashboard');
            exit;
        }
        break;
    
    case 'states':
        require_once 'controllers/StateController.php';
        $controller = new StateController();
        
        // Check if administrator for state management
        if (!hasRole('administrator')) {
            setFlashMessage('error', 'You do not have permission to access state management');
            redirect('dashboard');
            exit;
        }
        break;
    
    case 'cities':
        require_once 'controllers/CityController.php';
        $controller = new CityController();
        
        // Check if administrator for city management
        if (!hasRole('administrator')) {
            setFlashMessage('error', 'You do not have permission to access city management');
            redirect('dashboard');
            exit;
        }
        break;
    
    case 'leads':
        require_once 'controllers/LeadController.php';
        $controller = new LeadController();
        break;
    
    case 'import':
        require_once 'controllers/ImportExportController.php';
        $controller = new ImportExportController();
        
        // Check if administrator for import/export
        if (!hasRole('administrator')) {
            setFlashMessage('error', 'You do not have permission to access import/export');
            redirect('dashboard');
            exit;
        }
        break;
    
    case 'export':
        require_once 'controllers/ImportExportController.php';
        $controller = new ImportExportController();
        break;
    
    case 'api':
        // Handle API requests
        header('Content-Type: application/json');
        
        $apiRoute = isset($_GET['api_route']) ? $_GET['api_route'] : '';
        
        switch ($apiRoute) {
            case 'cities':
                require_once 'api/cities.php';
                break;
            
            case 'leads':
                require_once 'api/leads.php';
                break;
            
            case 'users':
                require_once 'api/users.php';
                break;
            
            case 'states':
                require_once 'api/states.php';
                break;
            
            default:
                echo json_encode(['error' => 'Invalid API route']);
                exit;
        }
        break;
    
    default:
        require_once 'controllers/DashboardController.php';
        $controller = new DashboardController();
        $route = 'dashboard';
}

// Call the appropriate method
if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    // Default to index if method doesn't exist
    $controller->index();
}

