<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create City</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/cities" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Cities
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
        
        <form method="POST" action="<?php echo APP_URL; ?>/cities/create">
            <div class="mb-3">
                <label for="state_id" class="form-label required">State</label>
                <select class="form-select" id="state_id" name="state_id" required>
                    <option value="">Select State</option>
                    <?php foreach ($states as $state): ?>
                        <option value="<?php echo $state['id']; ?>" <?php echo isset($_POST['state_id']) && $_POST['state_id'] == $state['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($state['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label required">City Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="<?php echo APP_URL; ?>/cities" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create City</button>
            </div>
        </form>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>

