<?php
/**
 * Update the dashboard view
 * 
 * This file contains the HTML for the dashboard filters and status cards.
 */
?>
<!-- Dashboard Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?php echo APP_URL; ?>/dashboard" id="dashboardFilterForm">
            <div class="row g-3">
                <?php if (hasRole('administrator')): ?>
                <div class="col-md-3">
                    <label for="employee_id" class="form-label">Employee</label>
                    <select class="form-select" id="employee_id" name="employee_id">
                        <option value="0">All Employees</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?php echo $employee['id']; ?>" <?php echo (isset($_GET['employee_id']) && $_GET['employee_id'] == $employee['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($employee['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="new" <?php echo (isset($_GET['status']) && $_GET['status'] == 'new') ? 'selected' : ''; ?>>New</option>
                        <option value="follow_up" <?php echo (isset($_GET['status']) && $_GET['status'] == 'follow_up') ? 'selected' : ''; ?>>Follow-up</option>
                        <option value="not_attend" <?php echo (isset($_GET['status']) && $_GET['status'] == 'not_attend') ? 'selected' : ''; ?>>Not Attend</option>
                        <option value="wrong_number" <?php echo (isset($_GET['status']) && $_GET['status'] == 'wrong_number') ? 'selected' : ''; ?>>Wrong Number</option>
                        <option value="other" <?php echo (isset($_GET['status']) && $_GET['status'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        <option value="dead" <?php echo (isset($_GET['status']) && $_GET['status'] == 'dead') ? 'selected' : ''; ?>>Lost</option>
                        <option value="interested" <?php echo (isset($_GET['status']) && $_GET['status'] == 'interested') ? 'selected' : ''; ?>>Interested</option>
                        <option value="win" <?php echo (isset($_GET['status']) && $_GET['status'] == 'win') ? 'selected' : ''; ?>>Won</option>
                    </select>
                </div>
                
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="<?php echo APP_URL; ?>/dashboard" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Status Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-dashboard card-new h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-muted mb-0">New Leads</h6>
                        <h2 class="mt-2 mb-0"><?php echo $stats['new_leads']; ?></h2>
                        <div class="small text-muted mt-1">
                            <i class="fas fa-user-plus"></i> Uncontacted leads
                        </div>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="fas fa-user-plus text-primary fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-dashboard card-follow-up h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-muted mb-0">Today's Follow-ups</h6>
                        <h2 class="mt-2 mb-0"><?php echo count($todayFollowUps); ?></h2>
                        <div class="small text-muted mt-1">
                            <i class="fas fa-calendar-alt"></i> Scheduled for today
                        </div>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="fas fa-calendar-alt text-warning fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-dashboard card-win h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-muted mb-0">Won</h6>
                        <h2 class="mt-2 mb-0"><?php echo $stats['wins']; ?></h2>
                        <div class="small text-muted mt-1">
                            <i class="fas fa-trophy"></i> Current month
                        </div>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="fas fa-trophy text-success fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-dashboard card-dead h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-muted mb-0">Lost</h6>
                        <h2 class="mt-2 mb-0"><?php echo $stats['dead']; ?></h2>
                        <div class="small text-muted mt-1">
                            <i class="fas fa-times-circle"></i> Current month
                        </div>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                        <i class="fas fa-times-circle text-danger fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-dashboard card-missed h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-muted mb-0">Missed Follow-ups</h6>
                        <h2 class="mt-2 mb-0"><?php echo count($missedFollowUps); ?></h2>
                        <div class="small text-muted mt-1">
                            <i class="fas fa-exclamation-triangle"></i> Require attention
                        </div>
                    </div>
                    <div class="bg-secondary bg-opacity-10 p-3 rounded">
                        <i class="fas fa-exclamation-triangle text-secondary fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Missed Follow-ups Table -->
<?php if (!empty($missedFollowUps)): ?>
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Missed Follow-ups</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Phone</th>
                                <th>Assigned To</th>
                                <th>Follow-up Date</th>
                                <th>Last Remark</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($missedFollowUps as $followUp): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($followUp['name']); ?></td>
                                    <td><?php echo htmlspecialchars($followUp['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($followUp['assigned_to_name']); ?></td>
                                    <td class="text-danger"><?php echo formatDateTime($followUp['follow_up_date']); ?></td>
                                    <td><?php echo htmlspecialchars($followUp['remarks']); ?></td>
                                    <td>
                                        <a href="<?php echo APP_URL; ?>/leads/view?id=<?php echo $followUp['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-center">
                <a href="<?php echo APP_URL; ?>/leads?status=follow_up" class="btn btn-sm btn-outline-primary">View All Follow-ups</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Add JavaScript for dashboard filters -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Today button
    document.getElementById('todayBtn').addEventListener('click', function() {
        document.getElementById('start_date').value = '<?php echo date('Y-m-d'); ?>';
        document.getElementById('end_date').value = '<?php echo date('Y-m-d'); ?>';
        document.getElementById('dashboardFilterForm').submit();
    });
    
    // This week button
    document.getElementById('weekBtn').addEventListener('click', function() {
        // Get first day of current week (Monday)
        var firstDay = new Date();
        var day = firstDay.getDay() || 7; // Get current day number, converting Sunday (0) to 7
        if (day !== 1) // If not Monday
            firstDay.setHours(-24 * (day - 1)); // Set to previous Monday
        
        // Get last day of current week (Sunday)
        var lastDay = new Date(firstDay);
        lastDay.setDate(lastDay.getDate() + 6);
        
        document.getElementById('start_date').value = formatDate(firstDay);
        document.getElementById('end_date').value = formatDate(lastDay);
        document.getElementById('dashboardFilterForm').submit();
    });
    
    // This month button
    document.getElementById('monthBtn').addEventListener('click', function() {
        var date = new Date();
        var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
        var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
        
        document.getElementById('start_date').value = formatDate(firstDay);
        document.getElementById('end_date').value = formatDate(lastDay);
        document.getElementById('dashboardFilterForm').submit();
    });
    
    // Helper function to format date as YYYY-MM-DD
    function formatDate(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();
        
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        
        return [year, month, day].join('-');
    }
});
</script>

