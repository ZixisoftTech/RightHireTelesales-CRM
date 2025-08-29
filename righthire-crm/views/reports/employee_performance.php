<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Employee Performance Report</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/reports" class="btn btn-sm btn-secondary me-2">
            <i class="fas fa-arrow-left"></i> Back to Reports
        </a>
        <a href="<?php echo APP_URL; ?>/reports/export-employee-performance" class="btn btn-sm btn-success">
            <i class="fas fa-file-export"></i> Export to CSV
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Filter Report</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo APP_URL; ?>/reports/employee-performance">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="text" class="form-control datepicker" id="date_from" name="date_from" value="<?php echo isset($filters['date_from']) ? htmlspecialchars($filters['date_from']) : ''; ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="text" class="form-control datepicker" id="date_to" name="date_to" value="<?php echo isset($filters['date_to']) ? htmlspecialchars($filters['date_to']) : ''; ?>">
                </div>
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
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="<?php echo APP_URL; ?>/reports/employee-performance" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Employee Performance</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Total Leads</th>
                                <th>Wins</th>
                                <th>Conversion Rate</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employeeStats as $employee): ?>
                                <tr>
                                    <td><?php echo $employee['id']; ?></td>
                                    <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                    <td><?php echo $employee['total_leads']; ?></td>
                                    <td><?php echo $employee['wins']; ?></td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $employee['conversion_rate']; ?>%;" aria-valuenow="<?php echo $employee['conversion_rate']; ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?php echo $employee['conversion_rate']; ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="<?php echo APP_URL; ?>/reports/employee-performance?user_id=<?php echo $employee['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-chart-line"></i> View Trend
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($filters['user_id']) && !empty($employeeTrend)): ?>
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Performance Trend for <?php echo htmlspecialchars($employeeTrend[0]['name']); ?></h5>
                </div>
                <div class="card-body">
                    <canvas id="employeeTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Employee Trend Chart
            var employeeTrendCtx = document.getElementById('employeeTrendChart').getContext('2d');
            var employeeTrendChart = new Chart(employeeTrendCtx, {
                type: 'line',
                data: {
                    labels: [
                        <?php foreach ($employeeTrend as $trend): ?>
                            '<?php echo $trend['month_name']; ?>',
                        <?php endforeach; ?>
                    ],
                    datasets: [
                        {
                            label: 'Total Leads',
                            data: [
                                <?php foreach ($employeeTrend as $trend): ?>
                                    <?php echo $trend['total_leads']; ?>,
                                <?php endforeach; ?>
                            ],
                            backgroundColor: 'rgba(13, 110, 253, 0.5)',
                            borderColor: 'rgba(13, 110, 253, 1)',
                            borderWidth: 1,
                            fill: false
                        },
                        {
                            label: 'Wins',
                            data: [
                                <?php foreach ($employeeTrend as $trend): ?>
                                    <?php echo $trend['wins']; ?>,
                                <?php endforeach; ?>
                            ],
                            backgroundColor: 'rgba(40, 167, 69, 0.5)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 1,
                            fill: false
                        },
                        {
                            label: 'Conversion Rate (%)',
                            data: [
                                <?php foreach ($employeeTrend as $trend): ?>
                                    <?php echo $trend['conversion_rate']; ?>,
                                <?php endforeach; ?>
                            ],
                            backgroundColor: 'rgba(255, 193, 7, 0.5)',
                            borderColor: 'rgba(255, 193, 7, 1)',
                            borderWidth: 1,
                            fill: false,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Count'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Percentage'
                            },
                            max: 100,
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    }
                }
            });
        });
    </script>
<?php endif; ?>

<?php include 'views/templates/footer.php'; ?>

