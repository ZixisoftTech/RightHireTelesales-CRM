<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo APP_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <?php if (isset($extraCss)): ?>
        <?php echo $extraCss; ?>
    <?php endif; ?>
</head>
<body>
    <?php if (isLoggedIn()): ?>
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?php echo APP_URL; ?>/dashboard">
                    <?php echo APP_NAME; ?>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $route == 'dashboard' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $route == 'leads' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/leads">
                                <i class="fas fa-users"></i> Leads
                            </a>
                        </li>
                        
                        <?php if (hasRole('administrator')): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle <?php echo in_array($route, ['states', 'cities']) ? 'active' : ''; ?>" href="#" id="geoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-map-marker-alt"></i> Geography
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="geoDropdown">
                                    <li>
                                        <a class="dropdown-item" href="<?php echo APP_URL; ?>/states">
                                            <i class="fas fa-flag"></i> States
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo APP_URL; ?>/cities">
                                            <i class="fas fa-city"></i> Cities
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link <?php echo $route == 'users' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/users">
                                    <i class="fas fa-user-tie"></i> Employees
                                </a>
                            </li>
                            
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle <?php echo in_array($route, ['import', 'export']) ? 'active' : ''; ?>" href="#" id="importExportDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-file-import"></i> Import/Export
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="importExportDropdown">
                                    <li>
                                        <a class="dropdown-item" href="<?php echo APP_URL; ?>/import">
                                            <i class="fas fa-file-import"></i> Import Leads
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo APP_URL; ?>/export">
                                            <i class="fas fa-file-export"></i> Export Leads
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/users/profile">
                                        <i class="fas fa-user-cog"></i> Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/auth/logout">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    <?php endif; ?>
    
    <!-- Main Content -->
    <div class="container-fluid mt-4">
        <?php
        // Display flash messages
        $flash = getFlashMessage();
        if ($flash) {
            $alertClass = 'alert-info';
            
            if ($flash['type'] == 'success') {
                $alertClass = 'alert-success';
            } elseif ($flash['type'] == 'error') {
                $alertClass = 'alert-danger';
            } elseif ($flash['type'] == 'warning') {
                $alertClass = 'alert-warning';
            }
        ?>
            <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
                <?php echo $flash['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>

