<?php
/**
 * Cabinet360 SaaS - Forgot Password
 */

require_once 'config/config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $stmt = $conn->prepare("SELECT id, lawyer_name FROM lawyers WHERE email = ? AND is_active = TRUE");
            $stmt->execute([$email]);
            $lawyer = $stmt->fetch();
            
            if ($lawyer) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour
                
                // Store token
                $stmt = $conn->prepare("INSERT INTO password_resets (lawyer_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$lawyer['id'], $token, $expires_at]);
                
                // In production, send email with reset link
                $reset_link = APP_URL . "/reset_password.php?token=" . $token;
                
                // For now, show the link (in production, this should be sent via email)
                $message = "Un lien de réinitialisation a été généré. <br><br><strong>Lien de réinitialisation:</strong><br><a href='$reset_link' style='color: #D4AF37;'>$reset_link</a><br><br><small class='text-muted'>(En production, ce lien sera envoyé par email)</small>";
                
                // Log activity
                $stmt = $conn->prepare("INSERT INTO activity_logs (lawyer_id, action, details, ip_address) VALUES (?, 'password_reset_requested', 'Password reset requested', ?)");
                $stmt->execute([$lawyer['id'], $_SERVER['REMOTE_ADDR']]);
            } else {
                $message = "Si cette adresse email existe dans notre système, un lien de réinitialisation a été envoyé.";
            }
        } catch (PDOException $e) {
            $error = 'Erreur lors du traitement de la demande.';
        }
    } else {
        $error = 'Veuillez entrer une adresse email valide.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - <?php echo APP_NAME; ?></title>
    
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
        .forgot-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        .forgot-card {
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
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #999;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link a:hover {
            color: #D4AF37;
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-card">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h2>Mot de passe oublié ?</h2>
                <p class="subtitle">Entrez votre email pour réinitialiser votre mot de passe</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if (!$message): ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Adresse Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="votre.email@example.com" required autofocus>
                </div>

                <button type="submit" class="btn btn-reset">
                    <i class="fas fa-paper-plane"></i> Envoyer le lien de réinitialisation
                </button>
            </form>
            <?php endif; ?>

            <div class="back-link">
                <a href="login_lawyer.php">
                    <i class="fas fa-arrow-left"></i> Retour à la connexion
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



