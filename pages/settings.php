<?php
/**
 * Cabinet360 - Settings & Profile
 */

require_once __DIR__ . '/../config/auth.php';

$page_title = 'Paramètres';
$success = '';
$error = '';

// Get current lawyer info (Multi-tenant SaaS)
$stmt = $conn->prepare("SELECT * FROM lawyers WHERE id = ?");
$stmt->execute([$lawyer_id]);
$lawyer_info = $stmt->fetch();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            if ($_POST['action'] == 'update_profile') {
                $cabinet_name_update = sanitize_input($_POST['cabinet_name']);
                $lawyer_name_update = sanitize_input($_POST['lawyer_name']);
                $phone_update = sanitize_input($_POST['phone']);
                $address_update = sanitize_input($_POST['address']);
                
                $stmt = $conn->prepare("UPDATE lawyers SET cabinet_name = ?, lawyer_name = ?, phone = ?, address = ? WHERE id = ?");
                $stmt->execute([$cabinet_name_update, $lawyer_name_update, $phone_update, $address_update, $lawyer_id]);
                
                $_SESSION['cabinet_name'] = $cabinet_name_update;
                $_SESSION['lawyer_name'] = $lawyer_name_update;
                $success = "Profil mis à jour avec succès!";
                
                // Refresh lawyer data
                $stmt = $conn->prepare("SELECT * FROM lawyers WHERE id = ?");
                $stmt->execute([$lawyer_id]);
                $lawyer_info = $stmt->fetch();
                
            } elseif ($_POST['action'] == 'change_password') {
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                
                if (!password_verify($current_password, $lawyer_info['password'])) {
                    $error = "Mot de passe actuel incorrect";
                } elseif ($new_password !== $confirm_password) {
                    $error = "Les nouveaux mots de passe ne correspondent pas";
                } elseif (strlen($new_password) < 8) {
                    $error = "Le mot de passe doit contenir au moins 8 caractères";
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    $stmt = $conn->prepare("UPDATE lawyers SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $lawyer_id]);
                    $success = "Mot de passe changé avec succès!";
                }
            }
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/UserPreferences.php';
$userPrefs = new UserPreferences($conn, $lawyer_id);
$prefs = $userPrefs->getPreferences();
// Language loader
$lang_code = $prefs['language'] ?? 'fr';
switch ($lang_code) {
    case 'en':
        $lang = include __DIR__ . '/../lang/lang_en.php';
        break;
    case 'ar':
        $lang = include __DIR__ . '/../lang/lang_ar.php';
        break;
    default:
        $lang = include __DIR__ . '/../lang/lang_fr.php';
}
function t($key) { global $lang; return $lang[$key] ?? $key; }
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-cog"></i> <?php echo t('settings'); ?> 
    </h1>
    <p class="page-subtitle"><?php echo t('profile_info'); ?> & <?php echo t('cabinet_info'); ?></p>
</div>

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

<div class="row">
    <!-- Profile Settings -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user"></i> <?php echo t('profile_info'); ?>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="mb-3">
                        <label class="form-label">Nom d'utilisateur</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <small class="text-muted">Le nom d'utilisateur ne peut pas être modifié</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nom Complet *</label>
                        <input type="text" class="form-control" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Rôle</label>
                        <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Membre depuis</label>
                        <input type="text" class="form-control" 
                               value="<?php echo date('d/m/Y', strtotime($user['created_at'])); ?>" disabled>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Change Password -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-lock"></i> <?php echo t('change_password'); ?>
            </div>
            <div class="card-body">
                <form method="POST" id="passwordForm">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-3">
                        <label class="form-label">Mot de passe actuel *</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nouveau mot de passe *</label>
                        <input type="password" class="form-control" name="new_password" 
                               id="new_password" minlength="6" required>
                        <small class="text-muted">Au moins 6 caractères</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirmer le nouveau mot de passe *</label>
                        <input type="password" class="form-control" name="confirm_password" 
                               id="confirm_password" minlength="6" required>
                    </div>
                    
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="fas fa-key"></i> Changer le mot de passe
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Cabinet Settings -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-building"></i> <?php echo t('cabinet_info'); ?>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    Ces informations apparaissent sur les reçus et documents générés.
                </div>
                
                <form>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom du Cabinet</label>
                            <input type="text" class="form-control" value="Cabinet360" placeholder="Nom du cabinet">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" class="form-control" placeholder="+212 XXX XXX XXX">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" placeholder="contact@cabinet360.ma">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Site Web</label>
                            <input type="text" class="form-control" placeholder="www.cabinet360.ma">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Adresse</label>
                            <textarea class="form-control" rows="2" placeholder="Adresse complète du cabinet"></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" disabled>
                        <i class="fas fa-save"></i> Enregistrer (Prochainement)
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Application Settings -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-palette"></i> <?php echo t('appearance'); ?>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><?php echo t('theme'); ?></label>
                    <select class="form-select" id="themeSelect">
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bell"></i> Notifications
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="emailNotif" checked disabled>
                    <label class="form-check-label" for="emailNotif">
                        Notifications par email (Prochainement)
                    </label>
                </div>
                
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="appointmentReminder" checked disabled>
                    <label class="form-check-label" for="appointmentReminder">
                        Rappels de rendez-vous (Prochainement)
                    </label>
                </div>
                
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="paymentReminder" checked disabled>
                    <label class="form-check-label" for="paymentReminder">
                        Rappels de paiement (Prochainement)
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Database Info -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-database"></i> Informations Système
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="stat-box">
                            <i class="fas fa-users fa-2x text-gold mb-2"></i>
                            <h4 class="text-gold"><?php 
                                $stmt = $conn->query("SELECT COUNT(*) as count FROM clients");
                                echo $stmt->fetch()['count'];
                            ?></h4>
                            <small>Clients Total</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-box">
                            <i class="fas fa-briefcase fa-2x text-gold mb-2"></i>
                            <h4 class="text-gold"><?php 
                                $stmt = $conn->query("SELECT COUNT(*) as count FROM cases");
                                echo $stmt->fetch()['count'];
                            ?></h4>
                            <small>Dossiers Total</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-box">
                            <i class="fas fa-calendar fa-2x text-gold mb-2"></i>
                            <h4 class="text-gold"><?php 
                                $stmt = $conn->query("SELECT COUNT(*) as count FROM appointments");
                                echo $stmt->fetch()['count'];
                            ?></h4>
                            <small>Rendez-vous Total</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-box">
                            <i class="fas fa-money-bill fa-2x text-gold mb-2"></i>
                            <h4 class="text-gold"><?php 
                                $stmt = $conn->query("SELECT COUNT(*) as count FROM payments");
                                echo $stmt->fetch()['count'];
                            ?></h4>
                            <small>Paiements Total</small>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <p class="mb-1"><strong>Version:</strong> Cabinet360 v1.0</p>
                <p class="mb-1"><strong>Base de données:</strong> MySQL</p>
                <p class="mb-1"><strong>Dernier backup:</strong> <span class="text-warning">Aucun (Fonctionnalité à venir)</span></p>
            </div>
        </div>
    </div>
</div>

<?php
$page_scripts = "
<script>
// Password confirmation validation
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_password').value;
    
    if (newPass !== confirmPass) {
        e.preventDefault();
        alert('Les mots de passe ne correspondent pas!');
        return false;
    }
});
</script>
";

require_once __DIR__ . '/../includes/footer.php';
?>






