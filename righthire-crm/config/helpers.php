<?php
/**
 * Helper Functions
 * 
 * This file contains helper functions used throughout the application.
 */

// Define base path constant for consistent file includes
define('BASE_PATH', dirname(__DIR__));
define('VIEWS_PATH', BASE_PATH . '/views');

/**
 * Sanitize input
 */
function sanitizeInput($input) {
    if ($input === null) {
        return '';
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format date and time
 */
function formatDateTime($dateTime) {
    return date('M d, Y h:i A', strtotime($dateTime));
}

/**
 * Format date
 */
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Display flash message
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_message']['type'];
        $message = $_SESSION['flash_message']['message'];
        
        $alertClass = 'alert-info';
        
        if ($type === 'success') {
            $alertClass = 'alert-success';
        } elseif ($type === 'error') {
            $alertClass = 'alert-danger';
        } elseif ($type === 'warning') {
            $alertClass = 'alert-warning';
        }
        
        $html = '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        $html .= $message;
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        $html .= '</div>';
        
        unset($_SESSION['flash_message']);
        
        return $html;
    }
    
    return '';
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header('Location: ' . APP_URL . '/' . $url);
    exit;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'You must be logged in to access this page');
        redirect('auth/login');
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireLogin();
    
    if (!hasRole('administrator')) {
        setFlashMessage('error', 'You do not have permission to access this page');
        redirect('dashboard');
    }
}

/**
 * Check if user has a specific role
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['role'] === $role;
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return isLoggedIn() ? $_SESSION['user_id'] : null;
}

/**
 * Get current user name
 */
function getCurrentUserName() {
    return isLoggedIn() ? $_SESSION['user_name'] : null;
}

/**
 * Get current user email
 */
function getCurrentUserEmail() {
    return isLoggedIn() ? $_SESSION['user_email'] : null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return isLoggedIn() ? $_SESSION['role'] : null;
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status) {
    $badgeClass = 'bg-primary';
    $statusText = ucfirst(str_replace('_', ' ', $status));
    
    switch ($status) {
        case 'new':
            $badgeClass = 'bg-primary';
            break;
        case 'follow_up':
            $badgeClass = 'bg-warning';
            break;
        case 'not_attend':
            $badgeClass = 'bg-secondary';
            break;
        case 'wrong_number':
            $badgeClass = 'bg-danger';
            break;
        case 'other':
            $badgeClass = 'bg-secondary';
            break;
        case 'dead':
            $badgeClass = 'bg-danger';
            break;
        case 'interested':
            $badgeClass = 'bg-info';
            break;
        case 'win':
            $badgeClass = 'bg-success';
            break;
    }
    
    return '<span class="badge ' . $badgeClass . '">' . $statusText . '</span>';
}

/**
 * Get pagination links
 */
function getPaginationLinks($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation">';
    $html .= '<ul class="pagination justify-content-center">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $baseUrl . 'page=' . ($currentPage - 1) . '" aria-label="Previous">';
        $html .= '<span aria-hidden="true">&laquo;</span>';
        $html .= '</a>';
        $html .= '</li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<a class="page-link" href="#" aria-label="Previous">';
        $html .= '<span aria-hidden="true">&laquo;</span>';
        $html .= '</a>';
        $html .= '</li>';
    }
    
    // Page numbers
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);
    
    if ($startPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=1">1</a></li>';
        if ($startPage > 2) {
            $html .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i === $currentPage) {
            $html .= '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=' . $totalPages . '">' . $totalPages . '</a></li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $baseUrl . 'page=' . ($currentPage + 1) . '" aria-label="Next">';
        $html .= '<span aria-hidden="true">&raquo;</span>';
        $html .= '</a>';
        $html .= '</li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<a class="page-link" href="#" aria-label="Next">';
        $html .= '<span aria-hidden="true">&raquo;</span>';
        $html .= '</a>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Export data to CSV
 */
function exportToCSV($data, $headers, $filename) {
    // Set headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add headers
    fputcsv($output, $headers);
    
    // Add data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    // Close output stream
    fclose($output);
    exit;
}
