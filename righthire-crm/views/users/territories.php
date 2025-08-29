<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Territories for <?php echo htmlspecialchars($user['name']); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/users" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Add Territory</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo APP_URL; ?>/users/add-territory">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <div class="mb-3">
                        <label for="state_id" class="form-label required">State</label>
                        <select class="form-select" id="state_id" name="state_id" required>
                            <option value="">Select State</option>
                            <?php foreach ($states as $state): ?>
                                <option value="<?php echo $state['id']; ?>">
                                    <?php echo htmlspecialchars($state['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="city_id" class="form-label">City (Optional)</label>
                        <select class="form-select" id="city_id" name="city_id">
                            <option value="">All Cities</option>
                        </select>
                        <div class="form-text">Leave blank to assign all cities in the selected state.</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Add Territory</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Current Territories</h5>
            </div>
            <div class="card-body">
                <?php if (empty($territories)): ?>
                    <p class="text-muted">No territories assigned yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>State</th>
                                    <th>City</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($territories as $territory): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($territory['state_name']); ?></td>
                                        <td><?php echo $territory['city_name'] ? htmlspecialchars($territory['city_name']) : 'All Cities'; ?></td>
                                        <td>
                                            <a href="<?php echo APP_URL; ?>/users/remove-territory?id=<?php echo $territory['id']; ?>&user_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to remove this territory?');">
                                                <i class="fas fa-trash"></i> Remove
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
</div>

<?php include 'views/templates/footer.php'; ?>

