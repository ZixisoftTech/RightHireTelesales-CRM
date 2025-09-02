<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Leads</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/leads/create" class="btn btn-sm btn-primary me-2">
            <i class="fas fa-plus"></i> Add New Lead
        </a>
        <?php if (hasRole('administrator')): ?>
            <a href="<?php echo APP_URL; ?>/leads/import" class="btn btn-sm btn-success me-2">
                <i class="fas fa-file-import"></i> Import Leads
            </a>
        <?php endif; ?>
        <a href="<?php echo APP_URL; ?>/leads/export<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-file-export"></i> Export Leads
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Filter Leads</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo APP_URL; ?>/leads">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="new" <?php echo isset($filters['status']) && $filters['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="not_attend" <?php echo isset($filters['status']) && $filters['status'] === 'not_attend' ? 'selected' : ''; ?>>Not Attend</option>
                        <option value="wrong_number" <?php echo isset($filters['status']) && $filters['status'] === 'wrong_number' ? 'selected' : ''; ?>>Wrong Number</option>
                        <option value="interested" <?php echo isset($filters['status']) && $filters['status'] === 'interested' ? 'selected' : ''; ?>>Interested</option>
                        <option value="won" <?php echo isset($filters['status']) && $filters['status'] === 'won' ? 'selected' : ''; ?>>Won</option>
                        <option value="lost" <?php echo isset($filters['status']) && $filters['status'] === 'lost' ? 'selected' : ''; ?>>Lost</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="state_id" class="form-label">State</label>
                    <select class="form-select" id="state_id" name="state_id">
                        <option value="">All States</option>
                        <?php foreach ($states as $state): ?>
                            <option value="<?php echo $state['id']; ?>" <?php echo isset($filters['state_id']) && $filters['state_id'] == $state['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($state['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="city_id" class="form-label">City</label>
                    <select class="form-select" id="city_id" name="city_id">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo $city['id']; ?>" <?php echo isset($filters['city_id']) && $filters['city_id'] == $city['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($city['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (hasRole('administrator') && isset($employees)): ?>
                    <div class="col-md-3 mb-3">
                        <label for="assigned_to" class="form-label">Assigned To</label>
                        <select class="form-select" id="assigned_to" name="assigned_to">
                            <option value="">All Employees</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?php echo $employee['id']; ?>" <?php echo isset($filters['assigned_to']) && $filters['assigned_to'] == $employee['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($employee['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="col-md-3 mb-3">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="text" class="form-control datepicker" id="date_from" name="date_from" value="<?php echo isset($filters['date_from']) ? htmlspecialchars($filters['date_from']) : ''; ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="text" class="form-control datepicker" id="date_to" name="date_to" value="<?php echo isset($filters['date_to']) ? htmlspecialchars($filters['date_to']) : ''; ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Name, Email, Phone" value="<?php echo isset($filters['search']) ? htmlspecialchars($filters['search']) : ''; ?>">
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="<?php echo APP_URL; ?>/leads" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>State</th>
                        <th>City</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leads as $lead): ?>
                        <tr>
                            <td><?php echo $lead['id']; ?></td>
                            <td><?php echo htmlspecialchars($lead['name']); ?></td>
                            <td><?php echo htmlspecialchars($lead['phone']); ?></td>
                            <td><?php echo htmlspecialchars($lead['state_name']); ?></td>
                            <td><?php echo htmlspecialchars($lead['city_name']); ?></td>
                            <td><?php echo getStatusBadge($lead['status']); ?></td>
                            <td><?php echo $lead['assigned_to_name'] ? htmlspecialchars($lead['assigned_to_name']) : '-'; ?></td>
                            <td><?php echo formatDateTime($lead['created_at']); ?></td>
                            <td>
                                <a href="<?php echo APP_URL; ?>/leads/view?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo APP_URL; ?>/leads/edit?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo APP_URL; ?>/leads/update-status?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Update Status">
                                    <i class="fas fa-phone-alt"></i>
                                </a>
                                <a href="<?php echo APP_URL; ?>/leads/delete?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this lead?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <?php echo getPaginationLinks($page, $totalPages, APP_URL . '/leads' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] . '&' : '?')); ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>
