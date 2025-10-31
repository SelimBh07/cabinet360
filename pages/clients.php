<?php
/**
 * Cabinet360 - Clients Management
 */

require_once __DIR__ . '/../config/auth.php';

$page_title = 'Gestion des Clients';
$success = '';
$error = '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';


try {
    $lawyer_id = $_SESSION['lawyer_id'];
    // Count total clients
    if ($search) {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM clients 
                                WHERE lawyer_id = ? AND (full_name LIKE ? OR cin LIKE ? OR phone LIKE ? OR email LIKE ?)");
        $search_term = "%$search%";
        $stmt->execute([$lawyer_id, $search_term, $search_term, $search_term, $search_term]);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM clients WHERE lawyer_id = ?");
        $stmt->execute([$lawyer_id]);
    }
    $total_clients = $stmt->fetch()['total'];
    $total_pages = ceil($total_clients / $per_page);
    
    // Fetch clients
    if ($search) {
        $stmt = $conn->prepare("SELECT * FROM clients 
                                WHERE lawyer_id = ? AND (full_name LIKE ? OR cin LIKE ? OR phone LIKE ? OR email LIKE ?)
                                ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$lawyer_id, $search_term, $search_term, $search_term, $search_term, $per_page, $offset]);
    } else {
        $stmt = $conn->prepare("SELECT * FROM clients WHERE lawyer_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$lawyer_id, $per_page, $offset]);
    }
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
                <i class="fas fa-users"></i> Gestion des Clients
            </h1>
            <p class="page-subtitle">Total: <?php echo $total_clients; ?> client(s)</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
            <i class="fas fa-plus"></i> Nouveau Client
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

<!-- Search Bar -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-10">
                <input type="text" class="form-control" name="search" 
                       placeholder="Rechercher par nom, CIN, téléphone ou email..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Rechercher
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Clients Table -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-list"></i> Liste des Clients
    </div>
    <div class="card-body">
        <?php if (!empty($clients)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom Complet</th>
                            <th>CIN</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>Date d'ajout</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?php echo $client['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($client['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($client['cin']); ?></td>
                                <td><?php echo htmlspecialchars($client['phone']); ?></td>
                                <td><?php echo htmlspecialchars($client['email']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($client['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-info view-client" data-id="<?php echo $client['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning edit-client" data-id="<?php echo $client['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-client" data-id="<?php echo $client['id']; ?>" 
                                                data-name="<?php echo htmlspecialchars($client['full_name']); ?>">
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
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucun client trouvé</p>
                <?php if ($search): ?>
                    <a href="clients.php" class="btn btn-sm btn-outline-primary">Réinitialiser la recherche</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Client Modal -->
<div class="modal fade" id="addClientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Nouveau Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addClientForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom Complet *</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CIN *</label>
                            <input type="text" class="form-control" name="cin" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" class="form-control" name="phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Adresse</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
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

<!-- Edit Client Modal -->
<div class="modal fade" id="editClientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editClientForm">
                <input type="hidden" name="client_id" id="edit_client_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom Complet *</label>
                            <input type="text" class="form-control" name="full_name" id="edit_full_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CIN *</label>
                            <input type="text" class="form-control" name="cin" id="edit_cin" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" class="form-control" name="phone" id="edit_phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="edit_email">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Adresse</label>
                            <textarea class="form-control" name="address" id="edit_address" rows="2"></textarea>
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

<!-- View Client Modal -->
<div class="modal fade" id="viewClientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user"></i> Détails du Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="clientDetails">
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
<script src='" . APP_URL . "/assets/js/clients.js'></script>
";

require_once __DIR__ . '/../includes/footer.php';
?>

