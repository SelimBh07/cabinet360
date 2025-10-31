<?php
/**
 * Cabinet360 - Document Management
 */

require_once __DIR__ . '/../config/auth.php';

$page_title = 'Gestion des Documents';

// Get filter parameters
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$filter_type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';

try {
    // Get all cases with documents (MULTI-TENANT: Filtered by lawyer_id)
    $sql = "SELECT c.id, c.case_number, c.type, c.document_path, c.created_at, cl.full_name as client_name 
            FROM cases c 
            JOIN clients cl ON c.client_id = cl.id 
            WHERE c.lawyer_id = ? AND c.document_path IS NOT NULL AND c.document_path != ''";
    $params = [$lawyer_id];
    if ($search) {
        $sql .= " AND (c.case_number LIKE ? OR cl.full_name LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
    }
    if ($filter_type) {
        $sql .= " AND c.type = ?";
        $params[] = $filter_type;
    }
    $sql .= " ORDER BY c.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $documents = $stmt->fetchAll();
    // Count total documents
    $total_docs = count($documents);
    // Calculate total size (if files exist)
    $total_size = 0;
    foreach ($documents as $doc) {
        $file_path = __DIR__ . '/../uploads/' . basename($doc['document_path']);
        if (file_exists($file_path)) {
            $total_size += filesize($file_path);
        }
    }
} catch (PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="fas fa-folder-open"></i> Gestion des Documents
            </h1>
            <p class="page-subtitle">Tous les documents du cabinet</p>
        </div>
        <div>
            <button class="btn btn-info" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimer
            </button>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
            <div class="stat-number"><?php echo $total_docs; ?></div>
            <div class="stat-label">Total Documents</div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-hdd"></i></div>
            <div class="stat-number"><?php echo number_format($total_size / 1024 / 1024, 2); ?> MB</div>
            <div class="stat-label">Espace Utilisé</div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-number"><?php echo !empty($documents) ? date('d/m/Y', strtotime($documents[0]['created_at'])) : 'N/A'; ?></div>
            <div class="stat-label">Dernier Ajout</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <input type="text" class="form-control" name="search" 
                       placeholder="Rechercher par numéro de dossier ou client..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-4">
                <select class="form-select" name="type">
                    <option value="">Tous les types</option>
                    <option value="civil" <?php echo $filter_type == 'civil' ? 'selected' : ''; ?>>Civil</option>
                    <option value="pénal" <?php echo $filter_type == 'pénal' ? 'selected' : ''; ?>>Pénal</option>
                    <option value="commercial" <?php echo $filter_type == 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                    <option value="administratif" <?php echo $filter_type == 'administratif' ? 'selected' : ''; ?>>Administratif</option>
                    <option value="familial" <?php echo $filter_type == 'familial' ? 'selected' : ''; ?>>Familial</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Filtrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Documents Grid -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-file-alt"></i> Liste des Documents
    </div>
    <div class="card-body">
        <?php if (!empty($documents)): ?>
            <div class="row">
                <?php foreach ($documents as $doc): ?>
                    <?php
                    $file_path = __DIR__ . '/../uploads/' . basename($doc['document_path']);
                    $file_exists = file_exists($file_path);
                    $file_size = $file_exists ? filesize($file_path) : 0;
                    $file_ext = pathinfo($doc['document_path'], PATHINFO_EXTENSION);
                    
                    // Icon based on file type
                    $icon = 'fa-file';
                    $icon_color = '#D4AF37';
                    if ($file_ext == 'pdf') {
                        $icon = 'fa-file-pdf';
                        $icon_color = '#dc3545';
                    } elseif (in_array($file_ext, ['doc', 'docx'])) {
                        $icon = 'fa-file-word';
                        $icon_color = '#2196F3';
                    } elseif (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $icon = 'fa-file-image';
                        $icon_color = '#4CAF50';
                    }
                    ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="document-card">
                            <div class="document-icon" style="color: <?php echo $icon_color; ?>">
                                <i class="fas <?php echo $icon; ?> fa-3x"></i>
                            </div>
                            <div class="document-info">
                                <h6 class="document-title"><?php echo htmlspecialchars($doc['case_number']); ?></h6>
                                <p class="document-client">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($doc['client_name']); ?>
                                </p>
                                <div class="document-meta">
                                    <span class="badge bg-info"><?php echo ucfirst($doc['type']); ?></span>
                                    <span class="text-muted"><?php echo number_format($file_size / 1024, 2); ?> KB</span>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($doc['created_at'])); ?>
                                </small>
                            </div>
                            <div class="document-actions">
                                <?php if ($file_exists): ?>
                                    <a href="<?php echo APP_URL; ?>/uploads/<?php echo basename($doc['document_path']); ?>" 
                                       class="btn btn-sm btn-info" target="_blank" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo APP_URL; ?>/uploads/<?php echo basename($doc['document_path']); ?>" 
                                       class="btn btn-sm btn-success" download title="Télécharger">
                                        <i class="fas fa-download"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-danger">Fichier introuvable</span>
                                <?php endif; ?>
                                <a href="<?php echo APP_URL; ?>/pages/cases.php" 
                                   class="btn btn-sm btn-warning" title="Voir le dossier">
                                    <i class="fas fa-folder"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucun document trouvé</p>
                <?php if ($search || $filter_type): ?>
                    <a href="documents.php" class="btn btn-sm btn-outline-primary">Réinitialiser les filtres</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.document-card {
    background: var(--secondary-black);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
    height: 100%;
}

.document-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(212, 175, 55, 0.2);
    border-color: var(--gold);
}

.document-icon {
    margin-bottom: 15px;
}

.document-title {
    color: var(--gold);
    margin-bottom: 10px;
    font-size: 16px;
    font-weight: bold;
}

.document-client {
    color: var(--text-white);
    font-size: 14px;
    margin-bottom: 10px;
}

.document-meta {
    display: flex;
    justify-content: space-around;
    align-items: center;
    margin: 10px 0;
    gap: 10px;
}

.document-actions {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid var(--border-color);
}
</style>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
















