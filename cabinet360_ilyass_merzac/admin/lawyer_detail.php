<?php
/**
 * Cabinet360 SaaS - Admin Lawyer Detail
 */

require_once '../config/admin_auth.php';

$lawyer_id = intval($_GET['id'] ?? 0);

if (!$lawyer_id) {
    header("Location: lawyers.php");
    exit();
}

// Fetch lawyer details
try {
    $stmt = $conn->prepare("SELECT * FROM lawyers WHERE id = ?");
    $stmt->execute([$lawyer_id]);
    $lawyer = $stmt->fetch();
    
    if (!$lawyer) {
        header("Location: lawyers.php");
        exit();
    }
    
    // Get statistics
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM clients WHERE lawyer_id = ?");
    $stmt->execute([$lawyer_id]);
    $total_clients = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM cases WHERE lawyer_id = ?");
    $stmt->execute([$lawyer_id]);
    $total_cases = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE lawyer_id = ?");
    $stmt->execute([$lawyer_id]);
    $total_appointments = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE lawyer_id = ? AND status = 'payé'");
    $stmt->execute([$lawyer_id]);
    $total_revenue = $stmt->fetch()['total'];
    
    // Recent activity
    $stmt = $conn->prepare("SELECT * FROM activity_logs WHERE lawyer_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$lawyer_id]);
    $recent_activity = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lawyer Detail - Admin Panel</title>
    
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
        .info-label {
            color: #999;
            font-size: 13px;
            margin-bottom: 5px;
        }
        .info-value {
            color: #e4e6eb;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .stat-box {
            background: #243B55;
            border: 1px solid #FF6B6B;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .stat-box .number {
            font-size: 32px;
            color: #FF6B6B;
            font-weight: bold;
        }
        .stat-box .label {
            color: #999;
            font-size: 14px;
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
        <div class="mb-4">
            <a href="lawyers.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Lawyers
            </a>
        </div>

        <h1 class="mb-4"><i class="fas fa-user-tie"></i> Lawyer Details</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-info-circle"></i> Account Information
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">Cabinet Name</div>
                                <div class="info-value"><?php echo htmlspecialchars($lawyer['cabinet_name']); ?></div>
                                
                                <div class="info-label">Lawyer Name</div>
                                <div class="info-value"><?php echo htmlspecialchars($lawyer['lawyer_name']); ?></div>
                                
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo htmlspecialchars($lawyer['email']); ?></div>
                                
                                <div class="info-label">Phone</div>
                                <div class="info-value"><?php echo htmlspecialchars($lawyer['phone'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Subscription Plan</div>
                                <div class="info-value">
                                    <span class="badge bg-warning"><?php echo strtoupper($lawyer['subscription_plan']); ?></span>
                                </div>
                                
                                <div class="info-label">Subscription Status</div>
                                <div class="info-value">
                                    <span class="badge bg-success"><?php echo ucfirst($lawyer['subscription_status']); ?></span>
                                </div>
                                
                                <div class="info-label">Account Status</div>
                                <div class="info-value">
                                    <?php if ($lawyer['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="info-label">Last Login</div>
                                <div class="info-value">
                                    <?php echo $lawyer['last_login'] ? date('d/m/Y H:i', strtotime($lawyer['last_login'])) : 'Never'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-history"></i> Recent Activity
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_activity)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Action</th>
                                            <th>Details</th>
                                            <th>IP Address</th>
                                            <th>Date/Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_activity as $activity): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($activity['action']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($activity['details']); ?></td>
                                                <td><small><?php echo htmlspecialchars($activity['ip_address']); ?></small></td>
                                                <td><small><?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?></small></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">No activity logged yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Statistics -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-bar"></i> Usage Statistics
                    </div>
                    <div class="card-body">
                        <div class="stat-box">
                            <div class="number"><?php echo $total_clients; ?></div>
                            <div class="label">Total Clients</div>
                        </div>
                        
                        <div class="stat-box">
                            <div class="number"><?php echo $total_cases; ?></div>
                            <div class="label">Total Cases</div>
                        </div>
                        
                        <div class="stat-box">
                            <div class="number"><?php echo $total_appointments; ?></div>
                            <div class="label">Total Appointments</div>
                        </div>
                        
                        <div class="stat-box">
                            <div class="number"><?php echo number_format($total_revenue, 2); ?> MAD</div>
                            <div class="label">Total Revenue</div>
                        </div>
                    </div>
                </div>

                <!-- Account Dates -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-calendar"></i> Important Dates
                    </div>
                    <div class="card-body">
                        <div class="info-label">Account Created</div>
                        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($lawyer['created_at'])); ?></div>
                        
                        <div class="info-label">Last Updated</div>
                        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($lawyer['updated_at'])); ?></div>
                        
                        <?php if ($lawyer['subscription_start']): ?>
                            <div class="info-label">Subscription Started</div>
                            <div class="info-value"><?php echo date('d/m/Y', strtotime($lawyer['subscription_start'])); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($lawyer['subscription_end']): ?>
                            <div class="info-label">Subscription Ends</div>
                            <div class="info-value"><?php echo date('d/m/Y', strtotime($lawyer['subscription_end'])); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



