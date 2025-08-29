<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Lead</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/leads" class="btn btn-sm btn-secondary me-2">
            <i class="fas fa-arrow-left"></i> Back to Leads
        </a>
        <a href="<?php echo APP_URL; ?>/leads/view?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-info">
            <i class="fas fa-eye"></i> View Lead
        </a>
    </div>
</div>

<div class="card">
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
        
        <form method="POST" action="<?php echo APP_URL; ?>/leads/edit?id=<?php echo $lead['id']; ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label required">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($lead['name']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($lead['email']); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label required">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($lead['phone']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($lead['address']); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="state_id" class="form-label required">State</label>
                    <select class="form-select" id="state_id" name="state_id" required>
                        <option value="">Select State</option>
                        <?php foreach ($states as $state): ?>
                            <option value="<?php echo $state['id']; ?>" <?php echo $lead['state_id'] == $state['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($state['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="city_id" class="form-label required">City</label>
                    <select class="form-select" id="city_id" name="city_id" required>
                        <option value="">Select City</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo $city['id']; ?>" <?php echo $lead['city_id'] == $city['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($city['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php if (hasRole('administrator') && isset($employees)): ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="assigned_to" class="form-label">Assigned To</label>
                        <select class="form-select" id="assigned_to" name="assigned_to">
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?php echo $employee['id']; ?>" <?php echo $lead['assigned_to'] == $employee['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($employee['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>
            <div class="mb-3">
                <label for="remarks" class="form-label">Remarks</label>
                <textarea class="form-control" id="remarks" name="remarks" rows="3"><?php echo htmlspecialchars($lead['remarks']); ?></textarea>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="<?php echo APP_URL; ?>/leads" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Lead</button>
            </div>
        </form>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>

