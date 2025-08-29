<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage States</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/states/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Add New State
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
                        <th>Cities</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($states as $state): ?>
                        <tr>
                            <td><?php echo $state['id']; ?></td>
                            <td><?php echo htmlspecialchars($state['name']); ?></td>
                            <td><?php echo $state['city_count']; ?></td>
                            <td>
                                <?php if ($state['status'] == 1): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDateTime($state['created_at']); ?></td>
                            <td>
                                <a href="<?php echo APP_URL; ?>/states/edit?id=<?php echo $state['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo APP_URL; ?>/states/toggle-status?id=<?php echo $state['id']; ?>" class="btn btn-sm <?php echo $state['status'] == 1 ? 'btn-warning' : 'btn-success'; ?>" data-bs-toggle="tooltip" title="<?php echo $state['status'] == 1 ? 'Deactivate' : 'Activate'; ?>">
                                    <i class="fas <?php echo $state['status'] == 1 ? 'fa-ban' : 'fa-check'; ?>"></i>
                                </a>
                                <a href="<?php echo APP_URL; ?>/states/delete?id=<?php echo $state['id']; ?>" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this state?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <?php echo getPaginationLinks($page, $totalPages, APP_URL . '/states'); ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>

