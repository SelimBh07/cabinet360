<?php
/**
 * Cabinet360 SaaS - Lawyer Signup Page
 */

require_once 'config/config.php';

// Redirect if already logged in
if (isset($_SESSION['lawyer_id'])) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cabinet_name = sanitize_input($_POST['cabinet_name'] ?? '');
    $lawyer_name = sanitize_input($_POST['lawyer_name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = sanitize_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($cabinet_name) || empty($lawyer_name) || empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        try {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM lawyers WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Cette adresse email est déjà utilisée.';
            } else {
                // Create new lawyer account
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO lawyers (cabinet_name, lawyer_name, email, password, phone, subscription_plan, subscription_status, subscription_start) VALUES (?, ?, ?, ?, ?, 'free', 'active', CURDATE())");
                
                if ($stmt->execute([$cabinet_name, $lawyer_name, $email, $hashed_password, $phone])) {
                    // Log activity
                    $lawyer_id = $conn->lastInsertId();
                    $stmt = $conn->prepare("INSERT INTO activity_logs (lawyer_id, action, details, ip_address) VALUES (?, 'signup', 'New lawyer account created', ?)");
                    $stmt->execute([$lawyer_id, $_SERVER['REMOTE_ADDR']]);
                    
                    $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
                } else {
                    $error = 'Erreur lors de la création du compte. Veuillez réessayer.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Erreur de connexion à la base de données.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Inscription - <?php echo APP_NAME; ?> SaaS</title>
    
    <!-- PWA Meta Tags -->
    <meta name="description" content="Créez votre compte Cabinet360 - Système de gestion pour cabinet d'avocat professionnel">
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
            padding: 20px 0;
        }
        .signup-container {
            max-width: 600px;
            width: 100%;
            padding: 20px;
        }
        .signup-card {
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
            font-size: 50px;
            color: #D4AF37;
            margin-bottom: 10px;
        }
        .app-title {
            color: #D4AF37;
            font-size: 28px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 2px;
        }
        .app-subtitle {
            color: #999;
            font-size: 14px;
            margin-top: 5px;
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
        .btn-signup {
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
        .btn-signup:hover {
            background: #c49a2c;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.4);
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #999;
        }
        .login-link a {
            color: #D4AF37;
            text-decoration: none;
            font-weight: bold;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .password-requirements {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        @media (max-width: 576px) {
            .signup-card {
                padding: 25px 20px;
            }
            .logo-icon {
                font-size: 40px;
            }
            .app-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-card">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <h1 class="app-title">Cabinet360</h1>
                <p class="app-subtitle">Créez votre compte SaaS</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <br><a href="login.php" class="text-white"><strong>Cliquez ici pour vous connecter</strong></a>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="signupForm">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="cabinet_name" class="form-label">
                            <i class="fas fa-building"></i> Nom du Cabinet <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="cabinet_name" name="cabinet_name" 
                               placeholder="Ex: Cabinet d'Avocat Bennis" required 
                               value="<?php echo htmlspecialchars($_POST['cabinet_name'] ?? ''); ?>">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="lawyer_name" class="form-label">
                            <i class="fas fa-user-tie"></i> Nom Complet de l'Avocat <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="lawyer_name" name="lawyer_name" 
                               placeholder="Ex: Me. Hassan Bennis" required
                               value="<?php echo htmlspecialchars($_POST['lawyer_name'] ?? ''); ?>">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="votre.email@example.com" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone"></i> Téléphone
                        </label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               placeholder="+212 6 00 00 00 00"
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Mot de passe <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Minimum 8 caractères" required minlength="8">
                        <div class="password-requirements">
                            <i class="fas fa-info-circle"></i> Minimum 8 caractères recommandés
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock"></i> Confirmer le mot de passe <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Retapez votre mot de passe" required minlength="8">
                    </div>
                </div>

                <button type="submit" class="btn btn-signup">
                    <i class="fas fa-user-plus"></i> Créer mon compte
                </button>
            </form>

            <div class="login-link">
                Vous avez déjà un compte ? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Se connecter</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password validation
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas.');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 8 caractères.');
                return false;
            }
        });
    </script>
</body>
</html>






