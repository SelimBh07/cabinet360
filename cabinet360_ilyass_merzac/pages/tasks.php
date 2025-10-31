<?php
/**
 * Cabinet360 - Task Management
 */

require_once __DIR__ . '/../config/auth.php';

$page_title = 'Gestion des Tâches';

// Get filter parameters
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_priority = isset($_GET['priority']) ? $_GET['priority'] : '';

try {
    // Initialize stats with default values
    $stats = [
        'total' => 0,
        'todo' => 0,
        'in_progress' => 0,
        'completed' => 0,
        'overdue' => 0
    ];
    
    // Check if tasks table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'tasks'");
    $tasks_table_exists = $stmt->fetch();
    
    if ($tasks_table_exists) {
        // Get all tasks (MULTI-TENANT: Filtered by lawyer_id)
        $sql = "SELECT t.*, c.case_number, l.lawyer_name as assigned_name 
                FROM tasks t 
                LEFT JOIN cases c ON t.case_id = c.id AND c.lawyer_id = ?
                LEFT JOIN lawyers l ON t.assigned_to = l.id 
                WHERE t.lawyer_id = ?";
        
        $params = [$lawyer_id, $lawyer_id];
        
        if ($filter_status) {
            $sql .= " AND t.status = ?";
            $params[] = $filter_status;
        }
        
        if ($filter_priority) {
            $sql .= " AND t.priority = ?";
            $params[] = $filter_priority;
        }
        
        $sql .= " ORDER BY 
                  FIELD(t.priority, 'urgente', 'haute', 'moyenne', 'basse'),
                  t.due_date ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $tasks = $stmt->fetchAll();
        
        // Get statistics (MULTI-TENANT: Filtered by lawyer_id)
        $stmt = $conn->prepare("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'à_faire' THEN 1 ELSE 0 END) as todo,
            SUM(CASE WHEN status = 'en_cours' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'terminée' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN due_date < CURDATE() AND status != 'terminée' AND status != 'annulée' THEN 1 ELSE 0 END) as overdue
            FROM tasks WHERE lawyer_id = ?");
        $stmt->execute([$lawyer_id]);
        $stats = $stmt->fetch();
    } else {
        // Tasks table doesn't exist, create empty arrays
        $tasks = [];
    }
    
    // Get cases for dropdown (MULTI-TENANT: Filtered by lawyer_id)
    $stmt = $conn->prepare("SELECT id, case_number FROM cases WHERE lawyer_id = ? ORDER BY case_number");
    $stmt->execute([$lawyer_id]);
    $cases = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
    $tasks = [];
    $cases = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="fas fa-tasks"></i> Gestion des Tâches
            </h1>
            <p class="page-subtitle">Suivez et gérez vos tâches</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
            <i class="fas fa-plus"></i> Nouvelle Tâche
        </button>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-number"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total Tâches</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-hourglass-start"></i></div>
            <div class="stat-number text-info"><?php echo $stats['todo']; ?></div>
            <div class="stat-label">À Faire</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-spinner"></i></div>
            <div class="stat-number text-warning"><?php echo $stats['in_progress']; ?></div>
            <div class="stat-label">En Cours</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-number text-success"><?php echo $stats['completed']; ?></div>
            <div class="stat-label">Terminées</div>
        </div>
    </div>
</div>

<?php if ($stats['overdue'] > 0): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle"></i> 
    <strong>Attention!</strong> Vous avez <?php echo $stats['overdue']; ?> tâche(s) en retard.
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <select class="form-select" name="status">
                    <option value="">Tous les statuts</option>
                    <option value="à_faire" <?php echo $filter_status == 'à_faire' ? 'selected' : ''; ?>>À faire</option>
                    <option value="en_cours" <?php echo $filter_status == 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                    <option value="terminée" <?php echo $filter_status == 'terminée' ? 'selected' : ''; ?>>Terminée</option>
                    <option value="annulée" <?php echo $filter_status == 'annulée' ? 'selected' : ''; ?>>Annulée</option>
                </select>
            </div>
            <div class="col-md-5">
                <select class="form-select" name="priority">
                    <option value="">Toutes les priorités</option>
                    <option value="urgente" <?php echo $filter_priority == 'urgente' ? 'selected' : ''; ?>>Urgente</option>
                    <option value="haute" <?php echo $filter_priority == 'haute' ? 'selected' : ''; ?>>Haute</option>
                    <option value="moyenne" <?php echo $filter_priority == 'moyenne' ? 'selected' : ''; ?>>Moyenne</option>
                    <option value="basse" <?php echo $filter_priority == 'basse' ? 'selected' : ''; ?>>Basse</option>
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

<!-- Tasks List -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-list"></i> Liste des Tâches
    </div>
    <div class="card-body">
        <?php if (!empty($tasks)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 30px;"></th>
                            <th>Titre</th>
                            <th>Dossier</th>
                            <th>Priorité</th>
                            <th>Statut</th>
                            <th>Échéance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <?php
                            $priority_colors = [
                                'urgente' => 'danger',
                                'haute' => 'warning',
                                'moyenne' => 'info',
                                'basse' => 'secondary'
                            ];
                            $status_colors = [
                                'à_faire' => 'secondary',
                                'en_cours' => 'warning',
                                'terminée' => 'success',
                                'annulée' => 'dark'
                            ];
                            
                            // Get colors with fallback
                            $priority_color = isset($priority_colors[$task['priority']]) ? $priority_colors[$task['priority']] : 'secondary';
                            $status_color = isset($status_colors[$task['status']]) ? $status_colors[$task['status']] : 'secondary';
                            
                            $is_overdue = $task['due_date'] < date('Y-m-d') && $task['status'] != 'terminée' && $task['status'] != 'annulée';
                            ?>
                            <tr class="<?php echo $is_overdue ? 'table-danger' : ''; ?>">
                                <td>
                                    <input type="checkbox" class="form-check-input" 
                                           <?php echo $task['status'] == 'terminée' ? 'checked' : ''; ?>
                                           onclick="toggleTaskStatus(<?php echo $task['id']; ?>, this.checked)">
                                </td>
                                <td>
                                    <strong class="<?php echo $task['status'] == 'terminée' ? 'text-decoration-line-through' : ''; ?>">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </strong>
                                    <?php if ($task['description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($task['description'], 0, 60)); ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $task['case_number'] ? htmlspecialchars($task['case_number']) : '-'; ?></td>
                                <td><span class="badge bg-<?php echo $priority_color; ?>"><?php echo ucfirst($task['priority']); ?></span></td>
                                <td><span class="badge bg-<?php echo $status_color; ?>"><?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?></span></td>
                                <td>
                                    <?php if ($task['due_date']): ?>
                                        <?php echo date('d/m/Y', strtotime($task['due_date'])); ?>
                                        <?php if ($is_overdue): ?>
                                            <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> En retard</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-info view-task" data-id="<?php echo $task['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning edit-task" data-id="<?php echo $task['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-task" data-id="<?php echo $task['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucune tâche trouvée</p>
                <?php if ($filter_status || $filter_priority): ?>
                    <a href="tasks.php" class="btn btn-sm btn-outline-primary">Réinitialiser les filtres</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- View Task Modal -->
<div class="modal fade" id="viewTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Détails de la Tâche</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="taskDetailsContent">
                <div class="text-center">
                    <div class="spinner-border text-gold" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Nouvelle Tâche</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTaskForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Titre *</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Priorité *</label>
                            <select class="form-select" name="priority" required>
                                <option value="moyenne">Moyenne</option>
                                <option value="basse">Basse</option>
                                <option value="haute">Haute</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Dossier lié</label>
                            <select class="form-select" name="case_id">
                                <option value="">Aucun</option>
                                <?php foreach ($cases as $case): ?>
                                    <option value="<?php echo $case['id']; ?>"><?php echo htmlspecialchars($case['case_number']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date d'échéance</label>
                            <input type="date" class="form-control" name="due_date">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Créer la tâche
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$page_scripts = "
<script src='" . APP_URL . "/assets/js/tasks.js'></script>
";

require_once __DIR__ . '/../includes/footer.php';
?>

