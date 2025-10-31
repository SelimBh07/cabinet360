<?php
/**
 * Cabinet360 SaaS - Admin Dashboard
 */

require_once '../config/admin_auth.php';

// Fetch statistics
try {
    // Total Lawyers
    $stmt = $conn->query("SELECT COUNT(*) as total FROM lawyers WHERE is_active = TRUE");
    $total_lawyers = $stmt->fetch()['total'];
    
    // Active Subscriptions
    $stmt = $conn->query("SELECT COUNT(*) as total FROM lawyers WHERE subscription_status = 'active' AND is_active = TRUE");
    $active_subscriptions = $stmt->fetch()['total'];
    
    // Total Revenue (Monthly)
    $stmt = $conn->query("SELECT 
        COUNT(CASE WHEN subscription_plan = 'pro' AND subscription_status = 'active' THEN 1 END) * 299 +
        COUNT(CASE WHEN subscription_plan = 'premium' AND subscription_status = 'active' THEN 1 END) * 599 
        as monthly_revenue FROM lawyers WHERE is_active = TRUE");
    $monthly_revenue = $stmt->fetch()['monthly_revenue'];
    
    // New Signups (This Month)
    $stmt = $conn->query("SELECT COUNT(*) as total FROM lawyers WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
    $new_signups = $stmt->fetch()['total'];
    
    // Recent Lawyers
    $stmt = $conn->query("SELECT id, cabinet_name, lawyer_name, email, subscription_plan, subscription_status, last_login, created_at FROM lawyers ORDER BY created_at DESC LIMIT 10");
    $recent_lawyers = $stmt->fetchAll();
    
    // Subscription Distribution
    $stmt = $conn->query("SELECT subscription_plan, COUNT(*) as count FROM lawyers WHERE is_active = TRUE GROUP BY subscription_plan");
    $subscription_dist = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
} catch (PDOException $e) {
    $error = "Erreur: " . $e->getMessage();
}

$page_title = 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo APP_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #0f1419;
            color: #e4e6eb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .admin-navbar {
            background: linear-gradient(135deg, #141E30, #243B55);
            border-bottom: 2px solid #FF6B6B;
            padding: 15px 0;
            margin-bottom: 30px;
        }
        .admin-navbar .brand {
            color: #FF6B6B;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }
        .admin-navbar .nav-link {
            color: #e4e6eb;
            margin: 0 10px;
            transition: color 0.3s;
        }
        .admin-navbar .nav-link:hover {
            color: #FF6B6B;
        }
        .stat-card {
            background: linear-gradient(135deg, #1a2332, #2d3e50);
            border: 1px solid #FF6B6B;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255, 107, 107, 0.3);
        }
        .stat-icon {
            font-size: 40px;
            color: #FF6B6B;
            margin-bottom: 10px;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #fff;
        }
        .stat-label {
            color: #999;
            font-size: 14px;
        }
        .card {
            background: #1a2332;
            border: 1px solid #2d3e50;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .card-header {
            background: #243B55;
            border-bottom: 1px solid #FF6B6B;
            color: #FF6B6B;
            font-weight: bold;
            padding: 15px 20px;
        }
        .table {
            color: #e4e6eb;
        }
        .table thead th {
            border-color: #2d3e50;
            background: #243B55;
            color: #FF6B6B;
        }
        .table tbody td {
            border-color: #2d3e50;
        }
        .badge {
            padding: 5px 10px;
            font-size: 11px;
        }
        .btn-admin {
            background: #FF6B6B;
            color: #fff;
            border: none;
        }
        .btn-admin:hover {
            background: #ee5a5a;
            color: #fff;
        }
        .btn-danger-admin {
            background: #dc3545;
            color: #fff;
            border: none;
        }
        .btn-success-admin {
            background: #28a745;
            color: #fff;
            border: none;
        }
    </style>
</head>
<body>
    <!-- Admin Navbar -->
    <nav class="admin-navbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="index.php" class="brand">
                    <i class="fas fa-shield-alt"></i> ADMIN PANEL
                </a>
                <div class="d-flex align-items-center">
                    <a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="lawyers.php" class="nav-link"><i class="fas fa-users"></i> Lawyers</a>
                    <a href="subscriptions.php" class="nav-link"><i class="fas fa-credit-card"></i> Subscriptions</a>
                    <a href="activity_logs.php" class="nav-link"><i class="fas fa-history"></i> Activity</a>
                    <span class="nav-link"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($admin_name); ?></span>
                    <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="mb-4"><i class="fas fa-chart-line"></i> Admin Dashboard</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-number"><?php echo $total_lawyers ?? 0; ?></div>
                    <div class="stat-label">Total Lawyers</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-number"><?php echo $active_subscriptions ?? 0; ?></div>
                    <div class="stat-label">Active Subscriptions</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-number"><?php echo number_format($monthly_revenue ?? 0); ?> MAD</div>
                    <div class="stat-label">Monthly Revenue</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
                    <div class="stat-number"><?php echo $new_signups ?? 0; ?></div>
                    <div class="stat-label">New This Month</div>
                </div>
            </div>
        </div>

        <!-- Subscription Distribution -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-pie"></i> Subscription Distribution
                    </div>
                    <div class="card-body text-center">
                        <canvas id="subscriptionChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-info-circle"></i> Quick Stats
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><i class="fas fa-gift text-success"></i> Free Plans</td>
                                <td class="text-end"><strong><?php echo $subscription_dist['free'] ?? 0; ?></strong></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-star text-warning"></i> Pro Plans</td>
                                <td class="text-end"><strong><?php echo $subscription_dist['pro'] ?? 0; ?></strong></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-crown text-danger"></i> Premium Plans</td>
                                <td class="text-end"><strong><?php echo $subscription_dist['premium'] ?? 0; ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong>Total Active</strong></td>
                                <td class="text-end"><strong><?php echo $active_subscriptions ?? 0; ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Lawyers -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-users"></i> Recent Lawyers</span>
                <a href="lawyers.php" class="btn btn-sm btn-admin">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cabinet Name</th>
                                <th>Lawyer Name</th>
                                <th>Email</th>
                                <th>Plan</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_lawyers)): ?>
                                <?php foreach ($recent_lawyers as $lawyer): ?>
                                    <tr>
                                        <td><?php echo $lawyer['id']; ?></td>
                                        <td><?php echo htmlspecialchars($lawyer['cabinet_name']); ?></td>
                                        <td><?php echo htmlspecialchars($lawyer['lawyer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($lawyer['email']); ?></td>
                                        <td>
                                            <?php
                                            $plan_badges = [
                                                'free' => 'bg-secondary',
                                                'pro' => 'bg-warning',
                                                'premium' => 'bg-danger'
                                            ];
                                            $badge_class = $plan_badges[$lawyer['subscription_plan']] ?? 'bg-secondary';
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo strtoupper($lawyer['subscription_plan']); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $status_badges = [
                                                'active' => 'bg-success',
                                                'suspended' => 'bg-warning',
                                                'cancelled' => 'bg-danger'
                                            ];
                                            $status_badge = $status_badges[$lawyer['subscription_status']] ?? 'bg-secondary';
                                            ?>
                                            <span class="badge <?php echo $status_badge; ?>"><?php echo ucfirst($lawyer['subscription_status']); ?></span>
                                        </td>
                                        <td><?php echo $lawyer['last_login'] ? date('d/m/Y H:i', strtotime($lawyer['last_login'])) : 'Never'; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($lawyer['created_at'])); ?></td>
                                        <td>
                                            <a href="lawyer_detail.php?id=<?php echo $lawyer['id']; ?>" class="btn btn-sm btn-admin">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No lawyers registered yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Subscription Distribution Chart
        const ctx = document.getElementById('subscriptionChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Free', 'Pro', 'Premium'],
                    datasets: [{
                        data: [
                            <?php echo $subscription_dist['free'] ?? 0; ?>,
                            <?php echo $subscription_dist['pro'] ?? 0; ?>,
                            <?php echo $subscription_dist['premium'] ?? 0; ?>
                        ],
                        backgroundColor: ['#6c757d', '#ffc107', '#dc3545'],
                        borderWidth: 2,
                        borderColor: '#1a2332'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#e4e6eb'
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>






