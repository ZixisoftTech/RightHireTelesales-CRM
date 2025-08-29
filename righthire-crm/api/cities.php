<?php
/**
 * Cities API
 * 
 * This file handles API requests for cities.
 */

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Include City model
require_once 'models/City.php';
$cityModel = new City();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle GET requests
if ($method === 'GET') {
    // Get cities by state
    if (isset($_GET['state_id']) && !empty($_GET['state_id'])) {
        $stateId = (int)$_GET['state_id'];
        $cities = $cityModel->getActiveByState($stateId);
        
        echo json_encode(['cities' => $cities]);
        exit;
    }
    
    // Get all cities
    $cities = $cityModel->getAllActive();
    
    echo json_encode(['cities' => $cities]);
    exit;
}

// Handle POST requests
if ($method === 'POST') {
    // Check if administrator
    if (!hasRole('administrator')) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode(['error' => 'Invalid data']);
        exit;
    }
    
    // Create city
    $result = $cityModel->create($data);
    
    if ($result) {
        echo json_encode(['success' => true, 'id' => $result]);
    } else {
        echo json_encode(['error' => 'Failed to create city']);
    }
    
    exit;
}

// Handle PUT requests
if ($method === 'PUT') {
    // Check if administrator
    if (!hasRole('administrator')) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    // Get PUT data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'])) {
        echo json_encode(['error' => 'Invalid data']);
        exit;
    }
    
    $id = (int)$data['id'];
    unset($data['id']);
    
    // Update city
    $result = $cityModel->update($id, $data);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to update city']);
    }
    
    exit;
}

// Handle DELETE requests
if ($method === 'DELETE') {
    // Check if administrator
    if (!hasRole('administrator')) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    // Get city ID from URL
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$id) {
        echo json_encode(['error' => 'Invalid city ID']);
        exit;
    }
    
    // Check if city has leads
    if ($cityModel->hasLeads($id)) {
        echo json_encode(['error' => 'Cannot delete city with associated leads']);
        exit;
    }
    
    // Delete city
    $result = $cityModel->delete($id);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to delete city']);
    }
    
    exit;
}

// If we get here, the request method is not supported
echo json_encode(['error' => 'Method not allowed']);
exit;

