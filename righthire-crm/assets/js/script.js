/**
 * Right Hire CRM Custom JavaScript
 */

// Document Ready
$(document).ready(function() {
    // Initialize DataTables
    if ($.fn.DataTable) {
        $('.datatable').DataTable({
            responsive: true,
            pageLength: 25,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search..."
            }
        });
    }
    
    // Initialize Tooltips
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Initialize Popovers
    if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }
    
    // Confirm Delete
    $('.confirm-delete').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
    
    // Toggle Status
    $('.toggle-status').on('click', function(e) {
        if (!confirm('Are you sure you want to change the status of this item?')) {
            e.preventDefault();
        }
    });
    
    // Dynamic Form Fields
    handleDynamicFormFields();
    
    // CSV Import Preview
    handleCsvImport();
    
    // Lead Status Change
    handleLeadStatusChange();
    
    // City Dropdown Population
    handleCityDropdown();
    
    // Print Button
    $('.btn-print').on('click', function() {
        window.print();
    });
    
    // Export Button
    $('.btn-export').on('click', function() {
        var exportUrl = $(this).data('url');
        if (exportUrl) {
            window.location.href = exportUrl;
        }
    });
    
    // Form Submission Loading
    $('form').on('submit', function() {
        showLoading();
    });
    
    // AJAX Loading
    $(document).ajaxStart(function() {
        showLoading();
    }).ajaxStop(function() {
        hideLoading();
    });
});

/**
 * Handle dynamic form fields
 */
function handleDynamicFormFields() {
    // Show/hide fields based on lead status
    $('#lead-status').on('change', function() {
        var status = $(this).val();
        
        // Hide all conditional fields first
        $('.conditional-field').hide();
        
        // Show fields based on selected status
        if (status === 'follow_up') {
            $('#follow-up-date-field').show();
        } else if (status === 'other') {
            $('#other-reason-field').show();
        }
    });
    
    // Trigger change event on page load
    $('#lead-status').trigger('change');
}

/**
 * Handle CSV import preview
 */
function handleCsvImport() {
    $('#csv-file').on('change', function() {
        var fileInput = this;
        var fileName = fileInput.files[0]?.name || 'No file chosen';
        
        // Update file name display
        $('.custom-file-label').text(fileName);
        
        // Enable/disable submit button
        if (fileInput.files.length > 0) {
            $('#import-submit').prop('disabled', false);
        } else {
            $('#import-submit').prop('disabled', true);
        }
    });
}

/**
 * Handle lead status change
 */
function handleLeadStatusChange() {
    $('#status-form #status').on('change', function() {
        var status = $(this).val();
        
        // Hide all conditional fields first
        $('.status-conditional').hide();
        
        // Show fields based on selected status
        if (status === 'follow_up') {
            $('#follow-up-date-container').show();
            $('#follow_up_date').prop('required', true);
        } else {
            $('#follow_up_date').prop('required', false);
        }
        
        if (status === 'other') {
            $('#other-reason-container').show();
            $('#other_reason').prop('required', true);
        } else {
            $('#other_reason').prop('required', false);
        }
    });
    
    // Trigger change event on page load
    $('#status-form #status').trigger('change');
}

/**
 * Handle city dropdown population based on selected state
 */
function handleCityDropdown() {
    $('#state_id').on('change', function() {
        var stateId = $(this).val();
        var cityDropdown = $('#city_id');
        
        if (stateId) {
            // Show loading
            cityDropdown.html('<option value="">Loading...</option>');
            
            // Fetch cities via AJAX
            $.ajax({
                url: APP_URL + '/api?api_route=cities&state_id=' + stateId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // Clear dropdown
                    cityDropdown.empty();
                    
                    // Add default option
                    cityDropdown.append('<option value="">Select City</option>');
                    
                    // Add cities to dropdown
                    if (response.cities && response.cities.length > 0) {
                        $.each(response.cities, function(index, city) {
                            cityDropdown.append('<option value="' + city.id + '">' + city.name + '</option>');
                        });
                    } else {
                        cityDropdown.append('<option value="">No cities found</option>');
                    }
                },
                error: function() {
                    cityDropdown.html('<option value="">Error loading cities</option>');
                }
            });
        } else {
            // Clear dropdown if no state selected
            cityDropdown.html('<option value="">Select State First</option>');
        }
    });
    
    // Trigger change event if state is already selected (e.g., on edit form)
    if ($('#state_id').val()) {
        $('#state_id').trigger('change');
    }
}

/**
 * Show loading spinner
 */
function showLoading() {
    // Check if spinner already exists
    if ($('.spinner-overlay').length === 0) {
        var spinner = '<div class="spinner-overlay">' +
                      '<div class="spinner-border text-primary" role="status">' +
                      '<span class="visually-hidden">Loading...</span>' +
                      '</div>' +
                      '</div>';
        
        $('body').append(spinner);
    }
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    $('.spinner-overlay').remove();
}

/**
 * Format date for display
 * 
 * @param {string} dateString
 * @param {string} format
 * @return {string}
 */
function formatDate(dateString, format = 'YYYY-MM-DD') {
    if (!dateString) return '';
    
    var date = new Date(dateString);
    
    if (isNaN(date.getTime())) return dateString;
    
    var year = date.getFullYear();
    var month = (date.getMonth() + 1).toString().padStart(2, '0');
    var day = date.getDate().toString().padStart(2, '0');
    var hours = date.getHours().toString().padStart(2, '0');
    var minutes = date.getMinutes().toString().padStart(2, '0');
    var seconds = date.getSeconds().toString().padStart(2, '0');
    
    format = format.replace('YYYY', year);
    format = format.replace('MM', month);
    format = format.replace('DD', day);
    format = format.replace('HH', hours);
    format = format.replace('mm', minutes);
    format = format.replace('ss', seconds);
    
    return format;
}

/**
 * Format number with commas
 * 
 * @param {number} number
 * @return {string}
 */
function formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Validate form
 * 
 * @param {HTMLFormElement} form
 * @return {boolean}
 */
function validateForm(form) {
    // Check if HTML5 validation is supported
    if (form.checkValidity) {
        if (!form.checkValidity()) {
            // Trigger HTML5 validation
            $('<input type="submit">').hide().appendTo(form).click().remove();
            return false;
        }
    }
    
    return true;
}

