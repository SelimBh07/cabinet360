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
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="fas fa-calendar-alt"></i> <?php echo t('appointments'); ?>
            </h1>
            <p class="page-subtitle"><?php echo $upcoming_count ?? 0; ?> <?php echo t('upcoming_appointments'); ?></p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
            <i class="fas fa-plus"></i> <?php echo t('new_appointment'); ?>
        </button>
    </div>
</div>

<!-- View Toggle -->
<div class="card mb-4">
    <div class="card-body">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary active" id="btnCalendarView">
                <i class="fas fa-calendar"></i> <?php echo t('calendar_view'); ?>
            </button>
            <button type="button" class="btn btn-outline-primary" id="btnListView">
                <i class="fas fa-list"></i> <?php echo t('list_view'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Calendar View -->
<div class="card" id="calendarView">
    <div class="card-header">
        <i class="fas fa-calendar-alt"></i> <?php echo t('appointments_calendar'); ?>
    </div>
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<!-- List View -->
<div class="card" id="listView" style="display: none;">
    <div class="card-header">
        <i class="fas fa-list"></i> <?php echo t('appointments_list'); ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="appointmentsTable">
                <thead>
                    <tr>
                        <th><?php echo t('date'); ?></th>
                        <th><?php echo t('time'); ?></th>
                        <th><?php echo t('client'); ?></th>
                        <th><?php echo t('purpose'); ?></th>
                        <th><?php echo t('location'); ?></th>
                        <th><?php echo t('status'); ?></th>
                        <th><?php echo t('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loaded via AJAX (should be filtered by lawyer_id in backend) -->
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
                <h5 class="modal-title"><i class="fas fa-calendar-plus"></i> <?php echo t('new_appointment'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAppointmentForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('client'); ?> *</label>
                        <select class="form-select" name="client_id" required>
                            <option value=""><?php echo t('select_client'); ?></option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('date'); ?> *</label>
                        <input type="date" class="form-control" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('time'); ?> *</label>
                        <input type="time" class="form-control" name="time" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('purpose'); ?></label>
                        <input type="text" class="form-control" name="purpose" placeholder="Ex: Consultation initiale">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('location'); ?></label>
                        <input type="text" class="form-control" name="location" placeholder="Ex: Bureau principal">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('status'); ?></label>
                        <select class="form-select" name="status">
                            <option value="planifie" selected><?php echo t('planned'); ?></option>
                            <option value="termine"><?php echo t('completed'); ?></option>
                            <option value="annule"><?php echo t('cancelled'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo t('save'); ?>
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
                <h5 class="modal-title"><i class="fas fa-edit"></i> <?php echo t('edit_appointment'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAppointmentForm">
                <input type="hidden" name="appointment_id" id="edit_appointment_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('client'); ?> *</label>
                        <select class="form-select" name="client_id" id="edit_client_id" required>
                            <option value=""><?php echo t('select_client'); ?></option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('date'); ?> *</label>
                        <input type="date" class="form-control" name="date" id="edit_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('time'); ?> *</label>
                        <input type="time" class="form-control" name="time" id="edit_time" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('purpose'); ?></label>
                        <input type="text" class="form-control" name="purpose" id="edit_purpose">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('location'); ?></label>
                        <input type="text" class="form-control" name="location" id="edit_location">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('status'); ?></label>
                        <select class="form-select" name="status" id="edit_status">
                            <option value="planifie"><?php echo t('planned'); ?></option>
                            <option value="termine"><?php echo t('completed'); ?></option>
                            <option value="annule"><?php echo t('cancelled'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo t('update'); ?>
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
                <h5 class="modal-title"><i class="fas fa-calendar-day"></i> <?php echo t('appointment_details'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="appointmentDetails">
                <div class="text-center">
                    <div class="spinner-border text-gold" role="status">
                        <span class="visually-hidden"><?php echo t('loading'); ?></span>
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

