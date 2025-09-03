<?php
/**
 * Lead Delete Confirmation View
 * 
 * This view displays a confirmation dialog for deleting a lead.
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
                        <p>You are about to <strong>permanently delete</strong> the lead <strong><?= htmlspecialchars($lead['name']) ?></strong>.</p>
                        
                        <p>This lead has <strong><?= $callLogCount ?></strong> call logs associated with it.</p>
                        
                        <p><strong>This is a HARD DELETE operation.</strong> All data will be permanently removed from the database and cannot be recovered.</p>
                        
                        <p>Are you sure you want to proceed?</p>
                    </div>
                    
                    <?php if (!empty($callLogs)): ?>
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Associated Call Logs (<?= count($callLogs) ?>)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Agent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($callLogs as $log): ?>
                                        <tr>
                                            <td><?= $log['id'] ?></td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                switch ($log['status']) {
                                                    case 'new': $statusClass = 'primary'; break;
                                                    case 'follow_up': $statusClass = 'info'; break;
                                                    case 'not_attend': $statusClass = 'warning'; break;
                                                    case 'wrong_number': $statusClass = 'danger'; break;
                                                    case 'other': $statusClass = 'secondary'; break;
                                                    case 'dead': $statusClass = 'dark'; break;
                                                    case 'interested': $statusClass = 'success'; break;
                                                    case 'win': $statusClass = 'success'; break;
                                                }
                                                ?>
                                                <span class="badge badge-<?= $statusClass ?>">
                                                    <?= ucwords(str_replace('_', ' ', $log['status'])) ?>
                                                </span>
                                            </td>
                                            <td><?= date('M d, Y h:i A', strtotime($log['created_at'])) ?></td>
                                            <td><?= htmlspecialchars($log['created_by_name'] ?? 'N/A') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/leads" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <a href="<?= APP_URL ?>/leads/delete?id=<?= $lead['id'] ?>&confirm=1" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Yes, Delete Lead
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
