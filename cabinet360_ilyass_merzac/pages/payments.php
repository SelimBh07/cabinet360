<?php
/**
 * Cabinet360 - Payments Management
 */

require_once __DIR__ . '/../config/auth.php';

$page_title = 'Gestion des Paiements';
$success = '';
$error = '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Filter
$filter_status = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

try {
    // Build query (MULTI-TENANT: Filtered by lawyer_id)
    $where_clause = "WHERE p.lawyer_id = ?";
    $params = [$lawyer_id];
    
    if ($filter_status) {
        $where_clause .= " AND p.status = ?";
        $params[] = $filter_status;
    }
    
    // Count total payments
    $count_sql = "SELECT COUNT(*) as total FROM payments p $where_clause";
    $stmt = $conn->prepare($count_sql);
    $stmt->execute($params);
    $total_payments = $stmt->fetch()['total'];
    $total_pages = ceil($total_payments / $per_page);
    
    // Fetch payments
    $sql = "SELECT p.*, c.full_name as client_name, cs.case_number 
            FROM payments p 
            JOIN clients c ON p.client_id = c.id AND c.lawyer_id = ?
            LEFT JOIN cases cs ON p.case_id = cs.id AND cs.lawyer_id = ?
            $where_clause 
            ORDER BY p.date DESC LIMIT ? OFFSET ?";
    $params[] = $lawyer_id;
    $params[] = $lawyer_id;
    $params[] = $per_page;
    $params[] = $offset;
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $payments = $stmt->fetchAll();
    
    // Calculate statistics (MULTI-TENANT: Filtered by lawyer_id)
    $stmt = $conn->prepare("SELECT 
                           COALESCE(SUM(CASE WHEN status = 'payé' THEN amount ELSE 0 END), 0) as total_paid,
                           COALESCE(SUM(CASE WHEN status = 'impayé' THEN amount ELSE 0 END), 0) as total_unpaid,
                           COALESCE(SUM(CASE WHEN status = 'partiel' THEN amount ELSE 0 END), 0) as total_partial
                           FROM payments WHERE lawyer_id = ?");
    $stmt->execute([$lawyer_id]);
    $stats = $stmt->fetch();
    
    // Get clients for dropdown (MULTI-TENANT: Filtered by lawyer_id)
    $stmt = $conn->prepare("SELECT id, full_name FROM clients WHERE lawyer_id = ? ORDER BY full_name");
    $stmt->execute([$lawyer_id]);
    $clients = $stmt->fetchAll();
    
    // Get cases for dropdown (MULTI-TENANT: Filtered by lawyer_id)
    $stmt = $conn->prepare("SELECT id, case_number FROM cases WHERE lawyer_id = ? ORDER BY case_number");
    $stmt->execute([$lawyer_id]);
    $cases_list = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="fas fa-money-bill-wave"></i> Gestion des Paiements
            </h1>
            <p class="page-subtitle">Total: <?php echo $total_payments; ?> paiement(s)</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
            <i class="fas fa-plus"></i> Nouveau Paiement
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon text-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-number"><?php echo number_format($stats['total_paid'] ?? 0, 2); ?> MAD</div>
            <div class="stat-label">Payé</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon text-danger">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-number"><?php echo number_format($stats['total_unpaid'] ?? 0, 2); ?> MAD</div>
            <div class="stat-label">Impayé</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon text-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-number"><?php echo number_format($stats['total_partial'] ?? 0, 2); ?> MAD</div>
            <div class="stat-label">Partiel</div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-10">
                <select class="form-select" name="status">
                    <option value="">Tous les statuts</option>
                    <option value="payé" <?php echo $filter_status == 'payé' ? 'selected' : ''; ?>>Payé</option>
                    <option value="impayé" <?php echo $filter_status == 'impayé' ? 'selected' : ''; ?>>Impayé</option>
                    <option value="partiel" <?php echo $filter_status == 'partiel' ? 'selected' : ''; ?>>Partiel</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Filtrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Payments Table -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-list"></i> Liste des Paiements
    </div>
    <div class="card-body">
        <?php if (!empty($payments)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Dossier</th>
                            <th>Montant</th>
                            <th>Méthode</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($payment['date'])); ?></td>
                                <td><?php echo htmlspecialchars($payment['client_name']); ?></td>
                                <td><?php echo $payment['case_number'] ? htmlspecialchars($payment['case_number']) : '<span class="text-muted">-</span>'; ?></td>
                                <td><strong class="text-gold"><?php echo number_format($payment['amount'], 2); ?> MAD</strong></td>
                                <td><span class="badge bg-info"><?php echo ucfirst($payment['method']); ?></span></td>
                                <td>
                                    <?php
                                    $status_colors = [
                                        'payé' => 'bg-success',
                                        'impayé' => 'bg-danger',
                                        'partiel' => 'bg-warning'
                                    ];
                                    $status_class = $status_colors[$payment['status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($payment['status']); ?></span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-info view-payment" data-id="<?php echo $payment['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="../actions/generate_receipt.php?id=<?php echo $payment['id']; ?>" 
                                           target="_blank" class="btn btn-sm btn-success">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <button class="btn btn-sm btn-warning edit-payment" data-id="<?php echo $payment['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-payment" data-id="<?php echo $payment['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center mt-4">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucun paiement enregistré</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-money-bill-wave"></i> Nouveau Paiement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPaymentForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Client *</label>
                        <select class="form-select" name="client_id" id="add_client_id" required>
                            <option value="">Sélectionner un client</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dossier (optionnel)</label>
                        <select class="form-select" name="case_id" id="add_case_id">
                            <option value="">Aucun dossier spécifique</option>
                            <?php foreach ($cases_list as $case): ?>
                                <option value="<?php echo $case['id']; ?>"><?php echo htmlspecialchars($case['case_number']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date *</label>
                        <input type="date" class="form-control" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Montant (MAD) *</label>
                        <input type="number" step="0.01" class="form-control" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Méthode de paiement *</label>
                        <select class="form-select" name="method" required>
                            <option value="espèces">Espèces</option>
                            <option value="chèque">Chèque</option>
                            <option value="virement">Virement</option>
                            <option value="carte">Carte bancaire</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Statut *</label>
                        <select class="form-select" name="status" required>
                            <option value="payé">Payé</option>
                            <option value="impayé">Impayé</option>
                            <option value="partiel">Partiel</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Payment Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier Paiement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPaymentForm">
                <input type="hidden" name="payment_id" id="edit_payment_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Client *</label>
                        <select class="form-select" name="client_id" id="edit_client_id" required>
                            <option value="">Sélectionner un client</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dossier (optionnel)</label>
                        <select class="form-select" name="case_id" id="edit_case_id">
                            <option value="">Aucun dossier spécifique</option>
                            <?php foreach ($cases_list as $case): ?>
                                <option value="<?php echo $case['id']; ?>"><?php echo htmlspecialchars($case['case_number']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date *</label>
                        <input type="date" class="form-control" name="date" id="edit_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Montant (MAD) *</label>
                        <input type="number" step="0.01" class="form-control" name="amount" id="edit_amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Méthode de paiement *</label>
                        <select class="form-select" name="method" id="edit_method" required>
                            <option value="espèces">Espèces</option>
                            <option value="chèque">Chèque</option>
                            <option value="virement">Virement</option>
                            <option value="carte">Carte bancaire</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Statut *</label>
                        <select class="form-select" name="status" id="edit_status" required>
                            <option value="payé">Payé</option>
                            <option value="impayé">Impayé</option>
                            <option value="partiel">Partiel</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" id="edit_notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Payment Modal -->
<div class="modal fade" id="viewPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-receipt"></i> Détails du Paiement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentDetails">
                <div class="text-center">
                    <div class="spinner-border text-gold" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$page_scripts = "
<script src='" . APP_URL . "/assets/js/payments.js'></script>
";

require_once __DIR__ . '/../includes/footer.php';
?>

