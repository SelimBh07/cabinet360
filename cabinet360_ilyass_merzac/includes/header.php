<?php
/**
 * Cabinet360 - Header Include
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title><?php echo $page_title ?? 'Dashboard'; ?> - <?php echo APP_NAME; ?></title>
    
    <!-- PWA Meta Tags -->
    <meta name="description" content="Cabinet360 - Système de gestion pour cabinet d'avocat professionnel">
    <meta name="theme-color" content="#007bff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Cabinet360">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?php echo APP_URL; ?>/manifest.json">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo APP_URL; ?>/assets/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?php echo APP_URL; ?>/assets/icons/icon-512x512.png">
    <link rel="apple-touch-icon" href="<?php echo APP_URL; ?>/assets/icons/icon-192x192.png">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    
    <!-- Chart.js (for dashboard) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    
    <!-- FullCalendar (for appointments) -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php require_once __DIR__ . '/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Navbar -->
            <nav class="navbar navbar-dark top-navbar">
                <div class="container-fluid">
                    <button class="btn btn-link text-white sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="navbar-brand d-md-none">
                        <i class="fas fa-balance-scale text-gold"></i>
                        <span class="ms-2"><?php echo APP_NAME; ?></span>
                    </div>
                    
                    <div class="d-flex align-items-center ms-auto">
                        <!-- SaaS Cabinet Info -->
                        <div class="me-3 text-white d-none d-md-block">
                            <small class="text-gold"><i class="fas fa-building"></i> <?php echo htmlspecialchars($cabinet_name); ?></small>
                        </div>
                        
                        <div class="dropdown">
                            <a href="#" class="nav-link dropdown-toggle text-white" id="userDropdown" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle fa-lg me-2"></i>
                                <span class="d-none d-md-inline"><?php echo htmlspecialchars($lawyer_name); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark" aria-labelledby="userDropdown">
                                <li class="dropdown-header">
                                    <strong><?php echo htmlspecialchars($cabinet_name); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($lawyer_email); ?></small>
                                    <br><span class="badge bg-warning mt-1"><?php echo strtoupper($subscription_plan); ?></span>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/pages/settings.php">
                                    <i class="fas fa-cog me-2"></i> Paramètres
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/pages/subscription.php">
                                    <i class="fas fa-crown me-2"></i> Mon abonnement
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- Page Content -->
            <div class="content-area">

