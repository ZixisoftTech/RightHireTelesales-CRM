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
        <div class="card card-dashboard card-new h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-muted mb-0">New Leads</h6>
                        <h2 class="mt-2 mb-0"><?php echo $stats['new_leads']; ?></h2>
                        <div class="small text-success mt-1">
                            <i class="fas fa-arrow-up"></i> 3.48% since last week
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
                        <h6 class="card-title text-muted mb-0">Follow-ups</h6>
                        <h2 class="mt-2 mb-0"><?php echo $stats['follow_ups']; ?></h2>
                        <div class="small text-success mt-1">
                            <i class="fas fa-arrow-up"></i> 2.15% since last week
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
        <div class="card card-dashboard card-interested h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-muted mb-0">Interested</h6>
                        <h2 class="mt-2 mb-0"><?php echo $stats['interested']; ?></h2>
                        <div class="small text-success mt-1">
                            <i class="fas fa-arrow-up"></i> 5.27% since last week
                        </div>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded">
                        <i class="fas fa-thumbs-up text-info fa-2x"></i>
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
                        <h6 class="card-title text-muted mb-0">Wins</h6>
                        <h2 class="mt-2 mb-0"><?php echo $stats['wins']; ?></h2>
                        <div class="small text-success mt-1">
                            <i class="fas fa-arrow-up"></i> 1.64% since last week
                        </div>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="fas fa-trophy text-success fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts & Tables -->
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
    
    <!-- Today's Follow-ups -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Today's Follow-ups</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($todayFollowUps)): ?>
                    <div class="text-center p-4">
                        <i class="fas fa-calendar-check text-muted fa-3x mb-3"></i>
                        <p class="mb-0">No follow-ups scheduled for today</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($todayFollowUps as $followUp): ?>
                            <a href="<?php echo APP_URL; ?>/leads/view?id=<?php echo $followUp['id']; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($followUp['name']); ?></h6>
                                        <p class="mb-1 text-muted small">
                                            <i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($followUp['phone']); ?>
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-warning">
                                            <i class="far fa-clock me-1"></i>
                                            <?php echo date('h:i A', strtotime($followUp['follow_up_date'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($todayFollowUps)): ?>
                <div class="card-footer bg-white text-center">
                    <a href="<?php echo APP_URL; ?>/leads?status=follow_up" class="btn btn-sm btn-outline-primary">View All Follow-ups</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <!-- Daily Call Activity -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Daily Call Activity</h5>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active" data-period="week">Week</button>
                    <button type="button" class="btn btn-outline-secondary" data-period="month">Month</button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height:300px;">
                    <canvas id="dailyCallChart"></canvas>
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

<?php if (hasRole('administrator') && !empty($employeeStats)): ?>
<div class="row">
    <!-- Employee Performance -->
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
                    'rgba(78, 115, 223, 0.8)',
                    'rgba(246, 194, 62, 0.8)',
                    'rgba(108, 117, 125, 0.8)',
                    'rgba(231, 74, 59, 0.8)',
                    'rgba(108, 117, 125, 0.8)',
                    'rgba(231, 74, 59, 0.8)',
                    'rgba(54, 185, 204, 0.8)',
                    'rgba(28, 200, 138, 0.8)'
                ],
                borderColor: [
                    'rgba(78, 115, 223, 1)',
                    'rgba(246, 194, 62, 1)',
                    'rgba(108, 117, 125, 1)',
                    'rgba(231, 74, 59, 1)',
                    'rgba(108, 117, 125, 1)',
                    'rgba(231, 74, 59, 1)',
                    'rgba(54, 185, 204, 1)',
                    'rgba(28, 200, 138, 1)'
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
                backgroundColor: 'rgba(78, 115, 223, 0.5)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 1,
                borderRadius: 4,
                barThickness: 16
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    padding: 10,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    displayColors: false
                }
            },
            animation: {
                duration: 1000
            }
        }
    });
    
    // Refresh dashboard
    document.getElementById('refreshDashboard').addEventListener('click', function() {
        this.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Refreshing...';
        this.disabled = true;
        
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    });
    
    // Period buttons for daily call chart
    document.querySelectorAll('[data-period]').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('[data-period]').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            // In a real application, this would fetch new data based on the period
            // For now, we'll just simulate a loading state
            dailyCallChart.data.datasets[0].backgroundColor = 'rgba(200, 200, 200, 0.5)';
            dailyCallChart.update();
            
            setTimeout(() => {
                dailyCallChart.data.datasets[0].backgroundColor = 'rgba(78, 115, 223, 0.5)';
                dailyCallChart.update();
            }, 500);
        });
    });
});
</script>

<?php include 'views/templates/footer.php'; ?>

