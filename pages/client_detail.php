<?php
/**
 * Cabinet360 - Client Detail Page
 */

require_once __DIR__ . '/../config/auth.php';


$client_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize variables to avoid undefined warnings
$total_cases = 0;
$active_cases = 0;
$total_appointments = 0;
$upcoming_appointments = 0;
$total_paid = 0;
$total_unpaid = 0;
$client_notes = [];
$cases = [];
$appointments = [];
$payments = [];

if (!$client_id) {
    header('Location: clients.php');
    exit();
}

try {
    // Get client information
    $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch();
    
    if (!$client) {
        header('Location: clients.php');
        exit();
    }
    
    // Get client's cases
    $stmt = $conn->prepare("SELECT * FROM cases WHERE client_id = ? ORDER BY date_opened DESC");
    $stmt->execute([$client_id]);
    $cases = $stmt->fetchAll();
    
    // Get client's appointments
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE client_id = ? ORDER BY date DESC, time DESC");
    $stmt->execute([$client_id]);
    $appointments = $stmt->fetchAll();
    
    // Get client's payments
    $stmt = $conn->prepare("SELECT p.*, c.case_number FROM payments p 
                           LEFT JOIN cases c ON p.case_id = c.id 
                           WHERE p.client_id = ? 
                           ORDER BY p.date DESC");
    $stmt->execute([$client_id]);
    $payments = $stmt->fetchAll();
    
    // Get client's notes/comments
    $stmt = $conn->prepare("SELECT n.*, u.full_name as author FROM notes n 
                           JOIN users u ON n.user_id = u.id 
                           WHERE n.entity_type = 'client' AND n.entity_id = ? 
                           ORDER BY n.created_at DESC");
    $stmt->execute([$client_id]);
    $client_notes = $stmt->fetchAll();
    
    // Calculate statistics
    $total_cases = count($cases);
    $active_cases = count(array_filter($cases, function($c) { return in_array($c['status'], ['ouvert', 'en_cours']); }));
    $total_appointments = count($appointments);
    $upcoming_appointments = count(array_filter($appointments, function($a) { return $a['date'] >= date('Y-m-d') && $a['status'] == 'planifie'; }));
    
    $total_paid = 0;
    $total_unpaid = 0;
    foreach ($payments as $payment) {
        if ($payment['status'] == 'payé') {
            $total_paid += $payment['amount'];
        } else {
            $total_unpaid += $payment['amount'];
        }
    }
    
} catch (PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}

$page_title = 'Détails Client - ' . $client['full_name'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="fas fa-user"></i> <?php echo htmlspecialchars($client['full_name']); ?>
            </h1>
            <p class="page-subtitle">Informations complètes du client</p>
        </div>
        <div>
            <a href="clients.php" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button class="btn btn-warning edit-client" data-id="<?php echo $client['id']; ?>">
                <i class="fas fa-edit"></i> Modifier
            </button>
        </div>
    </div>
</div>

<!-- Client Information Cards -->
<div class="row mb-4">
    <!-- Personal Information -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-id-card"></i> Informations Personnelles
            </div>
            <div class="card-body">
                <div class="info-row">
                    <strong><i class="fas fa-user text-gold"></i> Nom Complet:</strong>
                    <span><?php echo htmlspecialchars($client['full_name']); ?></span>
                </div>
                <div class="info-row">
                    <strong><i class="fas fa-id-badge text-gold"></i> CIN:</strong>
                    <span><?php echo htmlspecialchars($client['cin']); ?></span>
                </div>
                <div class="info-row">
                    <strong><i class="fas fa-phone text-gold"></i> Téléphone:</strong>
                    <span><?php echo htmlspecialchars($client['phone'] ?: 'Non renseigné'); ?></span>
                </div>
                <div class="info-row">
                    <strong><i class="fas fa-envelope text-gold"></i> Email:</strong>
                    <span><?php echo htmlspecialchars($client['email'] ?: 'Non renseigné'); ?></span>
                </div>
                <div class="info-row">
                    <strong><i class="fas fa-map-marker-alt text-gold"></i> Adresse:</strong>
                    <span><?php echo nl2br(htmlspecialchars($client['address'] ?: 'Non renseignée')); ?></span>
                </div>
                <div class="info-row">
                    <strong><i class="fas fa-calendar text-gold"></i> Membre depuis:</strong>
                    <span><?php echo date('d/m/Y', strtotime($client['created_at'])); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-bar"></i> Statistiques
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="stat-box">
                            <i class="fas fa-briefcase fa-2x text-gold mb-2"></i>
                            <h3 class="text-gold mb-0"><?php echo $total_cases; ?></h3>
                            <small class="text-muted">Total Dossiers</small>
                            <div class="mt-1">
                                <span class="badge bg-success"><?php echo $active_cases; ?> Actifs</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-box">
                            <i class="fas fa-calendar-check fa-2x text-gold mb-2"></i>
                            <h3 class="text-gold mb-0"><?php echo $total_appointments; ?></h3>
                            <small class="text-muted">Total RDV</small>
                            <div class="mt-1">
                                <span class="badge bg-info"><?php echo $upcoming_appointments; ?> À venir</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box">
                            <i class="fas fa-money-bill-wave fa-2x text-gold mb-2"></i>
                            <h3 class="text-gold mb-0"><?php echo number_format($total_paid, 2); ?> MAD</h3>
                            <small class="text-muted">Total Payé</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box">
                            <i class="fas fa-exclamation-triangle fa-2x text-gold mb-2"></i>
                            <h3 class="text-gold mb-0"><?php echo number_format($total_unpaid, 2); ?> MAD</h3>
                            <small class="text-muted">Impayé</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notes & Comments Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-comments"></i> Notes & Commentaires (<?php echo count($client_notes); ?>)</span>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                    <i class="fas fa-plus"></i> Ajouter une note
                </button>
            </div>
            <div class="card-body">
                <?php if ($client['notes']): ?>
                    <div class="alert alert-info">
                        <strong><i class="fas fa-sticky-note"></i> Note principale:</strong><br>
                        <?php echo nl2br(htmlspecialchars($client['notes'])); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($client_notes)): ?>
                    <div class="notes-timeline">
                        <?php foreach ($client_notes as $note): ?>
                            <div class="note-item <?php echo $note['is_important'] ? 'note-important' : ''; ?>">
                                <div class="note-header">
                                    <div>
                                        <strong><?php echo htmlspecialchars($note['author']); ?></strong>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($note['created_at'])); ?>
                                        </small>
                                        <?php if ($note['is_important']): ?>
                                            <span class="badge bg-danger">Important</span>
                                        <?php endif; ?>
                                    </div>
                                    <button class="btn btn-sm btn-danger delete-note" data-id="<?php echo $note['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="note-content">
                                    <?php echo nl2br(htmlspecialchars($note['content'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">Aucune note ajoutée</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.notes-timeline {
    max-height: 500px;
    overflow-y: auto;
}

.note-item {
    background: var(--dark-bg);
    border-left: 3px solid var(--gold);
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.note-item:hover {
    background: rgba(212, 175, 55, 0.05);
    transform: translateX(3px);
}

.note-important {
    border-left-color: #dc3545;
    background: rgba(220, 53, 69, 0.1);
}

.note-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.note-content {
    color: var(--text-white);
    line-height: 1.6;
}
</style>

<!-- Cases Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-briefcase"></i> Dossiers (<?php echo $total_cases; ?>)</span>
                <a href="cases.php?client_id=<?php echo $client['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Nouveau Dossier
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($cases)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>N° Dossier</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Avocat</th>
                                    <th>Statut</th>
                                    <th>Date d'ouverture</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cases as $case): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($case['case_number']); ?></strong></td>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars(ucfirst($case['type'])); ?></span></td>
                                        <td>
                                            <?php 
                                            if (!empty($case['description'])) {
                                                echo htmlspecialchars(substr($case['description'], 0, 50)) . (strlen($case['description']) > 50 ? '...' : '');
                                            } else {
                                                echo '<em class="text-muted">Aucune description</em>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($case['lawyer'] ?: 'Non assigné'); ?></td>
                                        <td>
                                            <?php
                                            $status_class = $case['status'] == 'clos' ? 'bg-secondary' : 
                                                          ($case['status'] == 'en_cours' ? 'bg-success' : 'bg-warning');
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars(ucfirst($case['status'])); ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($case['date_opened'])); ?></td>
                                        <td>
                                            <a href="cases.php" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">Aucun dossier pour ce client</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Appointments Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar-alt"></i> Rendez-vous (<?php echo $total_appointments; ?>)</span>
                <a href="appointments.php?client_id=<?php echo $client['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Nouveau Rendez-vous
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($appointments)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Objet</th>
                                    <th>Lieu</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($appointments, 0, 5) as $apt): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($apt['date'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($apt['time'])); ?></td>
                                        <td><?php echo htmlspecialchars($apt['purpose']); ?></td>
                                        <td><?php echo htmlspecialchars($apt['location'] ?: 'Non spécifié'); ?></td>
                                        <td>
                                            <?php
                                            $status_colors = [
                                                'planifie' => 'bg-info',
                                                'confirmé' => 'bg-success',
                                                'annulé' => 'bg-danger',
                                                'terminé' => 'bg-secondary'
                                            ];
                                            $status_class = $status_colors[$apt['status']] ?? 'bg-secondary';
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars(ucfirst($apt['status'])); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($appointments) > 5): ?>
                        <div class="text-center mt-3">
                            <a href="appointments.php?client_id=<?php echo $client['id']; ?>" class="btn btn-sm btn-outline-primary">
                                Voir tous les rendez-vous
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">Aucun rendez-vous pour ce client</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Payments Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-dollar-sign"></i> Paiements (<?php echo count($payments); ?>)</span>
                <a href="payments.php?client_id=<?php echo $client['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Nouveau Paiement
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($payments)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Dossier</th>
                                    <th>Montant</th>
                                    <th>Méthode</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($payments, 0, 5) as $payment): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($payment['date'])); ?></td>
                                        <td><?php echo htmlspecialchars($payment['case_number'] ?: 'N/A'); ?></td>
                                        <td><strong class="text-gold"><?php echo number_format($payment['amount'], 2); ?> MAD</strong></td>
                                        <td><?php echo htmlspecialchars(ucfirst($payment['method'])); ?></td>
                                        <td>
                                            <?php
                                            $status_colors = [
                                                'payé' => 'bg-success',
                                                'impayé' => 'bg-danger',
                                                'partiel' => 'bg-warning'
                                            ];
                                            $status_class = $status_colors[$payment['status']] ?? 'bg-secondary';
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars(ucfirst($payment['status'])); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($payments) > 5): ?>
                        <div class="text-center mt-3">
                            <a href="payments.php?client_id=<?php echo $client['id']; ?>" class="btn btn-sm btn-outline-primary">
                                Voir tous les paiements
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">Aucun paiement pour ce client</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Ajouter une Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNoteForm">
                <input type="hidden" name="entity_type" value="client">
                <input type="hidden" name="entity_id" value="<?php echo $client['id']; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Note / Commentaire *</label>
                        <textarea class="form-control" name="content" rows="4" required></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_important" id="isImportant">
                        <label class="form-check-label" for="isImportant">
                            Marquer comme important
                        </label>
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
                <input type="hidden" name="client_id" id="edit_client_id" value="<?php echo $client['id']; ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom Complet *</label>
                            <input type="text" class="form-control" name="full_name" id="edit_full_name" value="<?php echo htmlspecialchars($client['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CIN *</label>
                            <input type="text" class="form-control" name="cin" id="edit_cin" value="<?php echo htmlspecialchars($client['cin']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" class="form-control" name="phone" id="edit_phone" value="<?php echo htmlspecialchars($client['phone']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="edit_email" value="<?php echo htmlspecialchars($client['email']); ?>">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Adresse</label>
                            <textarea class="form-control" name="address" id="edit_address" rows="2"><?php echo htmlspecialchars($client['address']); ?></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="edit_notes" rows="3"><?php echo htmlspecialchars($client['notes']); ?></textarea>
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

<?php
$page_scripts = "
<script>
$(document).ready(function() {
    // Update Client Form
    $('#editClientForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '../actions/client_actions.php',
            method: 'POST',
            data: $(this).serialize() + '&action=update',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Erreur lors de la mise à jour');
            }
        });
    });
    
    // Edit button handler
    $('.edit-client').on('click', function() {
        $('#editClientModal').modal('show');
    });
    
    // Add Note Form
    $('#addNoteForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '../actions/note_actions.php',
            method: 'POST',
            data: $(this).serialize() + '&action=add',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Erreur lors de l\\'ajout de la note');
            }
        });
    });
    
    // Delete Note
    $('.delete-note').on('click', function() {
        const noteId = $(this).data('id');
        
        if (confirm('Supprimer cette note?')) {
            $.ajax({
                url: '../actions/note_actions.php',
                method: 'POST',
                data: { action: 'delete', note_id: noteId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('Erreur lors de la suppression');
                }
            });
        }
    });
});
</script>
";

require_once __DIR__ . '/../includes/footer.php';
?>

