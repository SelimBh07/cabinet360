<?php
/**
 * Cabinet360 - Appointments Management
 */

require_once __DIR__ . '/../config/auth.php';

$page_title = 'Gestion des Rendez-vous';

try {
    // Get clients for dropdown
    $stmt = $conn->query("SELECT id, full_name FROM clients ORDER BY full_name");
    $clients = $stmt->fetchAll();
    
    // Get upcoming appointments count
    $stmt = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE date >= CURDATE() AND status = 'planifie'");
    $upcoming_count = $stmt->fetch()['total'];
    
} catch (PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="fas fa-calendar-alt"></i> Gestion des Rendez-vous
            </h1>
            <p class="page-subtitle"><?php echo $upcoming_count ?? 0; ?> rendez-vous à venir</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
            <i class="fas fa-plus"></i> Nouveau Rendez-vous
        </button>
    </div>
</div>

<!-- View Toggle -->
<div class="card mb-4">
    <div class="card-body">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary active" id="btnCalendarView">
                <i class="fas fa-calendar"></i> Vue Calendrier
            </button>
            <button type="button" class="btn btn-outline-primary" id="btnListView">
                <i class="fas fa-list"></i> Vue Liste
            </button>
        </div>
    </div>
</div>

<!-- Calendar View -->
<div class="card" id="calendarView">
    <div class="card-header">
        <i class="fas fa-calendar-alt"></i> Calendrier des Rendez-vous
    </div>
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<!-- List View -->
<div class="card" id="listView" style="display: none;">
    <div class="card-header">
        <i class="fas fa-list"></i> Liste des Rendez-vous
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="appointmentsTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Client</th>
                        <th>Objet</th>
                        <th>Lieu</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Appointment Modal -->
<div class="modal fade" id="addAppointmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar-plus"></i> Nouveau Rendez-vous</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAppointmentForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Client *</label>
                        <select class="form-select" name="client_id" required>
                            <option value="">Sélectionner un client</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date *</label>
                        <input type="date" class="form-control" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Heure *</label>
                        <input type="time" class="form-control" name="time" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Objet</label>
                        <input type="text" class="form-control" name="purpose" placeholder="Ex: Consultation initiale">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lieu</label>
                        <input type="text" class="form-control" name="location" placeholder="Ex: Bureau principal">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Statut</label>
                        <select class="form-select" name="status">
                            <option value="planifie" selected>Planifié</option>
                            <option value="termine">Terminé</option>
                            <option value="annule">Annulé</option>
                        </select>
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

<!-- Edit Appointment Modal -->
<div class="modal fade" id="editAppointmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier Rendez-vous</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAppointmentForm">
                <input type="hidden" name="appointment_id" id="edit_appointment_id">
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
                        <label class="form-label">Date *</label>
                        <input type="date" class="form-control" name="date" id="edit_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Heure *</label>
                        <input type="time" class="form-control" name="time" id="edit_time" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Objet</label>
                        <input type="text" class="form-control" name="purpose" id="edit_purpose">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lieu</label>
                        <input type="text" class="form-control" name="location" id="edit_location">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Statut</label>
                        <select class="form-select" name="status" id="edit_status">
                            <option value="planifie">Planifié</option>
                            <option value="termine">Terminé</option>
                            <option value="annule">Annulé</option>
                        </select>
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

<!-- View Appointment Modal -->
<div class="modal fade" id="viewAppointmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar-day"></i> Détails du Rendez-vous</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="appointmentDetails">
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
<script src='" . APP_URL . "/assets/js/appointments.js'></script>
";

require_once __DIR__ . '/../includes/footer.php';
?>

