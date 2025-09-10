<?php
/**
 * Helper Functions
 * 
 * This file contains all the helper functions used throughout the application.
 */

/**
 * Redirect to a URL
 * 
 * @param string $url
 * @return void
 */
function redirect($url) {
    header('Location: ' . APP_URL . '/' . $url);
    exit;
}

/**
 * Sanitize input
 * 
 * @param string $input
 * @return string
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format date
 * 
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = DISPLAY_DATE_FORMAT) {
    if (!$date) return '';
    
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

/**
 * Format datetime
 * 
 * @param string $datetime
 * @param string $format
 * @return string
 */
function formatDateTime($datetime, $format = DISPLAY_DATETIME_FORMAT) {
    if (!$datetime) return '';
    
    $dateObj = new DateTime($datetime);
    return $dateObj->format($format);
}

/**
 * Generate CSRF token
 * 
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !$token) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set flash message
 * 
 * @param string $type
 * @param string $message
 * @return void
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get flash message
 * 
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    
    return null;
}

/**
 * Display flash message
 * 
 * @return string
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    
    if (!$flash) {
        return '';
    }
    
    $type = $flash['type'];
    $message = $flash['message'];
    
    $alertClass = 'alert-info';
    
    if ($type === 'success') {
        $alertClass = 'alert-success';
    } elseif ($type === 'error') {
        $alertClass = 'alert-danger';
    } elseif ($type === 'warning') {
        $alertClass = 'alert-warning';
    }
    
    return '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

/**
 * Get pagination links
 * 
 * @param int $currentPage
 * @param int $totalPages
 * @param string $baseUrl
 * @return string
 */
function getPaginationLinks($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $links = '<nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">';
    
    // Previous link
    if ($currentPage > 1) {
        $links .= '<li class="page-item">
                    <a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>';
    } else {
        $links .= '<li class="page-item disabled">
                    <a class="page-link" href="#" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>';
    }
    
    // Page links
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);
    
    if ($startPage > 1) {
        $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=1">1</a></li>';
        if ($startPage > 2) {
            $links .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $currentPage) {
            $links .= '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
        } else {
            $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $links .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
        $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $totalPages . '">' . $totalPages . '</a></li>';
    }
    
    // Next link
    if ($currentPage < $totalPages) {
        $links .= '<li class="page-item">
                    <a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>';
    } else {
        $links .= '<li class="page-item disabled">
                    <a class="page-link" href="#" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>';
    }
    
    $links .= '</ul>
            </nav>';
    
    return $links;
}

/**
 * Get status badge
 * 
 * @param string $status
 * @return string
 */
function getStatusBadge($status) {
    $badgeClass = 'bg-secondary';
    $statusText = ucfirst(str_replace('_', ' ', $status));
    
    switch ($status) {
        case 'new':
            $badgeClass = 'bg-primary';
            break;
        case 'not_attend':
            $badgeClass = 'bg-secondary';
            break;
        case 'wrong_number':
            $badgeClass = 'bg-danger';
            break;
        case 'interested':
            $badgeClass = 'bg-info';
            break;
        case 'in_dealing':
            $badgeClass = 'bg-in-dealing';
            break;
        case 'won':
            $badgeClass = 'bg-success';
            break;
        case 'lost':
            $badgeClass = 'bg-lost';
            break;
    }
    
    return '<span class="badge ' . $badgeClass . '">' . $statusText . '</span>';
}

/**
 * Export data to CSV
 * 
 * @param array $data
 * @param array $headers
 * @param string $filename
 * @return void
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

/**
 * Check if user has role
 * 
 * @param string $role
 * @return bool
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Check if user has access to territory
 * 
 * @param int $stateId
 * @param int $cityId
 * @return bool
 */
function hasAccessToTerritory($stateId, $cityId = null) {
    // Administrators have access to all territories
    if (hasRole('administrator')) {
        return true;
    }
    
    // Check if user has access to the territory
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $userId = $_SESSION['user_id'];
    
    // Get database instance
    $db = Database::getInstance();
    
    // Check if user has access to the state
    $sql = "SELECT COUNT(*) as count FROM employee_territories 
            WHERE user_id = :user_id AND state_id = :state_id AND deleted_at IS NULL";
    
    $params = [
        'user_id' => $userId,
        'state_id' => $stateId
    ];
    
    // If city ID is provided, check if user has access to the city
    if ($cityId) {
        $sql = "SELECT COUNT(*) as count FROM employee_territories 
                WHERE user_id = :user_id 
                AND state_id = :state_id 
                AND (city_id IS NULL OR city_id = :city_id)
                AND deleted_at IS NULL";
        
        $params['city_id'] = $cityId;
    }
    
    $result = $db->fetch($sql, $params);
    
    return $result['count'] > 0;
}
