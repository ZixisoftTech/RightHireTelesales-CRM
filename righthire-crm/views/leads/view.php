<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">View Lead</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/leads" class="btn btn-sm btn-secondary me-2">
            <i class="fas fa-arrow-left"></i> Back to Leads
        </a>
        <a href="<?php echo APP_URL; ?>/leads/edit?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-primary me-2">
            <i class="fas fa-edit"></i> Edit Lead
        </a>
        <a href="<?php echo APP_URL; ?>/leads/update-status?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-warning">
            <i class="fas fa-phone-alt"></i> Update Status
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Lead Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Status</h6>
                    <p class="mb-0"><?php echo getStatusBadge($lead['status']); ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Name</h6>
                    <p class="mb-0"><?php echo htmlspecialchars($lead['name']); ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Email</h6>
                    <p class="mb-0"><?php echo $lead['email'] ? htmlspecialchars($lead['email']) : '-'; ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Phone</h6>
                    <p class="mb-0"><?php echo htmlspecialchars($lead['phone']); ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Address</h6>
                    <p class="mb-0"><?php echo $lead['address'] ? htmlspecialchars($lead['address']) : '-'; ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-1">State</h6>
                    <p class="mb-0"><?php echo htmlspecialchars($lead['state_name']); ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-1">City</h6>
                    <p class="mb-0"><?php echo htmlspecialchars($lead['city_name']); ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Assigned To</h6>
                    <p class="mb-0"><?php echo $lead['assigned_to_name'] ? htmlspecialchars($lead['assigned_to_name']) : '-'; ?></p>
                </div>
                <?php if ($lead['status'] === 'follow_up' && $lead['follow_up_date']): ?>
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Follow-up Date</h6>
                        <p class="mb-0"><?php echo formatDateTime($lead['follow_up_date']); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($lead['status'] === 'other' && $lead['other_reason']): ?>
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Other Reason</h6>
                        <p class="mb-0"><?php echo htmlspecialchars($lead['other_reason']); ?></p>
                    </div>
                <?php endif; ?>
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Remarks</h6>
                    <p class="mb-0"><?php echo $lead['remarks'] ? nl2br(htmlspecialchars($lead['remarks'])) : '-'; ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Created By</h6>
                    <p class="mb-0"><?php echo $lead['created_by'] ? htmlspecialchars($lead['created_by']) : '-'; ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Created At</h6>
                    <p class="mb-0"><?php echo formatDateTime($lead['created_at']); ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Updated At</h6>
                    <p class="mb-0"><?php echo $lead['updated_at'] ? formatDateTime($lead['updated_at']) : '-'; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Call History</h5>
            </div>
            <div class="card-body">
                <?php if (empty($callLogs)): ?>
                    <p class="text-muted">No call history available.</p>
                <?php else: ?>
                    <?php foreach ($callLogs as $callLog): ?>
                        <div class="call-log call-log-<?php echo $callLog['status']; ?> mb-3">
                            <div class="d-flex justify-content-between">
                                <h6><?php echo getStatusBadge($callLog['status']); ?></h6>
                                <small class="text-muted"><?php echo formatDateTime($callLog['created_at']); ?></small>
                            </div>
                            <?php if ($callLog['status'] === 'follow_up' && $callLog['follow_up_date']): ?>
                                <div class="mb-2">
                                    <strong>Follow-up Date:</strong> <?php echo formatDateTime($callLog['follow_up_date']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($callLog['status'] === 'other' && $callLog['other_reason']): ?>
                                <div class="mb-2">
                                    <strong>Other Reason:</strong> <?php echo htmlspecialchars($callLog['other_reason']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($callLog['remarks']): ?>
                                <div class="mb-2">
                                    <strong>Remarks:</strong> <?php echo nl2br(htmlspecialchars($callLog['remarks'])); ?>
                                </div>
                            <?php endif; ?>
                            <div class="text-muted">
                                <small>By: <?php echo htmlspecialchars($callLog['created_by_name']); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="<?php echo APP_URL; ?>/leads/update-status?id=<?php echo $lead['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-phone-alt"></i> Log New Call
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>

