<?php
/**
 * Cabinet360 - Appointment CRUD Actions
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        add_appointment();
        break;
    case 'get':
        get_appointment();
        break;
    case 'get_all':
        get_all_appointments();
        break;
    case 'get_calendar':
        get_calendar_events();
        break;
    case 'update':
        update_appointment();
        break;
    case 'delete':
        delete_appointment();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
}

function add_appointment() {
    global $conn;
    
    $lawyer_id = $_SESSION['lawyer_id'] ?? 0; // Get current lawyer's ID
    $client_id = (int)($_POST['client_id'] ?? 0);
    $date = sanitize_input($_POST['date'] ?? '');
    $time = sanitize_input($_POST['time'] ?? '');
    $purpose = sanitize_input($_POST['purpose'] ?? '');
    $location = sanitize_input($_POST['location'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'planifie');
    
    if (!$lawyer_id || !$client_id || empty($date) || empty($time)) {
        echo json_encode(['success' => false, 'message' => 'Client, date et heure sont obligatoires']);
        return;
    }
    
    try {
        // First verify that the client belongs to the current lawyer
        $stmt = $conn->prepare("SELECT id FROM clients WHERE id = ? AND lawyer_id = ?");
        $stmt->execute([$client_id, $lawyer_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Client non autorisé']);
            return;
        }

        $stmt = $conn->prepare("INSERT INTO appointments (lawyer_id, client_id, date, time, purpose, location, status) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$lawyer_id, $client_id, $date, $time, $purpose, $location, $status]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Rendez-vous créé avec succès',
            'appointment_id' => $conn->lastInsertId()
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}

function get_appointment() {
    global $conn;
    
    $lawyer_id = $_SESSION['lawyer_id'] ?? 0;
    $appointment_id = $_GET['id'] ?? 0;
    
    try {
        $stmt = $conn->prepare("SELECT a.*, c.full_name as client_name FROM appointments a 
                                JOIN clients c ON a.client_id = c.id 
                                WHERE a.id = ? AND a.lawyer_id = ?");
        $stmt->execute([$appointment_id, $lawyer_id]);
        $appointment = $stmt->fetch();
        
        if ($appointment) {
            echo json_encode(['success' => true, 'appointment' => $appointment]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Rendez-vous non trouvé']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}

function get_all_appointments() {
    global $conn;
    
    $lawyer_id = $_SESSION['lawyer_id'] ?? 0;
    
    try {
        $stmt = $conn->prepare("SELECT a.*, c.full_name as client_name FROM appointments a 
                              JOIN clients c ON a.client_id = c.id 
                              WHERE a.lawyer_id = ?
                              ORDER BY a.date DESC, a.time DESC");
        $stmt->execute([$lawyer_id]);
        $appointments = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'appointments' => $appointments]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}

function get_calendar_events() {
    global $conn;
    
    $lawyer_id = $_SESSION['lawyer_id'] ?? 0;
    
    try {
        $stmt = $conn->prepare("SELECT a.id, a.date, a.time, a.purpose, a.status, c.full_name as client_name 
                              FROM appointments a 
                              JOIN clients c ON a.client_id = c.id 
                              WHERE a.lawyer_id = ?
                              ORDER BY a.date, a.time");
        $stmt->execute([$lawyer_id]);
        $appointments = $stmt->fetchAll();
        
        $events = [];
        foreach ($appointments as $apt) {
            $color = '#D4AF37'; // gold
            if ($apt['status'] == 'termine') {
                $color = '#28a745'; // green
            } elseif ($apt['status'] == 'annule') {
                $color = '#dc3545'; // red
            }
            
            $events[] = [
                'id' => $apt['id'],
                'title' => $apt['client_name'] . ' - ' . ($apt['purpose'] ?: 'RDV'),
                'start' => $apt['date'] . 'T' . $apt['time'],
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'status' => $apt['status']
                ]
            ];
        }
        
        echo json_encode($events);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}

function update_appointment() {
    global $conn;
    
    $lawyer_id = $_SESSION['lawyer_id'] ?? 0;
    $appointment_id = (int)($_POST['appointment_id'] ?? 0);
    $client_id = (int)($_POST['client_id'] ?? 0);
    $date = sanitize_input($_POST['date'] ?? '');
    $time = sanitize_input($_POST['time'] ?? '');
    $purpose = sanitize_input($_POST['purpose'] ?? '');
    $location = sanitize_input($_POST['location'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'planifie');
    
    if (!$client_id || empty($date) || empty($time)) {
        echo json_encode(['success' => false, 'message' => 'Client, date et heure sont obligatoires']);
        return;
    }
    
    try {
        // Verify ownership of the appointment and client
        $stmt = $conn->prepare("SELECT id FROM appointments WHERE id = ? AND lawyer_id = ?");
        $stmt->execute([$appointment_id, $lawyer_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Rendez-vous non autorisé']);
            return;
        }

        $stmt = $conn->prepare("SELECT id FROM clients WHERE id = ? AND lawyer_id = ?");
        $stmt->execute([$client_id, $lawyer_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Client non autorisé']);
            return;
        }

        $stmt = $conn->prepare("UPDATE appointments SET client_id = ?, date = ?, time = ?, purpose = ?, location = ?, status = ? 
                                WHERE id = ? AND lawyer_id = ?");
        $stmt->execute([$client_id, $date, $time, $purpose, $location, $status, $appointment_id, $lawyer_id]);
        
        echo json_encode(['success' => true, 'message' => 'Rendez-vous mis à jour avec succès']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}

function delete_appointment() {
    global $conn;
    
    $lawyer_id = $_SESSION['lawyer_id'] ?? 0;
    $appointment_id = $_POST['appointment_id'] ?? 0;
    
    try {
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ? AND lawyer_id = ?");
        $stmt->execute([$appointment_id, $lawyer_id]);
        
        echo json_encode(['success' => true, 'message' => 'Rendez-vous supprimé avec succès']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}
?>

