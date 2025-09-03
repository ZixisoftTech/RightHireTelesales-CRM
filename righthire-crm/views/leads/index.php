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
            <a href="<?php echo APP_URL; ?>/leads/export<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-file-export"></i> Export Leads
            </a>
        <?php endif; ?>
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
                            <td>
                                <?php 
                                if (isset($lead['assigned_to']) && $lead['assigned_to']) {
                                    echo isset($lead['assigned_to_name']) && $lead['assigned_to_name'] ? htmlspecialchars($lead['assigned_to_name']) : 'ID: ' . $lead['assigned_to'];
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td><?php echo formatDateTime($lead['created_at']); ?></td>
                            <td>
                                <a href="<?php echo APP_URL; ?>/leads/view?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (hasRole('administrator')): ?>
                                <a href="<?php echo APP_URL; ?>/leads/edit?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo APP_URL; ?>/leads/update-status?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Update Status">
                                    <i class="fas fa-phone-alt"></i>
                                </a>
                                <?php if (hasRole('administrator')): ?>
                                <a href="<?php echo APP_URL; ?>/leads/delete?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this lead?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (empty($leads) && !hasRole('administrator')): ?>
            <div class="alert alert-info mt-3">
                <strong>Debug Info:</strong> You are logged in as an employee. 
                <?php
                $userId = getCurrentUserId();
                // Ensure database is initialized
                if (!isset($GLOBALS['db'])) {
                    require_once 'models/Database.php';
                    $GLOBALS['db'] = Database::getInstance();
                }
                $territories = $GLOBALS['db']->getRows(
                    "SELECT et.id, s.name as state, c.name as city, s.id as state_id, c.id as city_id
                    FROM employee_territories et
                    LEFT JOIN states s ON et.state_id = s.id
                    LEFT JOIN cities c ON et.city_id = c.id
                    WHERE et.user_id = ? AND et.deleted_at IS NULL", 
                    [$userId]
                );
                
                if (empty($territories)) {
                    echo "You have no territories assigned. Please contact an administrator.";
                } else {
                    echo "Your assigned territories: <ul>";
                    foreach ($territories as $t) {
                        echo "<li>" . htmlspecialchars($t['state']) . 
                             (isset($t['city']) ? " / " . htmlspecialchars($t['city']) : " (All Cities)") . "</li>";
                    }
                    echo "</ul>";
                    
                    // Check if there are any leads in these territories
                    $territoryLeads = 0;
                    $assignedLeads = 0;
                    
                    foreach ($territories as $t) {
                        $stateId = $t['state_id'];
                        $cityId = isset($t['city_id']) ? $t['city_id'] : null;
                        
                        if ($stateId) {
                            // Count all leads in this territory
                            $count = $GLOBALS['db']->getValue(
                                "SELECT COUNT(*) FROM leads 
                                WHERE state_id = ? " . 
                                ($cityId ? "AND city_id = ? " : "") . 
                                "AND deleted_at IS NULL", 
                                $cityId ? [$stateId, $cityId] : [$stateId]
                            );
                            $territoryLeads += $count;
                            
                            // Count leads assigned to this user
                            $assignedCount = $GLOBALS['db']->getValue(
                                "SELECT COUNT(*) FROM leads 
                                WHERE state_id = ? " . 
                                ($cityId ? "AND city_id = ? " : "") . 
                                "AND assigned_to = ? AND deleted_at IS NULL", 
                                $cityId ? [$stateId, $cityId, $userId] : [$stateId, $userId]
                            );
                            $assignedLeads += $assignedCount;
                        }
                    }
                    
                    echo "<p>Total leads in your territories: " . $territoryLeads . "</p>";
                    echo "<p>Leads assigned directly to you: " . $assignedLeads . "</p>";
                    
                    // Check for unassigned leads in territories
                    $unassignedLeads = 0;
                    foreach ($territories as $t) {
                        $stateId = $t['state_id'];
                        $cityId = isset($t['city_id']) ? $t['city_id'] : null;
                        
                        if ($stateId) {
                            $count = $GLOBALS['db']->getValue(
                                "SELECT COUNT(*) FROM leads 
                                WHERE state_id = ? " . 
                                ($cityId ? "AND city_id = ? " : "") . 
                                "AND assigned_to IS NULL AND deleted_at IS NULL", 
                                $cityId ? [$stateId, $cityId] : [$stateId]
                            );
                            $unassignedLeads += $count;
                        }
                    }
                    
                    echo "<p>Unassigned leads in your territories: " . $unassignedLeads . "</p>";
                    
                    if ($territoryLeads > 0) {
                        echo "<p>However, no leads match your current filter criteria. Try adjusting the filters above.</p>";
                        echo "<p><strong>Note:</strong> If you're seeing this message but have territories assigned, the system will automatically assign leads to you based on your territories. Refresh the page to see your assigned leads.</p>";
                    }
                }
                ?>
            </div>
            <?php endif; ?>
            
            <?php if (hasRole('administrator')): ?>
            <div class="alert alert-info mt-3">
                <strong>Territory Assignment Debug:</strong>
                <?php
                // Get all territories
                $allTerritories = $GLOBALS['db']->getRows(
                    "SELECT et.id, u.name as employee, s.name as state, c.name as city, s.id as state_id, c.id as city_id
                    FROM employee_territories et
                    JOIN users u ON et.user_id = u.id
                    JOIN states s ON et.state_id = s.id
                    LEFT JOIN cities c ON et.city_id = c.id
                    WHERE et.deleted_at IS NULL
                    ORDER BY u.name, s.name, c.name"
                );
                
                if (empty($allTerritories)) {
                    echo "<p>No territories assigned to any employees.</p>";
                } else {
                    echo "<p>Territory assignments:</p><ul>";
                    $currentEmployee = '';
                    foreach ($allTerritories as $t) {
                        if ($currentEmployee != $t['employee']) {
                            if ($currentEmployee != '') {
                                echo "</ul></li>";
                            }
                            echo "<li><strong>" . htmlspecialchars($t['employee']) . "</strong>: <ul>";
                            $currentEmployee = $t['employee'];
                        }
                        echo "<li>" . htmlspecialchars($t['state']) . 
                             (isset($t['city']) ? " / " . htmlspecialchars($t['city']) : " (All Cities)") . "</li>";
                    }
                    echo "</ul></li></ul>";
                    
                    // Count unassigned leads
                    $unassignedLeads = $GLOBALS['db']->getValue(
                        "SELECT COUNT(*) FROM leads WHERE assigned_to IS NULL AND deleted_at IS NULL"
                    );
                    
                    echo "<p>Total unassigned leads: " . $unassignedLeads . "</p>";
                    
                    if ($unassignedLeads > 0) {
                        echo "<p><a href='#' onclick='runAutoAssign(); return false;' class='btn btn-sm btn-primary'>Run Auto-Assignment</a></p>";
                        echo "<script>
                            function runAutoAssign() {
                                if (confirm('This will assign all unassigned leads to employees based on territories. Continue?')) {
                                    window.location.href = '" . APP_URL . "/leads?auto_assign=1';
                                }
                            }
                        </script>";
                    }
                }
                ?>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <?php echo getPaginationLinks($page, $totalPages, APP_URL . '/leads' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] . '&' : '?')); ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>
