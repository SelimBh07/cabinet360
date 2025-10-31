<?php
/**
 * Cabinet360 - Dashboard
 */

require_once 'config/auth.php';

$page_title = 'Tableau de Bord';

// Fetch statistics (MULTI-TENANT: Filtered by lawyer_id)
try {
    // Total Clients
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM clients WHERE lawyer_id = ?");
    $stmt->execute([$lawyer_id]);
    $total_clients = $stmt->fetch()['total'];
    
    // Active Cases
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM cases WHERE lawyer_id = ? AND status IN ('ouvert', 'en_cours')");
    $stmt->execute([$lawyer_id]);
    $active_cases = $stmt->fetch()['total'];
    
    // Upcoming Appointments (next 7 days)
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE lawyer_id = ? AND date >= CURDATE() AND date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status = 'planifie'");
    $stmt->execute([$lawyer_id]);
    $upcoming_appointments = $stmt->fetch()['total'];
    
    // Unpaid Invoices
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM payments WHERE lawyer_id = ? AND status = 'impayé'");
    $stmt->execute([$lawyer_id]);
    $unpaid_invoices = $stmt->fetch()['total'];
    
    // Total Revenue (this month)
    $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE lawyer_id = ? AND MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE()) AND status = 'payé'");
    $stmt->execute([$lawyer_id]);
    $monthly_revenue = $stmt->fetch()['total'];
    
    // Recent Clients
    $stmt = $conn->prepare("SELECT id, full_name, cin, phone, created_at FROM clients WHERE lawyer_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$lawyer_id]);
    $recent_clients = $stmt->fetchAll();
    
    // Upcoming Appointments
    $stmt = $conn->prepare("SELECT a.*, c.full_name as client_name FROM appointments a 
                          JOIN clients c ON a.client_id = c.id 
                          WHERE a.lawyer_id = ? AND a.date >= CURDATE() AND a.status = 'planifie'
                          ORDER BY a.date, a.time LIMIT 5");
    $stmt->execute([$lawyer_id]);
    $upcoming_appointments_list = $stmt->fetchAll();
    
    // Recent Cases
    $stmt = $conn->prepare("SELECT cs.*, cl.full_name as client_name FROM cases cs 
                          JOIN clients cl ON cs.client_id = cl.id 
                          WHERE cs.lawyer_id = ?
                          ORDER BY cs.created_at DESC LIMIT 5");
    $stmt->execute([$lawyer_id]);
    $recent_cases = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des données : " . $e->getMessage();
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-tachometer-alt"></i> Tableau de Bord
    </h1>
    <p class="page-subtitle">Vue d'ensemble de votre cabinet</p>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <a href="<?php echo APP_URL; ?>/pages/clients.php" class="stat-card-link">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $total_clients ?? 0; ?></div>
                <div class="stat-label">Total Clients</div>
            </div>
        </a>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <a href="<?php echo APP_URL; ?>/pages/cases.php" class="stat-card-link">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-number"><?php echo $active_cases ?? 0; ?></div>
                <div class="stat-label">Dossiers Actifs</div>
            </div>
        </a>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <a href="<?php echo APP_URL; ?>/pages/appointments.php" class="stat-card-link">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-number"><?php echo $upcoming_appointments ?? 0; ?></div>
                <div class="stat-label">RDV à Venir</div>
            </div>
        </a>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <a href="<?php echo APP_URL; ?>/pages/payments.php" class="stat-card-link">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-number"><?php echo $unpaid_invoices ?? 0; ?></div>
                <div class="stat-label">Factures Impayées</div>
            </div>
        </a>
    </div>
</div>

<!-- Revenue Card -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line"></i> Revenus du Mois
            </div>
            <div class="card-body text-center">
                <h2 class="text-gold"><?php echo number_format($monthly_revenue ?? 0, 2); ?> MAD</h2>
                <canvas id="revenueChart" height="60"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Clients -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-users"></i> Clients Récents</span>
                <a href="<?php echo APP_URL; ?>/pages/clients.php" class="btn btn-sm btn-primary">
                    Voir Tout
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_clients)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>CIN</th>
                                    <th>Téléphone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_clients as $client): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($client['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($client['cin']); ?></td>
                                        <td><?php echo htmlspecialchars($client['phone']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">Aucun client enregistré</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Appointments -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar-alt"></i> Rendez-vous à Venir</span>
                <a href="<?php echo APP_URL; ?>/pages/appointments.php" class="btn btn-sm btn-primary">
                    Voir Tout
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($upcoming_appointments_list)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Objet</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcoming_appointments_list as $apt): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($apt['date'] . ' ' . $apt['time'])); ?></td>
                                        <td><?php echo htmlspecialchars($apt['client_name']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($apt['purpose'], 0, 30)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">Aucun rendez-vous planifié</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Cases -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-briefcase"></i> Dossiers Récents</span>
                <a href="<?php echo APP_URL; ?>/pages/cases.php" class="btn btn-sm btn-primary">
                    Voir Tout
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_cases)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>N° Dossier</th>
                                    <th>Client</th>
                                    <th>Type</th>
                                    <th>Statut</th>
                                    <th>Date d'ouverture</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_cases as $case): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($case['case_number']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($case['client_name']); ?></td>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars(ucfirst($case['type'])); ?></span></td>
                                        <td>
                                            <?php
                                            $status_class = $case['status'] == 'clos' ? 'bg-secondary' : 
                                                          ($case['status'] == 'en_cours' ? 'bg-success' : 'bg-warning');
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars(ucfirst($case['status'])); ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($case['date_opened'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">Aucun dossier enregistré</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Chart.js script for revenue
$page_scripts = "
<script>
    // Revenue Chart
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
                datasets: [{
                    label: 'Revenus (MAD)',
                    data: [12000, 19000, 15000, " . ($monthly_revenue ?? 0) . "],
                    borderColor: '#D4AF37',
                    backgroundColor: 'rgba(212, 175, 55, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            color: '#fff'
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: { color: '#999' },
                        grid: { color: '#444' }
                    },
                    x: {
                        ticks: { color: '#999' },
                        grid: { color: '#444' }
                    }
                }
            }
        });
    }
</script>
";

require_once 'includes/footer.php';
?>

