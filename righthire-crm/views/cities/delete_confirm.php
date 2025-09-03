<?php
/**
 * City Delete Confirmation View
 * 
 * This view displays a confirmation dialog for deleting a city with associated data.
 */

// Require header
require_once VIEWS_PATH . '/templates/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Confirm Delete</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <h4><i class="fas fa-exclamation-triangle"></i> Warning! PERMANENT DELETION</h4>
                        <p>You are about to <strong>permanently delete</strong> the city <strong><?= htmlspecialchars($city['name']) ?></strong> in state <strong><?= htmlspecialchars($state['name']) ?></strong>.</p>
                        
                        <p>This city has:</p>
                        <ul>
                            <li><strong><?= $leadCount ?></strong> leads associated with it</li>
                        </ul>
                        
                        <p><strong>This is a HARD DELETE operation.</strong> All data will be permanently removed from the database and cannot be recovered.</p>
                        
                        <p>Are you sure you want to proceed?</p>
                    </div>
                    
                    <?php if (!empty($leads)): ?>
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Associated Leads (<?= count($leads) ?><?= count($leads) < $leadCount ? ' of ' . $leadCount : '' ?>)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($leads as $lead): ?>
                                        <tr>
                                            <td><?= $lead['id'] ?></td>
                                            <td><?= htmlspecialchars($lead['name']) ?></td>
                                            <td><?= htmlspecialchars($lead['phone']) ?></td>
                                            <td><?= htmlspecialchars($lead['email'] ?? 'N/A') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/cities" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <a href="<?= APP_URL ?>/cities/delete?id=<?= $city['id'] ?>&confirm=1" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Yes, Delete City and All Associated Data
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Require footer
require_once VIEWS_PATH . '/templates/footer.php';
?>
