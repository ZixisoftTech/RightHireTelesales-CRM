<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add New Lead</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/leads" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Leads
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Lead Information</h5>
    </div>
    <div class="card-body">
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <form action="<?php echo APP_URL; ?>/leads/create" method="POST" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        <label for="name" class="required">Full Name</label>
                        <div class="invalid-feedback">Please enter the lead's name.</div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <label for="email">Email Address</label>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-floating">
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone Number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                        <label for="phone" class="required">Phone Number</label>
                        <div class="invalid-feedback">Please enter the lead's phone number.</div>
                    </div>
                </div>
                
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
            </div>
            
            <div class="row">
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
                        <label for="city_id">City</label>
                        <div class="invalid-feedback">Please select a city.</div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="form-floating">
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="new" <?php echo (isset($_POST['status']) && $_POST['status'] == 'new') ? 'selected' : ''; ?>>New</option>
                            <option value="follow_up" <?php echo (isset($_POST['status']) && $_POST['status'] == 'follow_up') ? 'selected' : ''; ?>>Follow-up</option>
                            <option value="not_attend" <?php echo (isset($_POST['status']) && $_POST['status'] == 'not_attend') ? 'selected' : ''; ?>>Not Attend</option>
                            <option value="wrong_number" <?php echo (isset($_POST['status']) && $_POST['status'] == 'wrong_number') ? 'selected' : ''; ?>>Wrong Number</option>
                            <option value="other" <?php echo (isset($_POST['status']) && $_POST['status'] == 'other') ? 'selected' : ''; ?>>Other</option>
                            <option value="dead" <?php echo (isset($_POST['status']) && $_POST['status'] == 'dead') ? 'selected' : ''; ?>>Dead</option>
                            <option value="interested" <?php echo (isset($_POST['status']) && $_POST['status'] == 'interested') ? 'selected' : ''; ?>>Interested</option>
                            <option value="win" <?php echo (isset($_POST['status']) && $_POST['status'] == 'win') ? 'selected' : ''; ?>>Win</option>
                        </select>
                        <label for="status" class="required">Status</label>
                        <div class="invalid-feedback">Please select a status.</div>
                    </div>
                </div>
            </div>
            
            <div class="row status-dependent" id="follow_up_date_group" style="display: none;">
                <div class="col-md-6 mb-3">
                    <div class="form-floating">
                        <input type="text" class="form-control datetimepicker" id="follow_up_date" name="follow_up_date" placeholder="Follow-up Date & Time" value="<?php echo isset($_POST['follow_up_date']) ? htmlspecialchars($_POST['follow_up_date']) : ''; ?>">
                        <label for="follow_up_date">Follow-up Date & Time</label>
                        <div class="invalid-feedback">Please select a follow-up date and time.</div>
                    </div>
                </div>
            </div>
            
            <div class="row status-dependent" id="other_reason_group" style="display: none;">
                <div class="col-md-6 mb-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="other_reason" name="other_reason" placeholder="Other Reason" value="<?php echo isset($_POST['other_reason']) ? htmlspecialchars($_POST['other_reason']) : ''; ?>">
                        <label for="other_reason">Other Reason</label>
                        <div class="invalid-feedback">Please provide a reason.</div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-floating">
                        <textarea class="form-control" id="address" name="address" placeholder="Address" style="height: 100px;"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                        <label for="address">Address</label>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-floating">
                        <textarea class="form-control" id="remarks" name="remarks" placeholder="Remarks" style="height: 100px;"><?php echo isset($_POST['remarks']) ? htmlspecialchars($_POST['remarks']) : ''; ?></textarea>
                        <label for="remarks">Remarks</label>
                    </div>
                </div>
            </div>
            
            <?php if (hasRole('administrator')): ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <select class="form-select" id="assigned_to" name="assigned_to">
                                <option value="">Assign to Employee</option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?php echo $employee['id']; ?>" <?php echo (isset($_POST['assigned_to']) && $_POST['assigned_to'] == $employee['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($employee['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="assigned_to">Assign To</label>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="reset" class="btn btn-outline-secondary">
                    <i class="fas fa-undo"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Lead
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize status-dependent fields
        const statusSelect = document.getElementById('status');
        const followUpDateGroup = document.getElementById('follow_up_date_group');
        const otherReasonGroup = document.getElementById('other_reason_group');
        const followUpDateInput = document.getElementById('follow_up_date');
        const otherReasonInput = document.getElementById('other_reason');
        
        statusSelect.addEventListener('change', function() {
            // Hide all conditional fields first
            followUpDateGroup.style.display = 'none';
            otherReasonGroup.style.display = 'none';
            
            // Remove required attribute
            followUpDateInput.removeAttribute('required');
            otherReasonInput.removeAttribute('required');
            
            // Show relevant fields based on status
            if (this.value === 'follow_up') {
                followUpDateGroup.style.display = 'flex';
                followUpDateInput.setAttribute('required', 'required');
            } else if (this.value === 'other') {
                otherReasonGroup.style.display = 'flex';
                otherReasonInput.setAttribute('required', 'required');
            }
        });
        
        // Trigger change event on page load
        statusSelect.dispatchEvent(new Event('change'));
        
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
                        citySelect.innerHTML = '<option value="">Select City</option>';
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
            }
            
            form.classList.add('was-validated');
        });
    });
</script>

<?php include 'views/templates/footer.php'; ?>

