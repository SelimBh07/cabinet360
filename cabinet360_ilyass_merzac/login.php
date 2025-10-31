<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cabinet360 SaaS</title>
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
        .login-selector {
            max-width: 900px;
            width: 100%;
            padding: 20px;
        }
        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }
        .logo-icon {
            font-size: 70px;
            color: #D4AF37;
            margin-bottom: 20px;
        }
        .app-title {
            color: #D4AF37;
            font-size: 40px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 3px;
        }
        .app-subtitle {
            color: #999;
            font-size: 16px;
            margin-top: 10px;
        }
        .badge-saas {
            display: inline-block;
            background: linear-gradient(90deg, #D4AF37, #FFD700);
            color: #1a1a1a;
            padding: 6px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: bold;
            margin-top: 15px;
        }
        .login-options {
            display: flex;
            gap: 30px;
            margin-top: 40px;
        }
        .login-card {
            flex: 1;
            background: #1a1a1a;
            border: 2px solid #D4AF37;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }
        .login-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 50px rgba(212, 175, 55, 0.4);
            border-color: #FFD700;
        }
        .login-card.admin {
            border-color: #FF6B6B;
        }
        .login-card.admin:hover {
            border-color: #FF4444;
            box-shadow: 0 15px 50px rgba(255, 107, 107, 0.4);
        }
        .card-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        .card-icon.lawyer {
            color: #D4AF37;
        }
        .card-icon.admin {
            color: #FF6B6B;
        }
        .card-title {
            font-size: 28px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 15px;
        }
        .card-description {
            color: #999;
            font-size: 15px;
            margin-bottom: 25px;
        }
        .btn-login-type {
            padding: 12px 30px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            border: none;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-lawyer {
            background: #D4AF37;
            color: #1a1a1a;
        }
        .btn-lawyer:hover {
            background: #FFD700;
            color: #000;
        }
        .btn-admin {
            background: #FF6B6B;
            color: #fff;
        }
        .btn-admin:hover {
            background: #FF4444;
            color: #fff;
        }
        .signup-link {
            text-align: center;
            margin-top: 30px;
            color: #999;
            font-size: 15px;
        }
        .signup-link a {
            color: #D4AF37;
            text-decoration: none;
            font-weight: bold;
        }
        .signup-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .login-options {
                flex-direction: column;
            }
            .app-title {
                font-size: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-selector">
        <div class="logo-section">
            <div class="logo-icon">
                <i class="fas fa-balance-scale"></i>
            </div>
            <h1 class="app-title">Cabinet360</h1>
            <p class="app-subtitle">Système de Gestion pour Cabinet d'Avocat</p>
            <span class="badge-saas">
                <i class="fas fa-cloud"></i> Multi-Tenant SaaS Platform
            </span>
        </div>

        <div class="login-options">
            <!-- Lawyer Login Card -->
            <div class="login-card" onclick="window.location.href='login_lawyer.php'">
                <div class="card-icon lawyer">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="card-title">Espace Avocat</div>
                <div class="card-description">
                    Accédez à votre cabinet et gérez vos clients, dossiers et rendez-vous
                </div>
                <button class="btn-login-type btn-lawyer" onclick="window.location.href='login_lawyer.php'">
                    <i class="fas fa-sign-in-alt"></i> Connexion Avocat
                </button>
            </div>

            <!-- Admin Login Card -->
            <div class="login-card admin" onclick="window.location.href='admin/login.php'">
                <div class="card-icon admin">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="card-title">Espace Admin</div>
                <div class="card-description">
                    Gérez tous les avocats et surveillez la plateforme
                </div>
                <button class="btn-login-type btn-admin" onclick="window.location.href='admin/login.php'">
                    <i class="fas fa-lock"></i> Connexion Admin
                </button>
            </div>
        </div>

        <div class="signup-link">
            Vous n'avez pas encore de compte ? 
            <a href="signup.php">
                <i class="fas fa-user-plus"></i> Créer un compte avocat
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
