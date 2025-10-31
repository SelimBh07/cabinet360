<?php
/**
 * Cabinet360 SaaS - Admin Subscriptions Management
 */

require_once '../config/admin_auth.php';

// Fetch subscription plans
try {
    $stmt = $conn->query("SELECT * FROM subscription_plans ORDER BY price ASC");
    $plans = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscriptions - Admin Panel</title>
    
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
        .plan-card {
            background: #1a2332;
            border: 2px solid #2d3e50;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            transition: transform 0.3s;
        }
        .plan-card:hover {
            transform: translateY(-10px);
            border-color: #FF6B6B;
        }
        .plan-name {
            font-size: 24px;
            font-weight: bold;
            color: #FF6B6B;
            margin-bottom: 15px;
        }
        .plan-price {
            font-size: 36px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 10px;
        }
        .plan-features {
            text-align: left;
            margin-top: 20px;
        }
        .plan-features li {
            margin-bottom: 10px;
            color: #999;
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
        <h1 class="mb-4"><i class="fas fa-credit-card"></i> Subscription Plans</h1>

        <div class="row">
            <?php if (!empty($plans)): ?>
                <?php foreach ($plans as $plan): ?>
                    <div class="col-md-4 mb-4">
                        <div class="plan-card">
                            <div class="plan-name">
                                <i class="fas fa-star"></i> <?php echo strtoupper($plan['plan_name']); ?>
                            </div>
                            <div class="plan-price">
                                <?php echo number_format($plan['price']); ?> MAD
                            </div>
                            <div style="color:#999; font-size:14px;">
                                / <?php echo $plan['billing_cycle']; ?>
                            </div>
                            
                            <div class="plan-features">
                                <ul class="list-unstyled">
                                    <?php if ($plan['max_clients']): ?>
                                        <li><i class="fas fa-users"></i> Up to <?php echo $plan['max_clients']; ?> clients</li>
                                    <?php else: ?>
                                        <li><i class="fas fa-users"></i> Unlimited clients</li>
                                    <?php endif; ?>
                                    
                                    <?php if ($plan['max_cases']): ?>
                                        <li><i class="fas fa-briefcase"></i> Up to <?php echo $plan['max_cases']; ?> cases</li>
                                    <?php else: ?>
                                        <li><i class="fas fa-briefcase"></i> Unlimited cases</li>
                                    <?php endif; ?>
                                    
                                    <li><i class="fas fa-database"></i> <?php echo $plan['max_storage_mb']; ?> MB storage</li>
                                    
                                    <?php 
                                    $features = json_decode($plan['features'], true);
                                    if ($features):
                                        foreach ($features as $key => $value):
                                            if ($value === true || $value === 'true'):
                                    ?>
                                                <li><i class="fas fa-check"></i> <?php echo ucfirst(str_replace('_', ' ', $key)); ?></li>
                                    <?php 
                                            elseif (is_string($value)):
                                    ?>
                                                <li><i class="fas fa-info-circle"></i> <?php echo ucfirst(str_replace('_', ' ', $key)); ?>: <?php echo $value; ?></li>
                                    <?php
                                            endif;
                                        endforeach;
                                    endif;
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-warning">No subscription plans found</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>






