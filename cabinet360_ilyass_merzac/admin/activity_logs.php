<?php
/**
 * Cabinet360 SaaS - Admin Activity Logs
 */

require_once '../config/admin_auth.php';

// Fetch activity logs
try {
    $stmt = $conn->query("SELECT al.*, l.lawyer_name, l.cabinet_name 
                          FROM activity_logs al 
                          LEFT JOIN lawyers l ON al.lawyer_id = l.id 
                          ORDER BY al.created_at DESC 
                          LIMIT 100");
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Admin Panel</title>
    
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
        .log-action {
            font-weight: bold;
            color: #FF6B6B;
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

    <div class="container-fluid">
        <h1 class="mb-4"><i class="fas fa-history"></i> Activity Logs</h1>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> Recent Activity (Last 100)
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Lawyer</th>
                                <th>Cabinet</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                                <th>Date/Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($logs)): ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><small>#<?php echo $log['id']; ?></small></td>
                                        <td><?php echo htmlspecialchars($log['lawyer_name'] ?? 'N/A'); ?></td>
                                        <td><small><?php echo htmlspecialchars($log['cabinet_name'] ?? 'N/A'); ?></small></td>
                                        <td><span class="log-action"><?php echo htmlspecialchars($log['action']); ?></span></td>
                                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                                        <td><small><?php echo htmlspecialchars($log['ip_address']); ?></small></td>
                                        <td><small><?php echo htmlspecialchars(substr($log['user_agent'] ?? '', 0, 50)); ?></small></td>
                                        <td><small><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No activity logs found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



