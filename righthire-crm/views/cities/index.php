<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Cities</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/cities/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Add New City
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
                        <th>State</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cities as $city): ?>
                        <tr>
                            <td><?php echo $city['id']; ?></td>
                            <td><?php echo htmlspecialchars($city['name']); ?></td>
                            <td><?php echo htmlspecialchars($city['state_name']); ?></td>
                            <td>
                                <?php if ($city['status'] == 1): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDateTime($city['created_at']); ?></td>
                            <td>
                                <a href="<?php echo APP_URL; ?>/cities/edit?id=<?php echo $city['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo APP_URL; ?>/cities/toggle-status?id=<?php echo $city['id']; ?>" class="btn btn-sm <?php echo $city['status'] == 1 ? 'btn-warning' : 'btn-success'; ?>" data-bs-toggle="tooltip" title="<?php echo $city['status'] == 1 ? 'Deactivate' : 'Activate'; ?>">
                                    <i class="fas <?php echo $city['status'] == 1 ? 'fa-ban' : 'fa-check'; ?>"></i>
                                </a>
                                <a href="<?php echo APP_URL; ?>/cities/delete?id=<?php echo $city['id']; ?>" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this city?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <?php echo getPaginationLinks($page, $totalPages, APP_URL . '/cities'); ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>

