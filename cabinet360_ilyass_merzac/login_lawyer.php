<?php
/**
 * Cabinet360 SaaS - Lawyer Login Page
 */

require_once 'config/config.php';

// Redirect if already logged in
if (isset($_SESSION['lawyer_id'])) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $conn->prepare("SELECT id, cabinet_name, lawyer_name, email, password, subscription_plan, subscription_status, is_active FROM lawyers WHERE email = ?");
            $stmt->execute([$email]);
            $lawyer = $stmt->fetch();
            
            if ($lawyer && password_verify($password, $lawyer['password'])) {
                // Check if account is active
                if (!$lawyer['is_active']) {
                    $error = 'Votre compte a été désactivé. Veuillez contacter l\'administrateur.';
                } elseif ($lawyer['subscription_status'] !== 'active') {
                    $error = 'Votre abonnement est ' . $lawyer['subscription_status'] . '. Veuillez renouveler votre abonnement.';
                } else {
                    // Set session variables
                    $_SESSION['lawyer_id'] = $lawyer['id'];
                    $_SESSION['cabinet_name'] = $lawyer['cabinet_name'];
                    $_SESSION['lawyer_name'] = $lawyer['lawyer_name'];
                    $_SESSION['lawyer_email'] = $lawyer['email'];
                    $_SESSION['subscription_plan'] = $lawyer['subscription_plan'];
                    $_SESSION['LAST_ACTIVITY'] = time();
                    
                    // Update last login
                    $stmt = $conn->prepare("UPDATE lawyers SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$lawyer['id']]);
                    
                    // Log activity
                    $stmt = $conn->prepare("INSERT INTO activity_logs (lawyer_id, action, details, ip_address) VALUES (?, 'login', 'Lawyer logged in', ?)");
                    $stmt->execute([$lawyer['id'], $_SERVER['REMOTE_ADDR']]);
                    
                    // Remember me (30 days)
                    if ($remember_me) {
                        setcookie('remember_lawyer', $lawyer['id'], time() + (30 * 24 * 60 * 60), '/');
                    }
                    
                    redirect('index.php');
                }
            } else {
                $error = 'Email ou mot de passe incorrect.';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Connexion - <?php echo APP_NAME; ?> SaaS</title>
    
    <!-- PWA Meta Tags -->
    <meta name="description" content="Cabinet360 - Système de gestion pour cabinet d'avocat professionnel">
    <meta name="theme-color" content="#007bff">
    <link rel="manifest" href="<?php echo APP_URL; ?>/manifest.json">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo APP_URL; ?>/assets/icons/icon-192x192.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        .login-card {
            background: #1a1a1a;
            border: 1px solid #D4AF37;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(212, 175, 55, 0.2);
            padding: 40px;
        }
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-icon {
            font-size: 60px;
            color: #D4AF37;
            margin-bottom: 15px;
        }
        .app-title {
            color: #D4AF37;
            font-size: 32px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 2px;
        }
        .app-subtitle {
            color: #999;
            font-size: 14px;
            margin-top: 5px;
        }
        .badge-saas {
            display: inline-block;
            background: linear-gradient(90deg, #D4AF37, #FFD700);
            color: #1a1a1a;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 10px;
        }
        .form-label {
            color: #D4AF37;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .form-control {
            background: #2d2d2d;
            border: 1px solid #444;
            color: #fff;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 16px;
        }
        .form-control:focus {
            background: #2d2d2d;
            border-color: #D4AF37;
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
        }
        .form-control::placeholder {
            color: #666;
        }
        .form-check-input {
            background-color: #2d2d2d;
            border-color: #444;
        }
        .form-check-input:checked {
            background-color: #D4AF37;
            border-color: #D4AF37;
        }
        .form-check-label {
            color: #999;
        }
        .btn-login {
            background: #D4AF37;
            border: none;
            color: #1a1a1a;
            padding: 12px;
            font-weight: bold;
            border-radius: 8px;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #c49a2c;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.4);
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
        .signup-link {
            text-align: center;
            margin-top: 20px;
            color: #999;
        }
        .signup-link a {
            color: #D4AF37;
            text-decoration: none;
            font-weight: bold;
        }
        .signup-link a:hover {
            text-decoration: underline;
        }
        .forgot-password {
            text-align: right;
            margin-top: 10px;
        }
        .forgot-password a {
            color: #999;
            font-size: 13px;
            text-decoration: none;
        }
        .forgot-password a:hover {
            color: #D4AF37;
        }
        @media (max-width: 576px) {
            .login-card {
                padding: 25px 20px;
            }
            .logo-icon {
                font-size: 48px;
            }
            .app-title {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <h1 class="app-title">Cabinet360</h1>
                <p class="app-subtitle">Gestion Cabinet d'Avocat</p>
                <span class="badge-saas">
                    <i class="fas fa-cloud"></i> SaaS Platform
                </span>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="votre.email@example.com" required autofocus
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Mot de passe
                    </label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Entrez votre mot de passe" required>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                        <label class="form-check-label" for="remember_me">
                            Se souvenir de moi
                        </label>
                    </div>
                    <div class="forgot-password">
                        <a href="forgot_password.php">
                            <i class="fas fa-key"></i> Mot de passe oublié ?
                        </a>
                    </div>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </form>

            <div class="signup-link">
                Pas encore de compte ? <a href="signup.php"><i class="fas fa-user-plus"></i> Créer un compte</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



