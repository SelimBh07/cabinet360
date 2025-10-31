<?php
/**
 * Cabinet360 SaaS - Admin Login Page
 */

session_start();
require_once '../config/config.php';

// Redirect if already logged in as admin
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $conn->prepare("SELECT id, username, password, full_name, email, is_super_admin FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Set session variables
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['is_super_admin'] = $admin['is_super_admin'];
                $_SESSION['ADMIN_LAST_ACTIVITY'] = time();
                
                header("Location: index.php");
                exit();
            } else {
                $error = 'Nom d\'utilisateur ou mot de passe incorrect.';
            }
        } catch (PDOException $e) {
            $error = 'Erreur de connexion. Veuillez réessayer.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo APP_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #141E30 0%, #243B55 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .admin-login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        .admin-login-card {
            background: rgba(20, 30, 48, 0.95);
            border: 2px solid #FF6B6B;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(255, 107, 107, 0.3);
            padding: 40px;
        }
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-icon {
            font-size: 60px;
            color: #FF6B6B;
            margin-bottom: 15px;
        }
        .app-title {
            color: #FF6B6B;
            font-size: 28px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 2px;
        }
        .app-subtitle {
            color: #999;
            font-size: 13px;
            margin-top: 5px;
        }
        .admin-badge {
            display: inline-block;
            background: #FF6B6B;
            color: #fff;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 10px;
        }
        .form-label {
            color: #FF6B6B;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .form-control {
            background: rgba(36, 59, 85, 0.5);
            border: 1px solid #555;
            color: #fff;
            padding: 12px 15px;
            border-radius: 8px;
        }
        .form-control:focus {
            background: rgba(36, 59, 85, 0.7);
            border-color: #FF6B6B;
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 107, 0.25);
        }
        .form-control::placeholder {
            color: #888;
        }
        .btn-admin-login {
            background: #FF6B6B;
            border: none;
            color: #fff;
            padding: 12px;
            font-weight: bold;
            border-radius: 8px;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn-admin-login:hover {
            background: #ee5a5a;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 107, 107, 0.5);
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #999;
            text-decoration: none;
            font-size: 13px;
        }
        .back-link a:hover {
            color: #FF6B6B;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-card">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1 class="app-title">ADMIN</h1>
                <p class="app-subtitle">Cabinet360 SaaS</p>
                <span class="admin-badge">
                    <i class="fas fa-lock"></i> RESTRICTED ACCESS
                </span>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Admin username" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Admin password" required>
                </div>

                <button type="submit" class="btn btn-admin-login">
                    <i class="fas fa-sign-in-alt"></i> Admin Login
                </button>
            </form>

            <div class="back-link">
                <a href="../login_lawyer.php">
                    <i class="fas fa-arrow-left"></i> Back to Lawyer Login
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>






