<?php
/**
 * Cabinet360 SaaS - Reset Password
 */

require_once 'config/config.php';

$error = '';
$success = '';
$valid_token = false;
$lawyer_id = null;

// Check token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        $stmt = $conn->prepare("SELECT pr.lawyer_id, l.lawyer_name FROM password_resets pr 
                               JOIN lawyers l ON pr.lawyer_id = l.id 
                               WHERE pr.token = ? AND pr.expires_at > NOW()");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();
        
        if ($reset) {
            $valid_token = true;
            $lawyer_id = $reset['lawyer_id'];
        } else {
            $error = 'Ce lien de réinitialisation est invalide ou a expiré.';
        }
    } catch (PDOException $e) {
        $error = 'Erreur de connexion.';
    }
} else {
    $error = 'Token manquant.';
}

// Process password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        try {
            // Update password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE lawyers SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $lawyer_id]);
            
            // Delete used token
            $stmt = $conn->prepare("DELETE FROM password_resets WHERE lawyer_id = ?");
            $stmt->execute([$lawyer_id]);
            
            // Log activity
            $stmt = $conn->prepare("INSERT INTO activity_logs (lawyer_id, action, details, ip_address) VALUES (?, 'password_reset_completed', 'Password successfully reset', ?)");
            $stmt->execute([$lawyer_id, $_SERVER['REMOTE_ADDR']]);
            
            $success = 'Votre mot de passe a été réinitialisé avec succès !';
            $valid_token = false;
        } catch (PDOException $e) {
            $error = 'Erreur lors de la réinitialisation du mot de passe.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe - <?php echo APP_NAME; ?></title>
    
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
        .reset-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        .reset-card {
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
            margin-bottom: 15px;
        }
        h2 {
            color: #D4AF37;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #999;
            font-size: 14px;
            text-align: center;
            margin-bottom: 25px;
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
        }
        .form-control:focus {
            background: #2d2d2d;
            border-color: #D4AF37;
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
        }
        .btn-reset {
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
        .btn-reset:hover {
            background: #c49a2c;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.4);
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #D4AF37;
            text-decoration: none;
            font-weight: bold;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h2>Nouveau mot de passe</h2>
                <p class="subtitle">Entrez votre nouveau mot de passe</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
                <div class="login-link">
                    <a href="login_lawyer.php">
                        <i class="fas fa-sign-in-alt"></i> Se connecter maintenant
                    </a>
                </div>
            <?php elseif ($valid_token): ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Nouveau mot de passe
                    </label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Minimum 8 caractères" required minlength="8" autofocus>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">
                        <i class="fas fa-lock"></i> Confirmer le mot de passe
                    </label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="Retapez votre mot de passe" required minlength="8">
                </div>

                <button type="submit" class="btn btn-reset">
                    <i class="fas fa-check"></i> Réinitialiser le mot de passe
                </button>
            </form>
            <?php else: ?>
                <div class="login-link">
                    <a href="login_lawyer.php">
                        <i class="fas fa-arrow-left"></i> Retour à la connexion
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>






