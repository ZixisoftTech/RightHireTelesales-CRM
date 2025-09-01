<?php
/**
 * Update lead status workflow
 * 
 * This update implements the lead status workflow with specific business rules
 * for each status type.
 */

// Update LeadController.php - updateStatus method
public function updateStatus() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        redirect('auth/login');
        exit;
    }
    
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        setFlashMessage('error', 'Lead ID is required');
        redirect('leads');
        exit;
    }
    
    $id = (int)$_GET['id'];
    
    // Get lead
    $lead = $this->leadModel->getLeadById($id);
    
    if (!$lead) {
        setFlashMessage('error', 'Lead not found');
        redirect('leads');
        exit;
    }
    
    // Check if user has access to this lead
    if (!hasRole('administrator') && $lead['assigned_to'] != $_SESSION['user_id']) {
        // Check if lead is in user's territory
        $hasAccess = $this->leadModel->checkLeadAccess($id, $_SESSION['user_id']);
        
        if (!$hasAccess) {
            setFlashMessage('error', 'You do not have access to this lead');
            redirect('leads');
            exit;
        }
    }
    
    // Check if form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize input
        $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : '';
        $region = isset($_POST['region']) ? sanitizeInput($_POST['region']) : '';
        $follow_up_date = isset($_POST['follow_up_date']) ? sanitizeInput($_POST['follow_up_date']) : '';
        $remarks = isset($_POST['remarks']) ? sanitizeInput($_POST['remarks']) : '';
        
        // Validate input
        $errors = [];
        
        if (empty($status)) {
            $errors[] = 'Status is required';
        }
        
        // Status-specific validation
        if ($status === 'follow_up' && empty($follow_up_date)) {
            $errors[] = 'Follow-up date is required for Follow-up status';
        }
        
        if ($status === 'interested' && empty($follow_up_date)) {
            $errors[] = 'Follow-up date is required for Interested status';
        }
        
        if (in_array($status, ['not_attend', 'wrong_number']) && empty($remarks)) {
            $errors[] = 'Remarks are required for ' . ucwords(str_replace('_', ' ', $status)) . ' status';
        }
        
        if ($status === 'dead' && empty($region)) {
            $errors[] = 'Region is required for Lost status';
        }
        
        // If no errors, update lead status
        if (empty($errors)) {
            try {
                // Start transaction
                $db = Database::getInstance();
                $db->beginTransaction();
                
                // Update lead status
                $leadData = [
                    'status' => $status,
                    'remarks' => $remarks,
                    'region' => $status === 'dead' ? $region : null,
                    'follow_up_date' => in_array($status, ['follow_up', 'interested']) ? $follow_up_date : null,
                    'updated_by' => getCurrentUserId()
                ];
                
                $result = $this->leadModel->update($id, $leadData);
                
                if ($result) {
                    // Create call log
                    $callLogData = [
                        'lead_id' => $id,
                        'status' => $status,
                        'remarks' => $remarks,
                        'follow_up_date' => in_array($status, ['follow_up', 'interested']) ? $follow_up_date : null,
                        'created_by' => getCurrentUserId()
                    ];
                    
                    $this->callLogModel->create($callLogData);
                    
                    // Handle status-specific actions
                    if (in_array($status, ['not_attend', 'wrong_number'])) {
                        // Auto-create follow-up for next day at 10:00 AM (except Sundays)
                        $nextDay = new DateTime('tomorrow');
                        
                        // If next day is Sunday, set to Monday
                        if ($nextDay->format('w') == 0) { // 0 = Sunday
                            $nextDay->modify('+1 day'); // Move to Monday
                        }
                        
                        $nextFollowUpDate = $nextDay->format('Y-m-d') . ' 10:00:00';
                        
                        // Update lead status to follow_up
                        $followUpData = [
                            'status' => 'follow_up',
                            'follow_up_date' => $nextFollowUpDate,
                            'remarks' => 'Auto-generated follow-up for ' . ucwords(str_replace('_', ' ', $status)),
                            'updated_by' => getCurrentUserId()
                        ];
                        
                        $this->leadModel->update($id, $followUpData);
                        
                        // Create call log for follow-up
                        $followUpLogData = [
                            'lead_id' => $id,
                            'status' => 'follow_up',
                            'remarks' => 'Auto-generated follow-up for ' . ucwords(str_replace('_', ' ', $status)),
                            'follow_up_date' => $nextFollowUpDate,
                            'created_by' => getCurrentUserId()
                        ];
                        
                        $this->callLogModel->create($followUpLogData);
                    }
                    
                    // Commit transaction
                    $db->commit();
                    
                    setFlashMessage('success', 'Lead status updated successfully');
                    redirect('leads/view?id=' . $id);
                    exit;
                } else {
                    // Rollback transaction
                    $db->rollBack();
                    
                    $errors[] = 'Failed to update lead status';
                }
            } catch (Exception $e) {
                // Rollback transaction
                $db->rollBack();
                
                $errors[] = 'Error: ' . $e->getMessage();
            }
        }
    }
    
    // Include view
    include 'views/leads/update_status.php';
}

