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
                            <option value="follow_up" <?php echo isset($_POST['status']) && $_POST['status'] === 'follow_up' ? 'selected' : ''; ?>>Follow-up</option>
                            <option value="not_attend" <?php echo isset($_POST['status']) && $_POST['status'] === 'not_attend' ? 'selected' : ''; ?>>Not Attend</option>
                            <option value="wrong_number" <?php echo isset($_POST['status']) && $_POST['status'] === 'wrong_number' ? 'selected' : ''; ?>>Wrong Number</option>
                            <option value="other" <?php echo isset($_POST['status']) && $_POST['status'] === 'other' ? 'selected' : ''; ?>>Other</option>
                            <option value="dead" <?php echo isset($_POST['status']) && $_POST['status'] === 'dead' ? 'selected' : ''; ?>>Dead</option>
                            <option value="interested" <?php echo isset($_POST['status']) && $_POST['status'] === 'interested' ? 'selected' : ''; ?>>Interested</option>
                            <option value="win" <?php echo isset($_POST['status']) && $_POST['status'] === 'win' ? 'selected' : ''; ?>>Win</option>
                        </select>
                    </div>
                    <div id="other-reason-group" class="mb-3 <?php echo isset($_POST['status']) && $_POST['status'] === 'other' ? '' : 'd-none'; ?>">
                        <label for="other_reason" class="form-label required">Other Reason</label>
                        <input type="text" class="form-control" id="other_reason" name="other_reason" value="<?php echo isset($_POST['other_reason']) ? htmlspecialchars($_POST['other_reason']) : ''; ?>">
                    </div>
                    <div id="follow-up-date-group" class="mb-3 <?php echo isset($_POST['status']) && $_POST['status'] === 'follow_up' ? '' : 'd-none'; ?>">
                        <label for="follow_up_date" class="form-label required">Follow-up Date</label>
                        <input type="text" class="form-control datetimepicker" id="follow_up_date" name="follow_up_date" value="<?php echo isset($_POST['follow_up_date']) ? htmlspecialchars($_POST['follow_up_date']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3"><?php echo isset($_POST['remarks']) ? htmlspecialchars($_POST['remarks']) : ''; ?></textarea>
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

<?php include 'views/templates/footer.php'; ?>

