<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshDashboard">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="todayBtn">
                Today
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="weekBtn">
                This Week
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="monthBtn">
                This Month
            </button>
        </div>
    </div>
</div>

<!-- Status Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="<?php echo APP_URL; ?>/leads?status=new" class="text-decoration-none">
            <div class="card card-dashboard card-new h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-0">New Leads</h6>
                            <h2 class="mt-2 mb-0"><?php echo $stats['new_leads']; ?></h2>
                            <div class="small text-success mt-1">
                                <i class="fas fa-arrow-up"></i> <?php echo date('M d'); ?> stats
                            </div>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-user-plus text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="<?php echo APP_URL; ?>/followups" class="text-decoration-none">
            <div class="card card-dashboard card-follow-up h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-0">Followups</h6>
                            <h2 class="mt-2 mb-0"><?php echo count($todayFollowUps) + count($missedFollowUps); ?></h2>
                            <div class="small text-success mt-1">
                                <i class="fas fa-calendar-alt"></i> Pending follow-ups
                            </div>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-calendar-alt text-warning fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="<?php echo APP_URL; ?>/leads?status=in_dealing" class="text-decoration-none">
            <div class="card card-dashboard card-in-dealing h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-0">In Dealing</h6>
                            <h2 class="mt-2 mb-0"><?php echo $stats['in_dealing']; ?></h2>
                            <div class="small text-success mt-1">
                                <i class="fas fa-handshake"></i> Active negotiations
                            </div>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-handshake text-info fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="<?php echo APP_URL; ?>/leads?status=win" class="text-decoration-none">
            <div class="card card-dashboard card-win h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-0">Won</h6>
                            <h2 class="mt-2 mb-0"><?php echo $stats['wins']; ?></h2>
                            <div class="small text-success mt-1">
                                <i class="fas fa-calendar-alt"></i> <?php echo $stats['monthly_wins']; ?> this month
                            </div>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-trophy text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Today's Follow-ups -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Today's Follow-ups</h5>
                <span class="badge bg-primary"><?php echo count($todayFollowUps); ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($todayFollowUps)): ?>
                    <div class="text-center p-4">
                        <i class="fas fa-calendar-check text-muted fa-3x mb-3"></i>
                        <p class="mb-0">No follow-ups scheduled for today</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Lead Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>State</th>
                                    <th>City</th>
                                    <th>Status</th>
                                    <?php if (hasRole('administrator')): ?>
                                        <th>Assigned To</th>
                                    <?php endif; ?>
                                    <th>Follow-Up Date & Time</th>
                                    <th>Last Remark</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todayFollowUps as $followUp): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo APP_URL; ?>/leads/view?id=<?php echo $followUp['id']; ?>">
                                                <?php echo htmlspecialchars($followUp['name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($followUp['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($followUp['email']); ?></td>
                                        <td><?php echo htmlspecialchars($followUp['state_name']); ?></td>
                                        <td><?php echo htmlspecialchars($followUp['city_name']); ?></td>
                                        <td><?php echo getStatusBadge($followUp['status']); ?></td>
                                        <?php if (hasRole('administrator')): ?>
                                            <td><?php echo htmlspecialchars($followUp['assigned_to_name']); ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <span class="badge bg-warning">
                                                <?php echo date('M d, h:i A', strtotime($followUp['follow_up_date'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($followUp['last_remark'] ?? 'No remarks'); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo APP_URL; ?>/leads/view?id=<?php echo $followUp['id']; ?>" class="btn btn-outline-primary" title="View Lead">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo APP_URL; ?>/call-logs/add?lead_id=<?php echo $followUp['id']; ?>" class="btn btn-outline-success" title="Add Call Log">
                                                    <i class="fas fa-phone-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($todayFollowUps)): ?>
                <div class="card-footer bg-white text-center">
                    <a href="<?php echo APP_URL; ?>/followups" class="btn btn-sm btn-outline-primary">View All Follow-ups</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Missed Follow-ups -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Missed Follow-ups</h5>
                <span class="badge bg-danger"><?php echo count($missedFollowUps); ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($missedFollowUps)): ?>
                    <div class="text-center p-4">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <p class="mb-0">No missed follow-ups</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Lead Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>State</th>
                                    <th>City</th>
                                    <th>Status</th>
                                    <?php if (hasRole('administrator')): ?>
                                        <th>Assigned To</th>
                                    <?php endif; ?>
                                    <th>Follow-Up Date & Time</th>
                                    <th>Last Remark</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($missedFollowUps as $followUp): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo APP_URL; ?>/leads/view?id=<?php echo $followUp['id']; ?>">
                                                <?php echo htmlspecialchars($followUp['name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($followUp['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($followUp['email']); ?></td>
                                        <td><?php echo htmlspecialchars($followUp['state_name']); ?></td>
                                        <td><?php echo htmlspecialchars($followUp['city_name']); ?></td>
                                        <td><?php echo getStatusBadge($followUp['status']); ?></td>
                                        <?php if (hasRole('administrator')): ?>
                                            <td><?php echo htmlspecialchars($followUp['assigned_to_name']); ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <span class="badge bg-danger">
                                                <?php echo date('M d, h:i A', strtotime($followUp['follow_up_date'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($followUp['last_remark'] ?? 'No remarks'); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo APP_URL; ?>/leads/view?id=<?php echo $followUp['id']; ?>" class="btn btn-outline-primary" title="View Lead">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo APP_URL; ?>/call-logs/add?lead_id=<?php echo $followUp['id']; ?>" class="btn btn-outline-success" title="Add Call Log">
                                                    <i class="fas fa-phone-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($missedFollowUps)): ?>
                <div class="card-footer bg-white text-center">
                    <a href="<?php echo APP_URL; ?>/followups" class="btn btn-sm btn-outline-danger">View All Missed Follow-ups</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Lead Status Overview and Recent Call Logs -->
<div class="row">
    <!-- Lead Status Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Lead Status Overview</h5>
                <div class="dropdown">
                    <button class="btn btn-sm btn-link dropdown-toggle text-muted" type="button" id="chartDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="chartDropdown">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i> Export</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-sync me-2"></i> Refresh</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height:300px;">
                    <canvas id="leadStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Call Logs -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Recent Call Logs</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentCalls)): ?>
                    <div class="text-center p-4">
                        <i class="fas fa-phone-slash text-muted fa-3x mb-3"></i>
                        <p class="mb-0">No recent call logs found</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentCalls as $call): ?>
                            <a href="<?php echo APP_URL; ?>/leads/view?id=<?php echo $call['lead_id']; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($call['lead_name']); ?></h6>
                                        <p class="mb-1">
                                            <?php echo getStatusBadge($call['status']); ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-user-edit me-1"></i> <?php echo htmlspecialchars($call['created_by_name']); ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">
                                            <?php echo date('M d, h:i A', strtotime($call['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($recentCalls)): ?>
                <div class="card-footer bg-white text-center">
                    <a href="<?php echo APP_URL; ?>/reports/call-log" class="btn btn-sm btn-outline-primary">View All Calls</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Employee Performance -->
<?php if (!empty($employeeStats)): ?>
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Employee Performance</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Total Leads</th>
                                <th>Wins</th>
                                <th>Conversion Rate</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employeeStats as $employee): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo APP_URL; ?>/assets/img/user-avatar.png" alt="User Avatar" class="user-avatar-sm me-2">
                                            <div>
                                                <?php echo htmlspecialchars($employee['name']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $employee['total_leads']; ?></td>
                                    <td><?php echo $employee['wins']; ?></td>
                                    <td><?php echo $employee['conversion_rate']; ?>%</td>
                                    <td>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $employee['conversion_rate']; ?>%;" aria-valuenow="<?php echo $employee['conversion_rate']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-center">
                <a href="<?php echo APP_URL; ?>/reports/employee-performance" class="btn btn-sm btn-outline-primary">View Detailed Report</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lead Status Chart
    var leadStatusCtx = document.getElementById('leadStatusChart').getContext('2d');
    var leadStatusChart = new Chart(leadStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['New', 'Not Attend', 'Wrong Number', 'Interested', 'In Dealing', 'Win', 'Lost'],
            datasets: [{
                data: [
                    <?php echo $stats['new_leads']; ?>,
                    <?php echo $stats['not_attend']; ?>,
                    <?php echo $stats['wrong_number']; ?>,
                    <?php echo $stats['interested']; ?>,
                    <?php echo $stats['in_dealing']; ?>,
                    <?php echo $stats['wins']; ?>,
                    <?php echo $stats['lost']; ?>
                ],
                backgroundColor: [
                    'rgba(78, 115, 223, 0.8)',  // New
                    'rgba(108, 117, 125, 0.8)', // Not Attend
                    'rgba(231, 74, 59, 0.8)',   // Wrong Number
                    'rgba(54, 185, 204, 0.8)',  // Interested
                    'rgba(246, 194, 62, 0.8)',  // In Dealing
                    'rgba(28, 200, 138, 0.8)',  // Win
                    'rgba(231, 74, 59, 0.8)'    // Lost
                ],
                borderColor: [
                    'rgba(78, 115, 223, 1)',    // New
                    'rgba(108, 117, 125, 1)',   // Not Attend
                    'rgba(231, 74, 59, 1)',     // Wrong Number
                    'rgba(54, 185, 204, 1)',    // Interested
                    'rgba(246, 194, 62, 1)',    // In Dealing
                    'rgba(28, 200, 138, 1)',    // Win
                    'rgba(231, 74, 59, 1)'      // Lost
                ],
                borderWidth: 1,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var label = context.label || '';
                            var value = context.raw || 0;
                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                            var percentage = Math.round((value / total) * 100);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });
    
    // Filter buttons
    document.getElementById('todayBtn').addEventListener('click', function() {
        window.location.href = '<?php echo APP_URL; ?>/dashboard?start_date=<?php echo date('Y-m-d'); ?>&end_date=<?php echo date('Y-m-d'); ?>';
    });
    
    document.getElementById('weekBtn').addEventListener('click', function() {
        var today = new Date();
        var startOfWeek = new Date(today.setDate(today.getDate() - today.getDay()));
        var endOfWeek = new Date(today.setDate(today.getDate() + 6));
        
        window.location.href = '<?php echo APP_URL; ?>/dashboard?start_date=' + formatDate(startOfWeek) + '&end_date=' + formatDate(endOfWeek);
    });
    
    document.getElementById('monthBtn').addEventListener('click', function() {
        var today = new Date();
        var startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        var endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        
        window.location.href = '<?php echo APP_URL; ?>/dashboard?start_date=' + formatDate(startOfMonth) + '&end_date=' + formatDate(endOfMonth);
    });
    
    document.getElementById('refreshDashboard').addEventListener('click', function() {
        window.location.reload();
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

<?php include 'views/templates/footer.php'; ?>

