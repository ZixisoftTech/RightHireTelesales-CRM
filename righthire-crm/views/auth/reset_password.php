<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo APP_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo APP_URL; ?>/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo APP_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.10.3/dist/cdn.min.js"></script>
</head>
<body>
    <div class="auth-container">
        <div class="auth-content">
            <div class="text-center mb-4">
                <img src="<?php echo APP_URL; ?>/assets/img/logo.png" alt="<?php echo APP_NAME; ?>" class="img-fluid mb-3" style="max-height: 80px;">
                <h2 class="text-primary fw-bold">Reset Password</h2>
                <p class="text-muted">Create a new password for your account</p>
            </div>
            
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
            
            <?php echo displayFlashMessage(); ?>
            
            <form method="POST" action="<?php echo APP_URL; ?>/auth/reset-password?token=<?php echo $_GET['token']; ?>" class="needs-validation" novalidate x-data="{ showPassword: false, showConfirmPassword: false }">
                <div class="mb-4">
                    <div class="form-floating">
                        <input :type="showPassword ? 'text' : 'password'" class="form-control" id="password" name="password" placeholder="New Password" required minlength="6">
                        <label for="password"><i class="fas fa-lock me-2"></i>New Password</label>
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-decoration-none" style="z-index: 5;" @click="showPassword = !showPassword">
                            <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    <div class="form-text">Password must be at least 6 characters long.</div>
                </div>
                
                <div class="mb-4">
                    <div class="form-floating">
                        <input :type="showConfirmPassword ? 'text' : 'password'" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required minlength="6">
                        <label for="confirm_password"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-decoration-none" style="z-index: 5;" @click="showConfirmPassword = !showConfirmPassword">
                            <i class="fas" :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">Passwords do not match.</div>
                </div>
                
                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i> Reset Password
                    </button>
                </div>
                
                <div class="text-center">
                    <a href="<?php echo APP_URL; ?>/auth/login" class="text-primary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Login
                    </a>
                </div>
            </form>
        </div>
        
        <div class="auth-footer text-center mt-4">
            <p class="text-white">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form validation
        (function() {
            'use strict';
            
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation');
            
            // Loop over them and prevent submission
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    } else {
                        // Check if passwords match
                        const password = document.getElementById('password');
                        const confirmPassword = document.getElementById('confirm_password');
                        
                        if (password.value !== confirmPassword.value) {
                            event.preventDefault();
                            event.stopPropagation();
                            confirmPassword.setCustomValidity('Passwords do not match');
                            confirmPassword.classList.add('is-invalid');
                        } else {
                            confirmPassword.setCustomValidity('');
                            
                            // Add loading state to submit button
                            const submitBtn = form.querySelector('[type="submit"]');
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Resetting...';
                            submitBtn.disabled = true;
                        }
                    }
                    
                    form.classList.add('was-validated');
                }, false);
                
                // Check password match on input
                const confirmPassword = document.getElementById('confirm_password');
                const password = document.getElementById('password');
                
                if (confirmPassword && password) {
                    confirmPassword.addEventListener('input', function() {
                        if (this.value === password.value) {
                            this.setCustomValidity('');
                        } else {
                            this.setCustomValidity('Passwords do not match');
                        }
                    });
                    
                    password.addEventListener('input', function() {
                        if (confirmPassword.value === this.value) {
                            confirmPassword.setCustomValidity('');
                        } else {
                            confirmPassword.setCustomValidity('Passwords do not match');
                        }
                    });
                }
            });
        })();
    </script>
</body>
</html>

