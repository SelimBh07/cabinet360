<?php
/**
 * Cabinet360 - Reports & Analytics
 */

require_once __DIR__ . '/../config/auth.php';

$page_title = 'Rapports & Analyses';

// Date range filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

try {
    // Revenue statistics
    $stmt = $conn->prepare("SELECT 
        COUNT(*) as total_payments,
        SUM(CASE WHEN status = 'payé' THEN amount ELSE 0 END) as total_paid,
        SUM(CASE WHEN status = 'impayé' THEN amount ELSE 0 END) as total_unpaid,
        SUM(CASE WHEN status = 'partiel' THEN amount ELSE 0 END) as total_partial
        FROM payments 
        WHERE date BETWEEN ? AND ?");
    $stmt->execute([$start_date, $end_date]);
    $revenue_stats = $stmt->fetch();
    
    // Monthly revenue chart data
    $stmt = $conn->query("SELECT 
        DATE_FORMAT(date, '%Y-%m') as month,
        SUM(CASE WHEN status = 'payé' THEN amount ELSE 0 END) as revenue
        FROM payments 
        WHERE date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month 
        ORDER BY month");
    $monthly_revenue = $stmt->fetchAll();
    
    // Cases by type
    $stmt = $conn->query("SELECT type, COUNT(*) as count FROM cases GROUP BY type");
    $cases_by_type = $stmt->fetchAll();
    
    // Cases by status
    $stmt = $conn->query("SELECT status, COUNT(*) as count FROM cases GROUP BY status");
    $cases_by_status = $stmt->fetchAll();
    
    // Top clients by revenue
    $stmt = $conn->prepare("SELECT 
        c.id, c.full_name,
        COUNT(DISTINCT p.id) as payment_count,
        SUM(CASE WHEN p.status = 'payé' THEN p.amount ELSE 0 END) as total_paid
        FROM clients c
        LEFT JOIN payments p ON c.id = p.client_id
        WHERE p.date BETWEEN ? AND ?
        GROUP BY c.id
        ORDER BY total_paid DESC
        LIMIT 10");
    $stmt->execute([$start_date, $end_date]);
    $top_clients = $stmt->fetchAll();
    
    // Recent activity
    $stmt = $conn->query("SELECT 
        'client' as type, full_name as name, created_at as date FROM clients
        UNION ALL
        SELECT 'case' as type, case_number as name, created_at as date FROM cases
        UNION ALL
        SELECT 'appointment' as type, purpose as name, CONCAT(date, ' ', time) as date FROM appointments
        ORDER BY date DESC LIMIT 20");
    $recent_activity = $stmt->fetchAll();
    
    // Payment methods distribution
    $stmt = $conn->prepare("SELECT 
        method, 
        COUNT(*) as count,
        SUM(amount) as total
        FROM payments 
        WHERE date BETWEEN ? AND ? AND status = 'payé'
        GROUP BY method");
    $stmt->execute([$start_date, $end_date]);
    $payment_methods = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-chart-line"></i> Rapports & Analyses
    </h1>
    <p class="page-subtitle">Statistiques et rapports détaillés</p>
</div>

<!-- Date Range Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Date de début</label>
                <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Date de fin</label>
                <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Filtrer
                </button>
                <button type="button" class="btn btn-success w-100 mt-2" onclick="exportReport()">
                    <i class="fas fa-file-excel"></i> Exporter Excel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Revenue Overview -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-number text-success"><?php echo number_format($revenue_stats['total_paid'] ?? 0, 2); ?> MAD</div>
            <div class="stat-label">Revenus Payés</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-number text-danger"><?php echo number_format($revenue_stats['total_unpaid'] ?? 0, 2); ?> MAD</div>
            <div class="stat-label">Impayés</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-number text-warning"><?php echo number_format($revenue_stats['total_partial'] ?? 0, 2); ?> MAD</div>
            <div class="stat-label">Paiements Partiels</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="stat-number"><?php echo $revenue_stats['total_payments'] ?? 0; ?></div>
            <div class="stat-label">Total Paiements</div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Revenue Chart -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line"></i> Évolution des Revenus (12 derniers mois)
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Cases by Type -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie"></i> Dossiers par Type
            </div>
            <div class="card-body">
                <canvas id="casesTypeChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Second Charts Row -->
<div class="row mb-4">
    <!-- Cases by Status -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-bar"></i> Dossiers par Statut
            </div>
            <div class="card-body">
                <canvas id="casesStatusChart" height="120"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Payment Methods -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie"></i> Méthodes de Paiement
            </div>
            <div class="card-body">
                <canvas id="paymentMethodsChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Clients Table -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-trophy"></i> Top 10 Clients (par revenus)
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Rang</th>
                                <th>Client</th>
                                <th>Nombre de Paiements</th>
                                <th>Total Payé</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($top_clients)): ?>
                                <?php foreach ($top_clients as $index => $client): ?>
                                    <tr>
                                        <td>
                                            <?php if ($index == 0): ?>
                                                <i class="fas fa-trophy text-gold"></i> #1
                                            <?php elseif ($index == 1): ?>
                                                <i class="fas fa-medal" style="color: silver;"></i> #2
                                            <?php elseif ($index == 2): ?>
                                                <i class="fas fa-medal" style="color: #cd7f32;"></i> #3
                                            <?php else: ?>
                                                #<?php echo $index + 1; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($client['full_name']); ?></strong>
                                        </td>
                                        <td><?php echo $client['payment_count']; ?></td>
                                        <td class="text-gold"><strong><?php echo number_format($client['total_paid'], 2); ?> MAD</strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Aucune donnée disponible</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Prepare chart data
$revenue_labels = array_map(function($item) {
    return date('M Y', strtotime($item['month'] . '-01'));
}, $monthly_revenue);
$revenue_data = array_map(function($item) {
    return $item['revenue'];
}, $monthly_revenue);

$cases_type_labels = array_map(function($item) {
    return ucfirst($item['type']);
}, $cases_by_type);
$cases_type_data = array_map(function($item) {
    return $item['count'];
}, $cases_by_type);

$cases_status_labels = array_map(function($item) {
    return ucfirst($item['status']);
}, $cases_by_status);
$cases_status_data = array_map(function($item) {
    return $item['count'];
}, $cases_by_status);

$payment_method_labels = array_map(function($item) {
    return ucfirst($item['method']);
}, $payment_methods);
$payment_method_data = array_map(function($item) {
    return $item['total'];
}, $payment_methods);

$page_scripts = "
<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
<script>
// Revenue Chart
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: " . json_encode($revenue_labels) . ",
        datasets: [{
            label: 'Revenus (MAD)',
            data: " . json_encode($revenue_data) . ",
            borderColor: '#D4AF37',
            backgroundColor: 'rgba(212, 175, 55, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { labels: { color: '#fff' } }
        },
        scales: {
            y: { ticks: { color: '#999' }, grid: { color: '#444' } },
            x: { ticks: { color: '#999' }, grid: { color: '#444' } }
        }
    }
});

// Cases by Type Chart
new Chart(document.getElementById('casesTypeChart'), {
    type: 'doughnut',
    data: {
        labels: " . json_encode($cases_type_labels) . ",
        datasets: [{
            data: " . json_encode($cases_type_data) . ",
            backgroundColor: ['#D4AF37', '#4CAF50', '#2196F3', '#FF9800', '#E91E63']
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { color: '#fff' } } }
    }
});

// Cases by Status Chart
new Chart(document.getElementById('casesStatusChart'), {
    type: 'bar',
    data: {
        labels: " . json_encode($cases_status_labels) . ",
        datasets: [{
            label: 'Nombre de Dossiers',
            data: " . json_encode($cases_status_data) . ",
            backgroundColor: '#D4AF37'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { color: '#fff' } } },
        scales: {
            y: { ticks: { color: '#999' }, grid: { color: '#444' } },
            x: { ticks: { color: '#999' }, grid: { color: '#444' } }
        }
    }
});

// Payment Methods Chart
new Chart(document.getElementById('paymentMethodsChart'), {
    type: 'pie',
    data: {
        labels: " . json_encode($payment_method_labels) . ",
        datasets: [{
            data: " . json_encode($payment_method_data) . ",
            backgroundColor: ['#D4AF37', '#4CAF50', '#2196F3', '#FF9800']
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { color: '#fff' } } }
    }
});

// Export Report Function
function exportReport() {
    alert('Fonctionnalité d\\'export en développement. Les données seront exportées en Excel.');
    // TODO: Implement Excel export
}
</script>
";

require_once __DIR__ . '/../includes/footer.php';
?>
















