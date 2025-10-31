<?php
/**
 * Cabinet360 SaaS - Admin Lawyers Management
 */

require_once '../config/admin_auth.php';

$success = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $lawyer_id = intval($_POST['lawyer_id'] ?? 0);
    
    try {
        switch ($action) {
            case 'deactivate':
                $stmt = $conn->prepare("UPDATE lawyers SET is_active = FALSE WHERE id = ?");
                $stmt->execute([$lawyer_id]);
                $success = "Lawyer account deactivated successfully.";
                break;
                
            case 'activate':
                $stmt = $conn->prepare("UPDATE lawyers SET is_active = TRUE WHERE id = ?");
                $stmt->execute([$lawyer_id]);
                $success = "Lawyer account activated successfully.";
                break;
                
            case 'suspend':
                $stmt = $conn->prepare("UPDATE lawyers SET subscription_status = 'suspended' WHERE id = ?");
                $stmt->execute([$lawyer_id]);
                $success = "Subscription suspended successfully.";
                break;
                
            case 'unsuspend':
                $stmt = $conn->prepare("UPDATE lawyers SET subscription_status = 'active' WHERE id = ?");
                $stmt->execute([$lawyer_id]);
                $success = "Subscription activated successfully.";
                break;
                
            case 'delete':
                // Only super admin can delete
                if ($is_super_admin) {
                    $stmt = $conn->prepare("DELETE FROM lawyers WHERE id = ?");
                    $stmt->execute([$lawyer_id]);
                    $success = "Lawyer account deleted permanently.";
                } else {
                    $error = "Only super admin can delete accounts.";
                }
                break;
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch all lawyers
try {
    $stmt = $conn->query("SELECT l.*, 
        (SELECT COUNT(*) FROM clients WHERE lawyer_id = l.id) as total_clients,
        (SELECT COUNT(*) FROM cases WHERE lawyer_id = l.id) as total_cases
        FROM lawyers l ORDER BY l.created_at DESC");
    $lawyers = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lawyers - Admin Panel</title>
    
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
            vertical-align: middle;
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
        .action-buttons .btn {
            padding: 4px 8px;
            font-size: 12px;
            margin: 2px;
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
        <h1 class="mb-4"><i class="fas fa-users"></i> Manage Lawyers</h1>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> All Registered Lawyers (<?php echo count($lawyers ?? []); ?>)
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cabinet</th>
                                <th>Lawyer</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Plan</th>
                                <th>Status</th>
                                <th>Clients</th>
                                <th>Cases</th>
                                <th>Last Login</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($lawyers)): ?>
                                <?php foreach ($lawyers as $lawyer): ?>
                                    <tr>
                                        <td><strong>#<?php echo $lawyer['id']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($lawyer['cabinet_name']); ?></td>
                                        <td><?php echo htmlspecialchars($lawyer['lawyer_name']); ?></td>
                                        <td><small><?php echo htmlspecialchars($lawyer['email']); ?></small></td>
                                        <td><small><?php echo htmlspecialchars($lawyer['phone'] ?? 'N/A'); ?></small></td>
                                        <td>
                                            <?php
                                            $plan_badges = [
                                                'free' => 'bg-secondary',
                                                'pro' => 'bg-warning',
                                                'premium' => 'bg-danger'
                                            ];
                                            ?>
                                            <span class="badge <?php echo $plan_badges[$lawyer['subscription_plan']]; ?>">
                                                <?php echo strtoupper($lawyer['subscription_plan']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($lawyer['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                            <br>
                                            <?php
                                            $status_badges = [
                                                'active' => 'bg-success',
                                                'suspended' => 'bg-warning',
                                                'cancelled' => 'bg-danger'
                                            ];
                                            ?>
                                            <span class="badge <?php echo $status_badges[$lawyer['subscription_status']]; ?>">
                                                <?php echo ucfirst($lawyer['subscription_status']); ?>
                                            </span>
                                        </td>
                                        <td><span class="badge bg-info"><?php echo $lawyer['total_clients']; ?></span></td>
                                        <td><span class="badge bg-primary"><?php echo $lawyer['total_cases']; ?></span></td>
                                        <td><small><?php echo $lawyer['last_login'] ? date('d/m/Y H:i', strtotime($lawyer['last_login'])) : 'Never'; ?></small></td>
                                        <td><small><?php echo date('d/m/Y', strtotime($lawyer['created_at'])); ?></small></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="lawyer_detail.php?id=<?php echo $lawyer['id']; ?>" class="btn btn-sm btn-info" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($lawyer['is_active']): ?>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Deactivate this lawyer account?');">
                                                        <input type="hidden" name="action" value="deactivate">
                                                        <input type="hidden" name="lawyer_id" value="<?php echo $lawyer['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-warning" title="Deactivate">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="activate">
                                                        <input type="hidden" name="lawyer_id" value="<?php echo $lawyer['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-success" title="Activate">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <?php if ($lawyer['subscription_status'] === 'active'): ?>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Suspend subscription?');">
                                                        <input type="hidden" name="action" value="suspend">
                                                        <input type="hidden" name="lawyer_id" value="<?php echo $lawyer['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-warning" title="Suspend">
                                                            <i class="fas fa-pause"></i>
                                                        </button>
                                                    </form>
                                                <?php elseif ($lawyer['subscription_status'] === 'suspended'): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="unsuspend">
                                                        <input type="hidden" name="lawyer_id" value="<?php echo $lawyer['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-success" title="Resume">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <?php if ($is_super_admin): ?>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('PERMANENTLY DELETE this lawyer and all their data? This cannot be undone!');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="lawyer_id" value="<?php echo $lawyer['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="12" class="text-center text-muted">No lawyers found</td>
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



