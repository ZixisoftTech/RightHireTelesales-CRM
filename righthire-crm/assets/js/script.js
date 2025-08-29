/**
 * Right Hire CRM - Custom JavaScript
 * Modern, interactive UI with micro-interactions
 */

// Document Ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initTooltips();
    
    // Initialize animations
    initAnimations();
    
    // Initialize form validations
    initFormValidations();
    
    // Initialize status-dependent fields
    initStatusDependentFields();
    
    // Initialize sidebar toggle
    initSidebarToggle();
    
    // Initialize card hover effects
    initCardHoverEffects();
    
    // Initialize action button effects
    initActionButtonEffects();
    
    // Initialize notification badges
    initNotificationBadges();
});

/**
 * Initialize tooltips
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover'
        });
    });
}

/**
 * Initialize animations
 */
function initAnimations() {
    // Animate cards on page load
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
    
    // Animate sidebar menu items
    const menuItems = document.querySelectorAll('.sidebar ul li a');
    menuItems.forEach((item, index) => {
        item.style.transitionDelay = `${index * 0.05}s`;
    });
}

/**
 * Initialize form validations
 */
function initFormValidations() {
    // Get all forms with the class 'needs-validation'
    const forms = document.querySelectorAll('.needs-validation');
    
    // Loop over them and prevent submission
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Show validation messages
                const invalidFields = form.querySelectorAll(':invalid');
                invalidFields.forEach(field => {
                    field.classList.add('is-invalid');
                    
                    // Add shake animation to invalid fields
                    field.classList.add('shake-animation');
                    setTimeout(() => {
                        field.classList.remove('shake-animation');
                    }, 500);
                });
                
                // Scroll to first invalid field
                if (invalidFields.length > 0) {
                    invalidFields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    invalidFields[0].focus();
                }
                
                // Show error toast
                showToast('Please check the form for errors', 'error');
            } else {
                // Add loading state to submit button
                const submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                    submitBtn.disabled = true;
                }
            }
            
            form.classList.add('was-validated');
        }, false);
        
        // Reset validation on input change
        form.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('input', () => {
                if (input.checkValidity()) {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                } else {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                }
            });
        });
    });
}

/**
 * Initialize status-dependent fields
 */
function initStatusDependentFields() {
    const statusSelect = document.getElementById('status');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const status = this.value;
            
            // Hide all conditional fields first
            document.querySelectorAll('.status-dependent').forEach(el => {
                el.style.display = 'none';
            });
            
            // Show relevant fields based on status
            if (status === 'follow_up') {
                document.getElementById('follow_up_date_group').style.display = 'block';
            } else if (status === 'other') {
                document.getElementById('other_reason_group').style.display = 'block';
            }
        });
        
        // Trigger change event on page load
        statusSelect.dispatchEvent(new Event('change'));
    }
}

/**
 * Initialize sidebar toggle
 */
function initSidebarToggle() {
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    const sidebarToggleBtn = document.querySelector('.sidebar-toggle-btn');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    
    if (sidebarCollapse) {
        sidebarCollapse.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            content.classList.toggle('active');
        });
    }
    
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            content.classList.toggle('active');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickInsideToggleBtn = sidebarCollapse && sidebarCollapse.contains(event.target);
        
        if (window.innerWidth < 768 && !isClickInsideSidebar && !isClickInsideToggleBtn && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            content.classList.remove('active');
        }
    });
}

/**
 * Initialize card hover effects
 */
function initCardHoverEffects() {
    const dashboardCards = document.querySelectorAll('.card-dashboard');
    
    dashboardCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1)';
        });
    });
}

/**
 * Initialize action button effects
 */
function initActionButtonEffects() {
    const actionButtons = document.querySelectorAll('.btn-action');
    
    actionButtons.forEach(button => {
        button.addEventListener('mousedown', function(e) {
            const x = e.clientX - e.target.getBoundingClientRect().left;
            const y = e.clientY - e.target.getBoundingClientRect().top;
            
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Delete confirmation
    const deleteButtons = document.querySelectorAll('.btn-delete');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const deleteUrl = this.getAttribute('href');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = deleteUrl;
                }
            });
        });
    });
}

/**
 * Initialize notification badges
 */
function initNotificationBadges() {
    const notificationBadges = document.querySelectorAll('.badge');
    
    notificationBadges.forEach(badge => {
        badge.classList.add('animate__animated', 'animate__pulse', 'animate__infinite');
    });
}

/**
 * Show toast notification
 */
function showToast(message, type = 'success') {
    const iconMap = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    };
    
    const bgColorMap = {
        'success': '#1cc88a',
        'error': '#e74a3b',
        'warning': '#f6c23e',
        'info': '#36b9cc'
    };
    
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.style.position = 'fixed';
    toast.style.bottom = '20px';
    toast.style.right = '20px';
    toast.style.backgroundColor = bgColorMap[type];
    toast.style.color = '#fff';
    toast.style.padding = '12px 20px';
    toast.style.borderRadius = '4px';
    toast.style.boxShadow = '0 0.25rem 0.75rem rgba(0, 0, 0, 0.1)';
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.zIndex = '9999';
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 0.3s ease-in-out';
    
    const icon = document.createElement('i');
    icon.className = iconMap[type];
    icon.style.marginRight = '10px';
    
    const messageSpan = document.createElement('span');
    messageSpan.textContent = message;
    
    toast.appendChild(icon);
    toast.appendChild(messageSpan);
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.opacity = '1';
    }, 10);
    
    // Animate out after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

/**
 * Format date
 */
function formatDate(date) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(date).toLocaleDateString(undefined, options);
}

/**
 * Format date and time
 */
function formatDateTime(dateTime) {
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateTime).toLocaleDateString(undefined, options);
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
}

/**
 * Format number with commas
 */
function formatNumber(number) {
    return new Intl.NumberFormat().format(number);
}

/**
 * Truncate text
 */
function truncateText(text, length = 50) {
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copied to clipboard!', 'success');
    }).catch(err => {
        showToast('Failed to copy text', 'error');
    });
}

/**
 * Debounce function
 */
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function
 */
function throttle(func, limit = 300) {
    let inThrottle;
    return function executedFunction(...args) {
        if (!inThrottle) {
            func(...args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Get status badge HTML
 */
function getStatusBadge(status) {
    const statusMap = {
        'new': { class: 'bg-primary', icon: 'fas fa-user-plus' },
        'follow_up': { class: 'bg-warning', icon: 'fas fa-calendar-alt' },
        'not_attend': { class: 'bg-secondary', icon: 'fas fa-user-times' },
        'wrong_number': { class: 'bg-danger', icon: 'fas fa-phone-slash' },
        'other': { class: 'bg-secondary', icon: 'fas fa-question-circle' },
        'dead': { class: 'bg-danger', icon: 'fas fa-skull' },
        'interested': { class: 'bg-info', icon: 'fas fa-thumbs-up' },
        'win': { class: 'bg-success', icon: 'fas fa-trophy' }
    };
    
    const statusInfo = statusMap[status] || { class: 'bg-secondary', icon: 'fas fa-question-circle' };
    const statusText = status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
    
    return `<span class="badge ${statusInfo.class}"><i class="${statusInfo.icon} me-1"></i> ${statusText}</span>`;
}

