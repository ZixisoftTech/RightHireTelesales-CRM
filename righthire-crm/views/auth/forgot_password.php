<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo APP_NAME; ?></title>
    
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
                <h2 class="text-primary fw-bold">Forgot Password</h2>
                <p class="text-muted">Enter your email to reset your password</p>
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
            
            <form method="POST" action="<?php echo APP_URL; ?>/auth/forgot-password" class="needs-validation" novalidate>
                <div class="mb-4">
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                        <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                    </div>
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>
                
                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane me-2"></i> Send Reset Link
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
                        // Add loading state to submit button
                        const submitBtn = form.querySelector('[type="submit"]');
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
                        submitBtn.disabled = true;
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>

