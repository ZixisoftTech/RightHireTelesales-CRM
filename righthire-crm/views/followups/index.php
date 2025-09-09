<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Follow-ups</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshBtn">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="resetFiltersBtn">
                <i class="fas fa-times"></i> Reset Filters
            </button>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Filters</h5>
    </div>
    <div class="card-body">
        <form id="filterForm" method="get" action="<?php echo APP_URL; ?>/followups">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <?php foreach (LEAD_STATUSES as $statusKey => $statusLabel): ?>
                            <option value="<?php echo $statusKey; ?>" <?php echo $status === $statusKey ? 'selected' : ''; ?>>
                                <?php echo $statusLabel; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="state_id" class="form-label">State</label>
                    <select class="form-select" id="state_id" name="state_id">
                        <option value="">All States</option>
                        <?php foreach ($states as $state): ?>
                            <option value="<?php echo $state['id']; ?>" <?php echo $stateId == $state['id'] ? 'selected' : ''; ?>>
                                <?php echo $state['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="city_id" class="form-label">City</label>
                    <select class="form-select" id="city_id" name="city_id" <?php echo empty($stateId) ? 'disabled' : ''; ?>>
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo $city['id']; ?>" <?php echo $cityId == $city['id'] ? 'selected' : ''; ?>>
                                <?php echo $city['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php if (hasRole('administrator')): ?>
                    <div class="col-md-3 mb-3">
                        <label for="employee_id" class="form-label">Assigned To</label>
                        <select class="form-select" id="employee_id" name="employee_id">
                            <option value="">All Employees</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?php echo $employee['id']; ?>" <?php echo $employeeId == $employee['id'] ? 'selected' : ''; ?>>
                                    <?php echo $employee['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                
                <div class="col-md-3 mb-3">
                    <label for="start_date" class="form-label">Follow-up Date (From)</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="end_date" class="form-label">Follow-up Date (To)</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                </div>
                
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Follow-ups Table -->
<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Follow-ups</h5>
            <span class="badge bg-primary"><?php echo $totalCount; ?> follow-ups found</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>Follow-Up Time</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>State</th>
                        <th>City</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($followUps)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <p class="text-muted mb-0">No follow-ups found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($followUps as $followUp): ?>
                            <tr>
                                <td><?php echo formatDateTime($followUp['follow_up_date']); ?></td>
                                <td>
                                    <a href="<?php echo APP_URL; ?>/leads/view?id=<?php echo $followUp['id']; ?>">
                                        <?php echo htmlspecialchars($followUp['name']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($followUp['phone']); ?></td>
                                <td><?php echo htmlspecialchars($followUp['email']); ?></td>
                                <td><?php echo htmlspecialchars($followUp['state_name']); ?></td>
                                <td><?php echo htmlspecialchars($followUp['city_name']); ?></td>
                                <td><?php echo getStatusBadge($followUp['status']); ?></td>
                                <td><?php echo htmlspecialchars($followUp['assigned_to_name'] ?? 'Not Assigned'); ?></td>
                                <td>
                                    <a href="<?php echo APP_URL; ?>/leads/view?id=<?php echo $followUp['id']; ?>" class="btn btn-sm btn-info" title="View Lead">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($followUp['status'] !== 'win' && $followUp['status'] !== 'lost'): ?>
                                    <a href="<?php echo APP_URL; ?>/leads/update-status?id=<?php echo $followUp['id']; ?>" class="btn btn-sm btn-success" title="Add Call Log">
                                        <i class="fas fa-phone"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-light">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo APP_URL; ?>/followups?page=1<?php echo getQueryString(['page']); ?>" aria-label="First">
                                <span aria-hidden="true">&laquo;&laquo;</span>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo APP_URL; ?>/followups?page=<?php echo $page - 1; ?><?php echo getQueryString(['page']); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $startPage + 4);
                    if ($endPage - $startPage < 4) {
                        $startPage = max(1, $endPage - 4);
                    }
                    ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo APP_URL; ?>/followups?page=<?php echo $i; ?><?php echo getQueryString(['page']); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo APP_URL; ?>/followups?page=<?php echo $page + 1; ?><?php echo getQueryString(['page']); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo APP_URL; ?>/followups?page=<?php echo $totalPages; ?><?php echo getQueryString(['page']); ?>" aria-label="Last">
                                <span aria-hidden="true">&raquo;&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // State-City Dependency
        const stateSelect = document.getElementById('state_id');
        const citySelect = document.getElementById('city_id');
        
        stateSelect.addEventListener('change', function() {
            if (this.value) {
                citySelect.disabled = true;
                citySelect.innerHTML = '<option value="">Loading...</option>';
                
                fetch(`<?php echo APP_URL; ?>/cities/get-by-state?state_id=${this.value}`)
                    .then(response => response.json())
                    .then(data => {
                        citySelect.innerHTML = '<option value="">All Cities</option>';
                        
                        data.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.id;
                            option.textContent = city.name;
                            citySelect.appendChild(option);
                        });
                        
                        citySelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error fetching cities:', error);
                        citySelect.innerHTML = '<option value="">All Cities</option>';
                        citySelect.disabled = false;
                    });
            } else {
                citySelect.innerHTML = '<option value="">All Cities</option>';
                citySelect.disabled = true;
            }
        });
        
        // Reset Filters Button
        document.getElementById('resetFiltersBtn').addEventListener('click', function() {
            window.location.href = '<?php echo APP_URL; ?>/followups';
        });
        
        // Refresh Button
        document.getElementById('refreshBtn').addEventListener('click', function() {
            window.location.reload();
        });
    });
</script>

<?php include 'views/templates/footer.php'; ?>
