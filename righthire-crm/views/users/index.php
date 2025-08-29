<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Users</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/users/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Add New User
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Leads</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <?php if ($user['role'] === 'administrator'): ?>
                                    <span class="badge bg-danger">Administrator</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Employee</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $user['lead_count']; ?></td>
                            <td>
                                <?php if ($user['status'] == 1): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDateTime($user['created_at']); ?></td>
                            <td>
                                <a href="<?php echo APP_URL; ?>/users/edit?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($user['role'] === 'employee'): ?>
                                    <a href="<?php echo APP_URL; ?>/users/territories?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Manage Territories">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($user['id'] !== getCurrentUserId()): ?>
                                    <a href="<?php echo APP_URL; ?>/users/toggle-status?id=<?php echo $user['id']; ?>" class="btn btn-sm <?php echo $user['status'] == 1 ? 'btn-warning' : 'btn-success'; ?>" data-bs-toggle="tooltip" title="<?php echo $user['status'] == 1 ? 'Deactivate' : 'Activate'; ?>">
                                        <i class="fas <?php echo $user['status'] == 1 ? 'fa-ban' : 'fa-check'; ?>"></i>
                                    </a>
                                    <a href="<?php echo APP_URL; ?>/users/delete?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this user?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <?php echo getPaginationLinks($page, $totalPages, APP_URL . '/users'); ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>

