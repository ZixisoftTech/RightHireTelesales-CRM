<?php if (isLoggedIn()): ?>
                </div>
                <!-- End Main Content -->
            </div>
            <!-- End Page Content -->
        </div>
        <!-- End Wrapper -->
    <?php else: ?>
            </div>
            <!-- End Auth Content -->
            
            <div class="auth-footer text-center mt-4">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
        <!-- End Auth Container -->
    <?php endif; ?>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo APP_URL; ?>/assets/js/script.js"></script>
    
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('.datatable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        previous: '<i class="fas fa-angle-left"></i>',
                        next: '<i class="fas fa-angle-right"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>'
                    }
                }
            });
            
            // Initialize Flatpickr for date inputs
            $(".datepicker").flatpickr({
                dateFormat: "Y-m-d",
                allowInput: true,
                altInput: true,
                altFormat: "F j, Y",
                disableMobile: true
            });
            
            // Initialize Flatpickr for datetime inputs
            $(".datetimepicker").flatpickr({
                dateFormat: "Y-m-d H:i",
                enableTime: true,
                time_24hr: false,
                allowInput: true,
                altInput: true,
                altFormat: "F j, Y at h:i K",
                disableMobile: true,
                minDate: "today"
            });
            
            // Toggle sidebar on mobile
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
            
            $('.sidebar-toggle-btn').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
            
            // State-City Dropdown Dependency
            $('#state_id').on('change', function() {
                var stateId = $(this).val();
                if (stateId) {
                    $.ajax({
                        url: '<?php echo APP_URL; ?>/cities/get-by-state',
                        type: 'GET',
                        data: { state_id: stateId },
                        dataType: 'json',
                        success: function(response) {
                            $('#city_id').empty();
                            $('#city_id').append('<option value="">Select City</option>');
                            
                            if (response.cities && response.cities.length > 0) {
                                $.each(response.cities, function(key, city) {
                                    $('#city_id').append('<option value="' + city.id + '">' + city.name + '</option>');
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching cities:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to load cities. Please try again.'
                            });
                        }
                    });
                } else {
                    $('#city_id').empty();
                    $('#city_id').append('<option value="">Select City</option>');
                }
            });
            
            // Status-dependent fields in lead status update
            $('#status').on('change', function() {
                var status = $(this).val();
                
                // Hide all conditional fields first
                $('.status-dependent').hide();
                
                // Show relevant fields based on status
                if (status === 'follow_up') {
                    $('#follow_up_date_group').show();
                } else if (status === 'other') {
                    $('#other_reason_group').show();
                }
            });
            
            // Trigger change event on page load to set initial state
            $('#status').trigger('change');
            
            // Confirm delete actions
            $('.btn-delete').on('click', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).attr('href');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });
            
            // Add animation classes to cards
            $('.card').addClass('animate-card');
            
            // Add hover effect to action buttons
            $('.btn-action').addClass('btn-hover-effect');
            
            // Add tooltip to buttons with title attribute
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>
