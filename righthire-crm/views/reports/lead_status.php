<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Lead Status Report</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/reports" class="btn btn-sm btn-secondary me-2">
            <i class="fas fa-arrow-left"></i> Back to Reports
        </a>
        <a href="<?php echo APP_URL; ?>/reports/export-lead-status<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-sm btn-success">
            <i class="fas fa-file-export"></i> Export to CSV
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Filter Report</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo APP_URL; ?>/reports/lead-status">
            <div class="row">
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
                        <label for="assigned_to" class="form-label">Employee</label>
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
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="<?php echo APP_URL; ?>/reports/lead-status" class="btn btn-secondary">
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
                        <h6 class="card-title text-muted mb-0">New Leads</h6>
                        <h2 class="mt-2 mb-0"><?php echo $stats['new_leads']; ?></h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="fas fa-user-plus text-primary fa-2x"></i>
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

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Lead Status Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="leadStatusChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Daily Call Activity</h5>
            </div>
            <div class="card-body">
                <canvas id="dailyCallChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lead Status Chart
        var leadStatusCtx = document.getElementById('leadStatusChart').getContext('2d');
        var leadStatusChart = new Chart(leadStatusCtx, {
            type: 'pie',
            data: {
                labels: ['New', 'Follow-up', 'Not Attend', 'Wrong Number', 'Other', 'Dead', 'Interested', 'Win'],
                datasets: [{
                    data: [
                        <?php echo $stats['new_leads']; ?>,
                        <?php echo $stats['follow_ups']; ?>,
                        <?php echo $stats['not_attend']; ?>,
                        <?php echo $stats['wrong_number']; ?>,
                        <?php echo $stats['other']; ?>,
                        <?php echo $stats['dead']; ?>,
                        <?php echo $stats['interested']; ?>,
                        <?php echo $stats['wins']; ?>
                    ],
                    backgroundColor: [
                        'rgba(13, 110, 253, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(108, 117, 125, 0.7)',
                        'rgba(220, 53, 69, 0.7)',
                        'rgba(108, 117, 125, 0.7)',
                        'rgba(220, 53, 69, 0.7)',
                        'rgba(23, 162, 184, 0.7)',
                        'rgba(40, 167, 69, 0.7)'
                    ],
                    borderColor: [
                        'rgba(13, 110, 253, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(108, 117, 125, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(108, 117, 125, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(40, 167, 69, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
        
        // Daily Call Chart
        var dailyCallCtx = document.getElementById('dailyCallChart').getContext('2d');
        var dailyCallChart = new Chart(dailyCallCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php foreach ($dailyCallCount as $day): ?>
                        '<?php echo date('M d', strtotime($day['date'])); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Calls',
                    data: [
                        <?php foreach ($dailyCallCount as $day): ?>
                            <?php echo $day['count']; ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: 'rgba(13, 110, 253, 0.5)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    });
</script>

<?php include 'views/templates/footer.php'; ?>

