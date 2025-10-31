<?php
/**
 * Cabinet360 - Sidebar Navigation
 */

// Determine active page
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-balance-scale"></i>
            <span class="logo-text">Cabinet360</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'index' || $current_page == 'dashboard') ? 'active' : ''; ?>" 
                   href="<?php echo APP_URL; ?>/index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de Bord</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'clients' ? 'active' : ''; ?>" 
                   href="<?php echo APP_URL; ?>/pages/clients.php">
                    <i class="fas fa-users"></i>
                    <span>Clients</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'cases' ? 'active' : ''; ?>" 
                   href="<?php echo APP_URL; ?>/pages/cases.php">
                    <i class="fas fa-briefcase"></i>
                    <span>Dossiers</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'appointments' ? 'active' : ''; ?>" 
                   href="<?php echo APP_URL; ?>/pages/appointments.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Rendez-vous</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'payments' ? 'active' : ''; ?>" 
                   href="<?php echo APP_URL; ?>/pages/payments.php">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Paiements</span>
                </a>
            </li>
            
            <li class="nav-divider"></li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'reports' ? 'active' : ''; ?>" 
                   href="<?php echo APP_URL; ?>/pages/reports.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Rapports</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'documents' ? 'active' : ''; ?>" 
                   href="<?php echo APP_URL; ?>/pages/documents.php">
                    <i class="fas fa-folder-open"></i>
                    <span>Documents</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'tasks' ? 'active' : ''; ?>" 
                   href="<?php echo APP_URL; ?>/pages/tasks.php">
                    <i class="fas fa-tasks"></i>
                    <span>Tâches</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'settings' ? 'active' : ''; ?>" 
                   href="<?php echo APP_URL; ?>/pages/settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
            </li>
            
            <li class="nav-divider"></li>
            
            <li class="nav-item">
                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#searchModal">
                    <i class="fas fa-search"></i>
                    <span>Recherche</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?php echo APP_URL; ?>/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <div class="text-center">
            <small class="text-muted">© 2025 Cabinet360</small>
        </div>
    </div>
</aside>

<!-- Global Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header border-gold">
                <h5 class="modal-title text-gold" id="searchModalLabel">
                    <i class="fas fa-search"></i> Recherche Globale
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control mb-3" id="globalSearch" 
                       placeholder="Rechercher par nom, CIN, numéro de dossier...">
                <div id="searchResults" class="search-results">
                    <p class="text-muted text-center">Entrez un terme de recherche...</p>
                </div>
            </div>
        </div>
    </div>
</div>

