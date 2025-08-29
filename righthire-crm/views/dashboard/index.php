<?php
$pageTitle = 'Dashboard';
include 'views/templates/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        <div>
            <a href="<?php echo APP_URL; ?>/leads/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Lead
            </a>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Leads</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_leads']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="<?php echo APP_URL; ?>/leads" class="text-primary">View All</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                New Leads</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['new_leads']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="<?php echo APP_URL; ?>/leads?status=new" class="text-success">View New Leads</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Follow-ups Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($todayFollowUps); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="<?php echo APP_URL; ?>/leads?status=follow_up" class="text-warning">View Follow-ups</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Wins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['wins']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="<?php echo APP_URL; ?>/leads?status=win" class="text-info">View Wins</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Today's Follow-ups -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-calendar-check"></i> Today's Follow-ups</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($todayFollowUps)): ?>
                        <p class="text-center">No follow-ups scheduled for today.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Location</th>
                                        <th>Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($todayFollowUps as $lead): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($lead['name']); ?></td>
                                            <td><?php echo htmlspecialchars($lead['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($lead['city_name'] . ', ' . $lead['state_name']); ?></td>
                                            <td>
                                                <?php 
                                                    $time = new DateTime($lead['follow_up_date']);
                                                    echo $time->format('h:i A');
                                                ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/leads/view/<?php echo $lead['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Recent Call Logs -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-phone-alt"></i> Recent Call Logs</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($recentCalls)): ?>
                        <p class="text-center">No recent call logs found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Lead</th>
                                        <th>Status</th>
                                        <th>Date/Time</th>
                                        <th>By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentCalls as $call): ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/leads/view/<?php echo $call['lead_id']; ?>">
                                                    <?php echo htmlspecialchars($call['lead_name']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php
                                                    $statusClass = '';
                                                    switch ($call['status']) {
                                                        case 'new':
                                                            $statusClass = 'bg-primary';
                                                            break;
                                                        case 'follow_up':
                                                            $statusClass = 'bg-warning';
                                                            break;
                                                        case 'interested':
                                                            $statusClass = 'bg-info';
                                                            break;
                                                        case 'win':
                                                            $statusClass = 'bg-success';
                                                            break;
                                                        case 'dead':
                                                            $statusClass = 'bg-danger';
                                                            break;
                                                        default:
                                                            $statusClass = 'bg-secondary';
                                                    }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $call['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $time = new DateTime($call['created_at']);
                                                    echo $time->format('M d, h:i A');
                                                ?>
                                            </td>
                                            <td>
                                                <?php echo isset($call['created_by_name']) ? htmlspecialchars($call['created_by_name']) : 'N/A'; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Performance Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-line"></i> 
                        <?php echo hasRole('administrator') ? 'Call Activity' : 'Your Call Activity'; ?>
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="callActivityChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Employee Performance -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-pie"></i> 
                        <?php echo hasRole('administrator') ? 'Employee Performance' : 'Your Performance'; ?>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (hasRole('administrator')): ?>
                        <?php if (empty($employeeStats)): ?>
                            <p class="text-center">No employee performance data available.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Leads</th>
                                            <th>Wins</th>
                                            <th>Conversion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($employeeStats as $employee): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                                <td><?php echo $employee['total_leads']; ?></td>
                                                <td><?php echo $employee['wins']; ?></td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-success" role="progressbar" 
                                                            style="width: <?php echo $employee['conversion_rate']; ?>%" 
                                                            aria-valuenow="<?php echo $employee['conversion_rate']; ?>" 
                                                            aria-valuemin="0" aria-valuemax="100">
                                                            <?php echo $employee['conversion_rate']; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if (empty($employeeStats)): ?>
                            <p class="text-center">No performance data available.</p>
                        <?php else: ?>
                            <canvas id="conversionTrendChart" width="100%" height="300"></canvas>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Add extra JavaScript for charts
$extraJs = '
<script>
    // Call Activity Chart
    var ctx = document.getElementById("callActivityChart");
    var callActivityChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: [' . implode(', ', array_map(function($item) { return '"' . $item['date'] . '"'; }, $dailyCallCount)) . '],
            datasets: [{
                label: "Calls",
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                pointBorderColor: "rgba(78, 115, 223, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: [' . implode(', ', array_map(function($item) { return $item['count']; }, $dailyCallCount)) . '],
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                xAxes: [{
                    time: {
                        unit: "date"
                    },
                    gridLines: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 7
                    }
                }],
                yAxes: [{
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        beginAtZero: true
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
            },
            legend: {
                display: false
            },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: "#6e707e",
                titleFontSize: 14,
                borderColor: "#dddfeb",
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: "index",
                caretPadding: 10,
            }
        }
    });
';

// Add conversion trend chart for employees
if (!hasRole('administrator') && !empty($employeeStats)) {
    $extraJs .= '
    // Conversion Trend Chart
    var ctxTrend = document.getElementById("conversionTrendChart");
    var conversionTrendChart = new Chart(ctxTrend, {
        type: "bar",
        data: {
            labels: [' . implode(', ', array_map(function($item) { return '"' . $item['month'] . '"'; }, $employeeStats)) . '],
            datasets: [{
                label: "Conversion Rate (%)",
                backgroundColor: "rgba(40, 167, 69, 0.8)",
                borderColor: "rgba(40, 167, 69, 1)",
                data: [' . implode(', ', array_map(function($item) { return $item['conversion_rate']; }, $employeeStats)) . '],
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                xAxes: [{
                    gridLines: {
                        display: false,
                        drawBorder: false
                    }
                }],
                yAxes: [{
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        beginAtZero: true,
                        callback: function(value) {return value + "%";}
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        return tooltipItem.yLabel + "%";
                    }
                }
            }
        }
    });
    ';
}

$extraJs .= '</script>';
?>

<?php include 'views/templates/footer.php'; ?>

