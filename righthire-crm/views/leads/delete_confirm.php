<?php
/**
 * Lead Delete Confirmation View
 * 
 * This view displays a confirmation dialog for deleting a lead.
 */

// Require header
require_once __DIR__ . '/../../views/templates/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Confirm Delete</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h4><i class="fas fa-exclamation-triangle"></i> Warning!</h4>
                        <p>You are about to delete the lead <strong><?= htmlspecialchars($lead['name']) ?></strong>.</p>
                        
                        <p>This will delete all call logs and history associated with this lead. This action cannot be undone.</p>
                        
                        <p>Are you sure you want to proceed?</p>
                    </div>
                    
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
require_once __DIR__ . '/../../views/templates/footer.php';
?>
