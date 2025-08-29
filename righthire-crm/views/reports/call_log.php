<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Call Log Report</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/reports" class="btn btn-sm btn-secondary me-2">
            <i class="fas fa-arrow-left"></i> Back to Reports
        </a>
        <a href="<?php echo APP_URL; ?>/reports/export-call-log<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-sm btn-success">
            <i class="fas fa-file-export"></i> Export to CSV
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Filter Report</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo APP_URL; ?>/reports/call-log">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="text" class="form-control datepicker" id="date_from" name="date_from" value="<?php echo isset($filters['date_from']) ? htmlspecialchars($filters['date_from']) : ''; ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="text" class="form-control datepicker" id="date_to" name="date_to" value="<?php echo isset($filters['date_to']) ? htmlspecialchars($filters['date_to']) : ''; ?>">
                </div>
                <?php if (hasRole('administrator') && isset($employees)): ?>
                    <div class="col-md-4 mb-3">
                        <label for="user_id" class="form-label">Employee</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="">All Employees</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?php echo $employee['id']; ?>" <?php echo isset($filters['user_id']) && $filters['user_id'] == $employee['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($employee['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="<?php echo APP_URL; ?>/reports/call-log" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-4">
        <div class="card card-dashboard card-new h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-muted mb-0">Total Calls</h6>
                        <h2 class="mt-2 mb-0"><?php echo $stats['total_calls']; ?></h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="fas fa-phone-alt text-primary fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card card-dashboard card-follow-up h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-muted mb-0">Follow-ups</h6>
                        <h2 class="mt-2 mb-0"><?php echo $stats['follow_ups']; ?></h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="fas fa-calendar-alt text-warning fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card card-dashboard card-interested h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-muted mb-0">Interested</h6>
                        <h2 class="mt-2 mb-0"><?php echo $stats['interested']; ?></h2>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded">
                        <i class="fas fa-thumbs-up text-info fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card card-dashboard card-win h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-muted mb-0">Wins</h6>
                        <h2 class="mt-2 mb-0"><?php echo $stats['wins']; ?></h2>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="fas fa-trophy text-success fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Call Logs</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Lead</th>
                        <th>Status</th>
                        <th>Other Reason</th>
                        <th>Follow-up Date</th>
                        <th>Remarks</th>
                        <th>Created By</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($callLogs as $callLog): ?>
                        <tr>
                            <td><?php echo $callLog['id']; ?></td>
                            <td>
                                <a href="<?php echo APP_URL; ?>/leads/view?id=<?php echo $callLog['lead_id']; ?>">
                                    <?php echo htmlspecialchars($callLog['lead_name']); ?>
                                </a>
                            </td>
                            <td><?php echo getStatusBadge($callLog['status']); ?></td>
                            <td><?php echo $callLog['other_reason'] ? htmlspecialchars($callLog['other_reason']) : '-'; ?></td>
                            <td><?php echo $callLog['follow_up_date'] ? formatDateTime($callLog['follow_up_date']) : '-'; ?></td>
                            <td><?php echo $callLog['remarks'] ? htmlspecialchars($callLog['remarks']) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($callLog['created_by_name']); ?></td>
                            <td><?php echo formatDateTime($callLog['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <?php echo getPaginationLinks($page, $totalPages, APP_URL . '/reports/call-log' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] . '&' : '?')); ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>

