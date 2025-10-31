<?php
/**
 * Cabinet360 SaaS - Subscription Management
 */

require_once __DIR__ . '/../config/auth.php';

$page_title = 'Mon Abonnement';

// Fetch current subscription
try {
    $stmt = $conn->prepare("SELECT * FROM lawyers WHERE id = ?");
    $stmt->execute([$lawyer_id]);
    $lawyer_info = $stmt->fetch();
    
    // Fetch all plans
    $stmt = $conn->query("SELECT * FROM subscription_plans ORDER BY price ASC");
    $plans = $stmt->fetchAll();
    
    // If no plans exist, create default ones
    if (empty($plans)) {
        $default_plans = [
            [
                'plan_name' => 'Free',
                'price' => 0,
                'billing_cycle' => 'monthly',
                'max_clients' => 5,
                'max_cases' => 10,
                'max_storage_mb' => 100,
                'features' => json_encode([
                    'basic_support' => true,
                    'email_notifications' => true,
                    'basic_reports' => true
                ])
            ],
            [
                'plan_name' => 'Pro',
                'price' => 299,
                'billing_cycle' => 'monthly',
                'max_clients' => 50,
                'max_cases' => 100,
                'max_storage_mb' => 1000,
                'features' => json_encode([
                    'priority_support' => true,
                    'email_notifications' => true,
                    'advanced_reports' => true,
                    'pdf_generation' => true,
                    'calendar_sync' => true
                ])
            ],
            [
                'plan_name' => 'Premium',
                'price' => 599,
                'billing_cycle' => 'monthly',
                'max_clients' => 0, // unlimited
                'max_cases' => 0, // unlimited
                'max_storage_mb' => 5000,
                'features' => json_encode([
                    'priority_support' => true,
                    'email_notifications' => true,
                    'advanced_reports' => true,
                    'pdf_generation' => true,
                    'calendar_sync' => true,
                    'api_access' => true,
                    'white_label' => true,
                    'custom_branding' => true
                ])
            ]
        ];
        
        $stmt = $conn->prepare("INSERT INTO subscription_plans (plan_name, price, billing_cycle, max_clients, max_cases, max_storage_mb, features) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($default_plans as $plan) {
            $stmt->execute([
                $plan['plan_name'],
                $plan['price'],
                $plan['billing_cycle'],
                $plan['max_clients'],
                $plan['max_cases'],
                $plan['max_storage_mb'],
                $plan['features']
            ]);
        }
        
        // Refetch plans
        $stmt = $conn->query("SELECT * FROM subscription_plans ORDER BY price ASC");
        $plans = $stmt->fetchAll();
    }
    
} catch (PDOException $e) {
    $error = "Erreur: " . $e->getMessage();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-crown"></i> Mon Abonnement
    </h1>
    <p class="page-subtitle">Gérez votre plan d'abonnement Cabinet360</p>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<!-- Current Subscription -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-info-circle"></i> Abonnement Actuel
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h4 class="text-gold">
                    <?php echo strtoupper($lawyer_info['subscription_plan'] ?? 'FREE'); ?> PLAN
                </h4>
                <p class="text-muted">
                    Statut: 
                    <?php
                    $status = $lawyer_info['subscription_status'] ?? 'active';
                    $status_class = $status == 'active' ? 'success' : 'warning';
                    echo "<span class='badge bg-$status_class'>" . ucfirst($status) . "</span>";
                    ?>
                </p>
                <p class="text-muted">
                    <i class="fas fa-calendar"></i> Membre depuis: <?php echo date('d/m/Y', strtotime($lawyer_info['created_at'])); ?>
                </p>
            </div>
            <div class="col-md-6 text-end">
                <p class="text-muted">ID Cabinet: #<?php echo $lawyer_id; ?></p>
                <p class="text-muted">Email: <?php echo htmlspecialchars($lawyer_info['email']); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Available Plans -->
<h3 class="mb-4"><i class="fas fa-star"></i> Plans Disponibles</h3>

<div class="row">
    <?php if (!empty($plans)): ?>
        <?php foreach ($plans as $plan): ?>
            <?php 
            $is_current = ($plan['plan_name'] == ($lawyer_info['subscription_plan'] ?? 'Free'));
            $features = json_decode($plan['features'], true);
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 <?php echo $is_current ? 'border-gold' : ''; ?>">
                    <div class="card-header text-center <?php echo $is_current ? 'bg-gold text-dark' : ''; ?>">
                        <h4><?php echo strtoupper($plan['plan_name']); ?></h4>
                        <?php if ($is_current): ?>
                            <span class="badge bg-success">Plan Actuel</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-gold mb-3">
                            <?php echo ($plan['price'] == 0) ? 'GRATUIT' : number_format($plan['price'], 0) . ' MAD'; ?>
                        </h2>
                        <p class="text-muted">/ mois</p>
                        
                        <hr>
                        
                        <ul class="list-unstyled text-start">
                            <?php if ($plan['max_clients'] > 0): ?>
                                <li class="mb-2"><i class="fas fa-users text-gold"></i> <?php echo $plan['max_clients']; ?> clients maximum</li>
                            <?php else: ?>
                                <li class="mb-2"><i class="fas fa-users text-gold"></i> Clients illimités</li>
                            <?php endif; ?>
                            
                            <?php if ($plan['max_cases'] > 0): ?>
                                <li class="mb-2"><i class="fas fa-briefcase text-gold"></i> <?php echo $plan['max_cases']; ?> dossiers maximum</li>
                            <?php else: ?>
                                <li class="mb-2"><i class="fas fa-briefcase text-gold"></i> Dossiers illimités</li>
                            <?php endif; ?>
                            
                            <li class="mb-2"><i class="fas fa-database text-gold"></i> <?php echo $plan['max_storage_mb']; ?> MB stockage</li>
                            
                            <?php if ($features): ?>
                                <?php foreach ($features as $key => $value): ?>
                                    <?php if ($value === true || $value === 'true' || (is_string($value) && $value != 'false')): ?>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success"></i> 
                                            <?php echo ucfirst(str_replace('_', ' ', $key)); ?>
                                            <?php if (is_string($value) && $value !== 'true'): ?>
                                                : <?php echo $value; ?>
                                            <?php endif; ?>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                        
                        <?php if (!$is_current): ?>
                            <button class="btn btn-gold w-100 mt-3" onclick="upgradePlan('<?php echo $plan['plan_name']; ?>')">
                                <i class="fas fa-arrow-up"></i> Passer à ce plan
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Help Section -->
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-question-circle"></i> Besoin d'aide ?
    </div>
    <div class="card-body">
        <p>Pour toute question concernant votre abonnement ou pour changer de plan, contactez-nous:</p>
        <p>
            <i class="fas fa-envelope"></i> Email: <a href="mailto:support@cabinet360.com">support@cabinet360.com</a><br>
            <i class="fas fa-phone"></i> Téléphone: +212 6 00 00 00 00
        </p>
    </div>
</div>

<?php 
$page_scripts = "
<script>
function upgradePlan(planName) {
    if (confirm('Êtes-vous sûr de vouloir passer au plan ' + planName + ' ?')) {
        alert('Fonctionnalité de paiement à venir. Contactez support@cabinet360.com pour upgrader votre plan.');
    }
}
</script>
";

require_once __DIR__ . '/../includes/footer.php';
?>


