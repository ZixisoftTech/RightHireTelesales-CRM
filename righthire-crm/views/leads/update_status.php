<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Update Lead Status</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/leads/view?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Lead
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Lead Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Name</h6>
                    <p class="mb-0"><?php echo htmlspecialchars($lead['name']); ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Phone</h6>
                    <p class="mb-0"><?php echo htmlspecialchars($lead['phone']); ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Email</h6>
                    <p class="mb-0"><?php echo $lead['email'] ? htmlspecialchars($lead['email']) : '-'; ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Current Status</h6>
                    <p class="mb-0"><?php echo getStatusBadge($lead['status']); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Update Status</h5>
            </div>
            <div class="card-body">
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo APP_URL; ?>/leads/update-status?id=<?php echo $lead['id']; ?>">
                    <div class="mb-3">
                        <label for="status" class="form-label required">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="new" <?php echo isset($_POST['status']) && $_POST['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="not_attend" <?php echo isset($_POST['status']) && $_POST['status'] === 'not_attend' ? 'selected' : ''; ?>>Not Attend</option>
                            <option value="wrong_number" <?php echo isset($_POST['status']) && $_POST['status'] === 'wrong_number' ? 'selected' : ''; ?>>Wrong Number</option>
                            <option value="interested" <?php echo isset($_POST['status']) && $_POST['status'] === 'interested' ? 'selected' : ''; ?>>Interested</option>
                            <option value="lost" <?php echo isset($_POST['status']) && $_POST['status'] === 'lost' ? 'selected' : ''; ?>>Lost</option>
                            <option value="won" <?php echo isset($_POST['status']) && $_POST['status'] === 'won' ? 'selected' : ''; ?>>Won</option>
                        </select>
                    </div>
                    <div id="region-group" class="mb-3 d-none">
                        <label for="region" class="form-label required">Region</label>
                        <input type="text" class="form-control" id="region" name="region" value="<?php echo isset($_POST['region']) ? htmlspecialchars($_POST['region']) : ''; ?>">
                        <div class="form-text">Required for Lost status</div>
                    </div>
                    <div id="follow-up-date-group" class="mb-3 d-none">
                        <label for="follow_up_date" class="form-label required">Follow-up Date</label>
                        <input type="text" class="form-control datetimepicker" id="follow_up_date" name="follow_up_date" value="<?php echo isset($_POST['follow_up_date']) ? htmlspecialchars($_POST['follow_up_date']) : ''; ?>">
                        <div class="form-text">Required for Interested status</div>
                    </div>
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3"><?php echo isset($_POST['remarks']) ? htmlspecialchars($_POST['remarks']) : ''; ?></textarea>
                        <div id="remarks-help" class="form-text"></div>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo APP_URL; ?>/leads/view?id=<?php echo $lead['id']; ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const regionGroup = document.getElementById('region-group');
    const followUpDateGroup = document.getElementById('follow-up-date-group');
    const remarksHelp = document.getElementById('remarks-help');
    const remarksField = document.getElementById('remarks');
    
    statusSelect.addEventListener('change', function() {
        // Hide all conditional fields first
        regionGroup.classList.add('d-none');
        followUpDateGroup.classList.add('d-none');
        remarksHelp.textContent = '';
        
        // Reset required attributes
        document.getElementById('region').required = false;
        document.getElementById('follow_up_date').required = false;
        remarksField.required = false;
        
        // Show fields based on selected status
        const selectedStatus = this.value;
        
        if (selectedStatus === 'lost') {
            regionGroup.classList.remove('d-none');
            document.getElementById('region').required = true;
        }
        
        if (selectedStatus === 'interested') {
            followUpDateGroup.classList.remove('d-none');
            document.getElementById('follow_up_date').required = true;
        }
        
        if (selectedStatus === 'not_attend' || selectedStatus === 'wrong_number') {
            remarksHelp.textContent = 'Remarks are required for ' + 
                (selectedStatus === 'not_attend' ? 'Not Attend' : 'Wrong Number') + ' status';
            remarksField.required = true;
        }
    });
    
    // Trigger change event to set initial state
    statusSelect.dispatchEvent(new Event('change'));
});
</script>

<?php include 'views/templates/footer.php'; ?>

