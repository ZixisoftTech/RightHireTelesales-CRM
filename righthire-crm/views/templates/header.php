<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo APP_URL; ?>/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Flatpickr CSS -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo APP_URL; ?>/assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.10.3/dist/cdn.min.js"></script>
</head>
<body>
    <?php if (isLoggedIn()): ?>
        <div class="wrapper">
            <!-- Sidebar -->
            <nav id="sidebar" class="sidebar">
                <div class="sidebar-header">
                    <h3><?php echo APP_NAME; ?></h3>
                    <div class="sidebar-toggle-btn d-md-none">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
                
                <div class="sidebar-user">
                    <div class="user-info">
                        <img src="<?php echo APP_URL; ?>/assets/img/user-avatar.png" alt="User Avatar" class="user-avatar">
                        <div class="user-details">
                            <h6 class="mb-0"><?php echo getCurrentUserName(); ?></h6>
                            <span class="user-role"><?php echo ucfirst(getCurrentUserRole()); ?></span>
                        </div>
                    </div>
                </div>
                
                <ul class="list-unstyled components">
                    <li class="<?php echo $route === 'dashboard' ? 'active' : ''; ?>">
                        <a href="<?php echo APP_URL; ?>/dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    
                    <li class="<?php echo strpos($route, 'followups') === 0 ? 'active' : ''; ?>">
                        <a href="<?php echo APP_URL; ?>/followups">
                            <i class="fas fa-calendar-check"></i> Follow-ups
                        </a>
                    </li>
                    
                    <li class="<?php echo strpos($route, 'leads') === 0 ? 'active' : ''; ?>">
                        <a href="#leadsSubmenu" data-bs-toggle="collapse" aria-expanded="<?php echo strpos($route, 'leads') === 0 ? 'true' : 'false'; ?>" class="dropdown-toggle">
                            <i class="fas fa-user-tag"></i> Leads
                        </a>
                        <ul class="collapse list-unstyled <?php echo strpos($route, 'leads') === 0 ? 'show' : ''; ?>" id="leadsSubmenu">
                            <li>
                                <a href="<?php echo APP_URL; ?>/leads">
                                    <i class="fas fa-list"></i> All Leads
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo APP_URL; ?>/leads/create">
                                    <i class="fas fa-plus"></i> Add New
                                </a>
                            </li>
                            <?php if (hasRole('administrator')): ?>
                                <li>
                                    <a href="<?php echo APP_URL; ?>/leads/import">
                                        <i class="fas fa-file-import"></i> Import
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    
                    <?php if (hasRole('administrator')): ?>
                        <li class="<?php echo strpos($route, 'states') === 0 ? 'active' : ''; ?>">
                            <a href="<?php echo APP_URL; ?>/states">
                                <i class="fas fa-map"></i> States
                            </a>
                        </li>
                        
                        <li class="<?php echo strpos($route, 'cities') === 0 ? 'active' : ''; ?>">
                            <a href="<?php echo APP_URL; ?>/cities">
                                <i class="fas fa-city"></i> Cities
                            </a>
                        </li>
                        
                        <li class="<?php echo strpos($route, 'users') === 0 ? 'active' : ''; ?>">
                            <a href="#usersSubmenu" data-bs-toggle="collapse" aria-expanded="<?php echo strpos($route, 'users') === 0 ? 'true' : 'false'; ?>" class="dropdown-toggle">
                                <i class="fas fa-users"></i> Users
                            </a>
                            <ul class="collapse list-unstyled <?php echo strpos($route, 'users') === 0 ? 'show' : ''; ?>" id="usersSubmenu">
                                <li>
                                    <a href="<?php echo APP_URL; ?>/users">
                                        <i class="fas fa-list"></i> All Users
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo APP_URL; ?>/users/create">
                                        <i class="fas fa-user-plus"></i> Add New
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    

                    
                    <li>
                        <a href="<?php echo APP_URL; ?>/users/profile">
                            <i class="fas fa-user-circle"></i> My Profile
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?php echo APP_URL; ?>/auth/logout" onclick="return confirm('Are you sure you want to logout?');">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </nav>
            
            <!-- Page Content -->
            <div id="content">
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                    <div class="container-fluid">
                        <button type="button" id="sidebarCollapse" class="btn btn-primary d-md-none">
                            <i class="fas fa-bars"></i>
                        </button>
                        
                        <div class="d-flex align-items-center ms-auto">
                            <?php if (isset($todayFollowUps) && count($todayFollowUps) > 0): ?>
                                <div class="dropdown me-3">
                                    <button class="btn btn-outline-primary position-relative" type="button" id="followUpDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-bell"></i>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            <?php echo count($todayFollowUps); ?>
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="followUpDropdown">
                                        <li><h6 class="dropdown-header">Today's Follow-ups</h6></li>
                                        <?php foreach ($todayFollowUps as $followUp): ?>
                                            <li>
                                                <a class="dropdown-item" href="<?php echo APP_URL; ?>/leads/view?id=<?php echo $followUp['id']; ?>">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-grow-1">
                                                            <strong><?php echo htmlspecialchars($followUp['name']); ?></strong>
                                                            <div class="small text-muted">
                                                                <?php echo date('h:i A', strtotime($followUp['follow_up_date'])); ?>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <i class="fas fa-chevron-right text-muted"></i>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <div class="dropdown">
                                <button class="btn btn-link dropdown-toggle text-decoration-none" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="<?php echo APP_URL; ?>/assets/img/user-avatar.png" alt="User Avatar" class="user-avatar-sm me-1">
                                    <span class="d-none d-md-inline"><?php echo getCurrentUserName(); ?></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li>
                                        <a class="dropdown-item" href="<?php echo APP_URL; ?>/users/profile">
                                            <i class="fas fa-user-circle me-2"></i> My Profile
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo APP_URL; ?>/auth/logout" onclick="return confirm('Are you sure you want to logout?');">
                                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
                
                <!-- Main Content -->
                <div class="container-fluid p-4">
                    <?php echo displayFlashMessage(); ?>
    <?php else: ?>
        <!-- Login/Register Pages -->
        <div class="auth-container">
            <div class="auth-content">
                <div class="auth-header text-center mb-4">
                    <h2><?php echo APP_NAME; ?></h2>
                </div>
                
                <?php echo displayFlashMessage(); ?>
    <?php endif; ?>
