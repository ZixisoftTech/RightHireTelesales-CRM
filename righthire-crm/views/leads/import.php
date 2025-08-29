<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Import Leads</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/leads" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Leads
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Import Leads from CSV</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo APP_URL; ?>/leads/import" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Import Instructions</h6>
                        <p class="mb-0">Please follow these steps to import leads:</p>
                        <ol class="mb-0">
                            <li>Download the sample CSV file</li>
                            <li>Fill in the lead information (name and phone are required)</li>
                            <li>Select the state and city (optional) for all imported leads</li>
                            <li>Upload the CSV file and click Import</li>
                        </ol>
                    </div>
                    
                    <div class="mb-3">
                        <a href="<?php echo APP_URL; ?>/assets/samples/leads_import_sample.csv" class="btn btn-outline-primary" download>
                            <i class="fas fa-download"></i> Download Sample CSV
                        </a>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select class="form-select" id="state_id" name="state_id" required>
                                    <option value="">Select State</option>
                                    <?php foreach ($states as $state): ?>
                                        <option value="<?php echo $state['id']; ?>" <?php echo (isset($_POST['state_id']) && $_POST['state_id'] == $state['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($state['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="state_id" class="required">State</label>
                                <div class="invalid-feedback">Please select a state.</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select class="form-select" id="city_id" name="city_id">
                                    <option value="">Select City</option>
                                    <?php if (isset($_POST['state_id']) && !empty($_POST['state_id'])): ?>
                                        <?php foreach ($cities as $city): ?>
                                            <option value="<?php echo $city['id']; ?>" <?php echo (isset($_POST['city_id']) && $_POST['city_id'] == $city['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($city['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <label for="city_id">City (Optional)</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select class="form-select" id="assigned_to" name="assigned_to">
                                    <option value="">Assign to Employee (Optional)</option>
                                    <?php foreach ($employees as $employee): ?>
                                        <option value="<?php echo $employee['id']; ?>" <?php echo (isset($_POST['assigned_to']) && $_POST['assigned_to'] == $employee['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($employee['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="assigned_to">Assign To</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="input-group">
                                <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                                <label class="input-group-text" for="csv_file"><i class="fas fa-file-csv"></i></label>
                                <div class="invalid-feedback">Please select a CSV file.</div>
                            </div>
                            <div class="form-text">Only CSV files are accepted.</div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-import"></i> Import Leads
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">CSV Format</h5>
            </div>
            <div class="card-body">
                <p>Your CSV file should have the following columns:</p>
                
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Column</th>
                            <th>Required</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>name</td>
                            <td><span class="badge bg-danger">Yes</span></td>
                            <td>Full name of the lead</td>
                        </tr>
                        <tr>
                            <td>email</td>
                            <td><span class="badge bg-secondary">No</span></td>
                            <td>Email address</td>
                        </tr>
                        <tr>
                            <td>phone</td>
                            <td><span class="badge bg-danger">Yes</span></td>
                            <td>Phone number</td>
                        </tr>
                        <tr>
                            <td>address</td>
                            <td><span class="badge bg-secondary">No</span></td>
                            <td>Physical address</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> All imported leads will be assigned to the selected state and city (if provided).
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // State-City Dependency
        const stateSelect = document.getElementById('state_id');
        const citySelect = document.getElementById('city_id');
        
        stateSelect.addEventListener('change', function() {
            const stateId = this.value;
            
            // Clear city dropdown
            citySelect.innerHTML = '<option value="">Select City</option>';
            
            if (stateId) {
                // Show loading indicator
                citySelect.innerHTML += '<option value="" disabled>Loading cities...</option>';
                citySelect.disabled = true;
                
                // Fetch cities for selected state
                fetch(`<?php echo APP_URL; ?>/cities/get-by-state?state_id=${stateId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Remove loading indicator
                        citySelect.innerHTML = '<option value="">Select City (Optional)</option>';
                        citySelect.disabled = false;
                        
                        // Add cities to dropdown
                        if (data.cities && data.cities.length > 0) {
                            data.cities.forEach(city => {
                                const option = document.createElement('option');
                                option.value = city.id;
                                option.textContent = city.name;
                                citySelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching cities:', error);
                        citySelect.innerHTML = '<option value="">Error loading cities</option>';
                        citySelect.disabled = false;
                    });
            }
        });
        
        // Form validation
        const form = document.querySelector('.needs-validation');
        
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                // Add loading state to submit button
                const submitBtn = form.querySelector('[type="submit"]');
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importing...';
                submitBtn.disabled = true;
            }
            
            form.classList.add('was-validated');
        });
    });
</script>

<?php include 'views/templates/footer.php'; ?>

