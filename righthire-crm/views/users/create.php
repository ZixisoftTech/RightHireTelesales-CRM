<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create User</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/users" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
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
        
        <form method="POST" action="<?php echo APP_URL; ?>/users/create">
            <div class="mb-3">
                <label for="name" class="form-label required">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label required">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label required">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="form-text">Password must be at least 6 characters long.</div>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label required">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label required">Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="administrator" <?php echo isset($_POST['role']) && $_POST['role'] === 'administrator' ? 'selected' : ''; ?>>Administrator</option>
                    <option value="employee" <?php echo isset($_POST['role']) && $_POST['role'] === 'employee' ? 'selected' : ''; ?>>Employee</option>
                </select>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="<?php echo APP_URL; ?>/users" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>

