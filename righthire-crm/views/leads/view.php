<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Lead Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/leads" class="btn btn-sm btn-secondary me-2">
            <i class="fas fa-arrow-left"></i> Back to Leads
        </a>
        <?php if (hasRole('administrator')): ?>
            <a href="<?php echo APP_URL; ?>/leads/edit?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-primary me-2">
                <i class="fas fa-edit"></i> Edit Lead
            </a>
        <?php endif; ?>
        <?php if (hasRole('administrator') || $lead['assigned_to'] == $_SESSION['user_id']): ?>
            <a href="<?php echo APP_URL; ?>/leads/update-status?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-success">
                <i class="fas fa-phone"></i> Update Status
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Lead Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <h6 class="text-muted mb-1">Name</h6>
                        <p class="mb-0"><?php echo htmlspecialchars($lead['name']); ?></p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-1">Phone</h6>
                        <p class="mb-0"><?php echo htmlspecialchars($lead['phone']); ?></p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-1">Email</h6>
                        <p class="mb-0"><?php echo $lead['email'] ? htmlspecialchars($lead['email']) : '-'; ?></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <h6 class="text-muted mb-1">Address</h6>
                        <p class="mb-0"><?php echo $lead['address'] ? htmlspecialchars($lead['address']) : '-'; ?></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">State</h6>
                        <p class="mb-0"><?php echo htmlspecialchars($lead['state_name']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">City</h6>
                        <p class="mb-0"><?php echo $lead['city_name'] ? htmlspecialchars($lead['city_name']) : '-'; ?></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Status</h6>
                        <p class="mb-0"><?php echo getStatusBadge($lead['status']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Assigned To</h6>
                        <p class="mb-0"><?php echo $lead['assigned_to_name'] ? htmlspecialchars($lead['assigned_to_name']) : 'Not Assigned'; ?></p>
                    </div>
                </div>
                <?php if ($lead['status'] === 'interested' && !empty($lead['follow_up_date'])): ?>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <h6 class="text-muted mb-1">Follow-up Date</h6>
                        <p class="mb-0"><?php echo formatDateTime($lead['follow_up_date']); ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($lead['status'] === 'lost' && !empty($lead['region'])): ?>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <h6 class="text-muted mb-1">Region</h6>
                        <p class="mb-0"><?php echo htmlspecialchars($lead['region']); ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Created At</h6>
                        <p class="mb-0"><?php echo formatDateTime($lead['created_at']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Updated At</h6>
                        <p class="mb-0"><?php echo $lead['updated_at'] ? formatDateTime($lead['updated_at']) : '-'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Call History</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($callLogs)): ?>
                    <div class="p-3 text-center">
                        <p class="text-muted mb-0">No call logs found</p>
                    </div>
                <?php else: ?>
                    <div class="timeline p-3">
                        <?php foreach ($callLogs as $index => $log): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker <?php echo getStatusColor($log['status']); ?>"></div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1"><?php echo getStatusLabel($log['status']); ?></h6>
                                        <small class="text-muted"><?php echo formatDateTime($log['created_at']); ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($log['remarks']); ?></p>
                                    <?php if (!empty($log['follow_up_date'])): ?>
                                        <small class="text-primary">
                                            <i class="fas fa-calendar-alt"></i> 
                                            Follow-up: <?php echo formatDateTime($log['follow_up_date']); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (hasRole('administrator') || $lead['assigned_to'] == $_SESSION['user_id']): ?>
                <?php if ($lead['status'] !== 'won' && $lead['status'] !== 'lost'): ?>
                    <div class="card-footer bg-light">
                        <a href="<?php echo APP_URL; ?>/leads/update-status?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-success w-100">
                            <i class="fas fa-phone"></i> Add New Call Log
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    max-height: 400px;
    overflow-y: auto;
}
.timeline-item {
    position: relative;
    padding-left: 30px;
    margin-bottom: 20px;
}
.timeline-marker {
    position: absolute;
    left: 0;
    top: 5px;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background-color: #007bff;
}
.timeline-marker.bg-success {
    background-color: #28a745;
}
.timeline-marker.bg-warning {
    background-color: #ffc107;
}
.timeline-marker.bg-danger {
    background-color: #dc3545;
}
.timeline-marker.bg-info {
    background-color: #17a2b8;
}
.timeline-marker.bg-secondary {
    background-color: #6c757d;
}
.timeline-content {
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}
.timeline-item:last-child .timeline-content {
    border-bottom: none;
    padding-bottom: 0;
}
</style>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'new':
            return 'bg-primary';
        case 'interested':
            return 'bg-success';
        case 'not_attend':
            return 'bg-warning';
        case 'wrong_number':
            return 'bg-danger';
        case 'lost':
            return 'bg-secondary';
        case 'won':
            return 'bg-info';
        default:
            return 'bg-secondary';
    }
}

function getStatusLabel($status) {
    switch ($status) {
        case 'new':
            return 'New';
        case 'interested':
            return 'Interested';
        case 'not_attend':
            return 'Not Attend';
        case 'wrong_number':
            return 'Wrong Number';
        case 'lost':
            return 'Lost';
        case 'won':
            return 'Won';
        default:
            return ucfirst($status);
    }
}
?>

<?php include 'views/templates/footer.php'; ?>
