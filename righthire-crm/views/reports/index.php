<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Reports</h1>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Lead Status Report</h5>
                <p class="card-text">View lead statistics by status, state, city, and employee.</p>
                <a href="<?php echo APP_URL; ?>/reports/lead-status" class="btn btn-primary">View Report</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Call Log Report</h5>
                <p class="card-text">View call logs by date range and employee.</p>
                <a href="<?php echo APP_URL; ?>/reports/call-log" class="btn btn-primary">View Report</a>
            </div>
        </div>
    </div>
    
    <?php if (hasRole('administrator')): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Employee Performance Report</h5>
                    <p class="card-text">View employee performance metrics and conversion rates.</p>
                    <a href="<?php echo APP_URL; ?>/reports/employee-performance" class="btn btn-primary">View Report</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'views/templates/footer.php'; ?>

