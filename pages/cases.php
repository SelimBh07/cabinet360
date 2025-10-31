<?php
/**
 * Cabinet360 - Cases Management
 */

require_once __DIR__ . '/../config/auth.php';

$page_title = 'Gestion des Dossiers';
$success = '';
$error = '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search & Filter
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$filter_type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';

try {
    // Build query
    $where_clauses = ["cs.lawyer_id = ?"];
    $params = [$lawyer_id];
    
    if ($search) {
        $where_clauses[] = "(cs.case_number LIKE ? OR cl.full_name LIKE ? OR cs.lawyer LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if ($filter_status) {
        $where_clauses[] = "cs.status = ?";
        $params[] = $filter_status;
    }
    
    if ($filter_type) {
        $where_clauses[] = "cs.type = ?";
        $params[] = $filter_type;
    }
    
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
    
    // Count total cases
    $count_sql = "SELECT COUNT(*) as total FROM cases cs JOIN clients cl ON cs.client_id = cl.id $where_sql";
    $stmt = $conn->prepare($count_sql);
    $stmt->execute($params);
    $total_cases = $stmt->fetch()['total'];
    $total_pages = ceil($total_cases / $per_page);
    
    // Fetch cases
    $sql = "SELECT cs.*, cl.full_name as client_name FROM cases cs 
            JOIN clients cl ON cs.client_id = cl.id 
            $where_sql 
            ORDER BY cs.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $cases = $stmt->fetchAll();
    
    // Get clients for dropdown
    $stmt = $conn->prepare("SELECT id, full_name FROM clients WHERE lawyer_id = ? ORDER BY full_name");
    $stmt->execute([$lawyer_id]);
    $clients = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="fas fa-briefcase"></i> Gestion des Dossiers
            </h1>
            <p class="page-subtitle">Total: <?php echo $total_cases; ?> dossier(s)</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCaseModal">
            <i class="fas fa-plus"></i> Nouveau Dossier
        </button>
    </div>
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

<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-5">
                <input type="text" class="form-control" name="search" 
                       placeholder="Rechercher par numéro, client ou avocat..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="type">
                    <option value="">Tous les types</option>
                    <option value="civil" <?php echo $filter_type == 'civil' ? 'selected' : ''; ?>>Civil</option>
                    <option value="penal" <?php echo $filter_type == 'penal' ? 'selected' : ''; ?>>Pénal</option>
                    <option value="commercial" <?php echo $filter_type == 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                    <option value="administratif" <?php echo $filter_type == 'administratif' ? 'selected' : ''; ?>>Administratif</option>
                    <option value="familial" <?php echo $filter_type == 'familial' ? 'selected' : ''; ?>>Familial</option>
                    <option value="autre" <?php echo $filter_type == 'autre' ? 'selected' : ''; ?>>Autre</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">Tous les statuts</option>
                    <option value="ouvert" <?php echo $filter_status == 'ouvert' ? 'selected' : ''; ?>>Ouvert</option>
                    <option value="en_cours" <?php echo $filter_status == 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                    <option value="clos" <?php echo $filter_status == 'clos' ? 'selected' : ''; ?>>Clos</option>
                    <option value="suspendu" <?php echo $filter_status == 'suspendu' ? 'selected' : ''; ?>>Suspendu</option>
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

<!-- Cases Table -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-list"></i> Liste des Dossiers
    </div>
    <div class="card-body">
        <?php if (!empty($cases)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>N° Dossier</th>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Avocat</th>
                            <th>Date d'ouverture</th>
                            <th>Document</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cases as $case): ?>
                            <tr>
                                <td><strong class="text-gold"><?php echo htmlspecialchars($case['case_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($case['client_name']); ?></td>
                                <td><span class="badge bg-info"><?php echo ucfirst($case['type']); ?></span></td>
                                <td>
                                    <?php
                                    $status_colors = [
                                        'ouvert' => 'bg-warning',
                                        'en_cours' => 'bg-success',
                                        'clos' => 'bg-secondary',
                                        'suspendu' => 'bg-danger'
                                    ];
                                    $status_class = $status_colors[$case['status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $case['status'])); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($case['lawyer']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($case['date_opened'])); ?></td>
                                <td>
                                    <?php if ($case['document_path']): ?>
                                        <a href="<?php echo APP_URL . '/' . $case['document_path']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-info view-case" data-id="<?php echo $case['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning edit-case" data-id="<?php echo $case['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-case" data-id="<?php echo $case['id']; ?>" 
                                                data-number="<?php echo htmlspecialchars($case['case_number']); ?>">
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
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?><?php echo $filter_type ? '&type=' . $filter_type : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucun dossier trouvé</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Case Modal -->
<div class="modal fade" id="addCaseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-folder-plus"></i> Nouveau Dossier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCaseForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">N° Dossier *</label>
                            <input type="text" class="form-control" name="case_number" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client *</label>
                            <select class="form-select" name="client_id" required>
                                <option value="">Sélectionner un client</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description du Dossier</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Décrivez brièvement le dossier..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type *</label>
                            <select class="form-select" name="type" required>
                                <option value="civil">Civil</option>
                                <option value="penal">Pénal</option>
                                <option value="commercial">Commercial</option>
                                <option value="administratif">Administratif</option>
                                <option value="familial">Familial</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Statut *</label>
                            <select class="form-select" name="status" required>
                                <option value="ouvert">Ouvert</option>
                                <option value="en_cours">En cours</option>
                                <option value="clos">Clos</option>
                                <option value="suspendu">Suspendu</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Avocat Assigné</label>
                            <input type="text" class="form-control" name="lawyer">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date d'ouverture *</label>
                            <input type="date" class="form-control" name="date_opened" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Document (PDF/DOCX - Max 5MB)</label>
                            <input type="file" class="form-control" name="document" accept=".pdf,.doc,.docx">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
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

<!-- Edit Case Modal -->
<div class="modal fade" id="editCaseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier Dossier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCaseForm" enctype="multipart/form-data">
                <input type="hidden" name="case_id" id="edit_case_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">N° Dossier *</label>
                            <input type="text" class="form-control" name="case_number" id="edit_case_number" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client *</label>
                            <select class="form-select" name="client_id" id="edit_client_id" required>
                                <option value="">Sélectionner un client</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description du Dossier</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3" placeholder="Décrivez brièvement le dossier..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type *</label>
                            <select class="form-select" name="type" id="edit_type" required>
                                <option value="civil">Civil</option>
                                <option value="penal">Pénal</option>
                                <option value="commercial">Commercial</option>
                                <option value="administratif">Administratif</option>
                                <option value="familial">Familial</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Statut *</label>
                            <select class="form-select" name="status" id="edit_status" required>
                                <option value="ouvert">Ouvert</option>
                                <option value="en_cours">En cours</option>
                                <option value="clos">Clos</option>
                                <option value="suspendu">Suspendu</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Avocat Assigné</label>
                            <input type="text" class="form-control" name="lawyer" id="edit_lawyer">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date d'ouverture *</label>
                            <input type="date" class="form-control" name="date_opened" id="edit_date_opened" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Nouveau Document (optionnel)</label>
                            <input type="file" class="form-control" name="document" accept=".pdf,.doc,.docx">
                            <small class="text-muted" id="current_document"></small>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="edit_notes" rows="3"></textarea>
                        </div>
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

<!-- View Case Modal -->
<div class="modal fade" id="viewCaseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-folder-open"></i> Détails du Dossier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="caseDetails">
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
<script src='" . APP_URL . "/assets/js/cases.js'></script>
";

require_once __DIR__ . '/../includes/footer.php';
?>

