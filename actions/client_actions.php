<?php
/**
 * Cabinet360 - Client CRUD Actions
 */

require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Get current lawyer_id for multi-tenancy
$current_lawyer_id = $lawyer_id;

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        add_client();
        break;
    case 'get':
        get_client();
        break;
    case 'update':
        update_client();
        break;
    case 'delete':
        delete_client();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
}

function add_client() {
    global $conn, $current_lawyer_id;
    
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $cin = sanitize_input($_POST['cin'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $notes = sanitize_input($_POST['notes'] ?? '');
    
    if (empty($full_name) || empty($cin)) {
        echo json_encode(['success' => false, 'message' => 'Nom et CIN sont obligatoires']);
        return;
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO clients (lawyer_id, full_name, cin, phone, email, address, notes) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$current_lawyer_id, $full_name, $cin, $phone, $email, $address, $notes]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Client ajouté avec succès',
            'client_id' => $conn->lastInsertId()
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'Un client avec ce CIN existe déjà']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
        }
    }
}

function get_client() {
    global $conn, $current_lawyer_id;
    
    $client_id = $_GET['id'] ?? 0;
    
    try {
        // Multi-tenant: verify client belongs to current lawyer
        $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ? AND lawyer_id = ?");
        $stmt->execute([$client_id, $current_lawyer_id]);
        $client = $stmt->fetch();
        
        if ($client) {
            // Get related cases
            $stmt = $conn->prepare("SELECT * FROM cases WHERE client_id = ? AND lawyer_id = ?");
            $stmt->execute([$client_id, $current_lawyer_id]);
            $cases = $stmt->fetchAll();
            
            // Get related appointments
            $stmt = $conn->prepare("SELECT * FROM appointments WHERE client_id = ? AND lawyer_id = ? ORDER BY date DESC");
            $stmt->execute([$client_id, $current_lawyer_id]);
            $appointments = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'client' => $client,
                'cases' => $cases,
                'appointments' => $appointments
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Client non trouvé']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}

function update_client() {
    global $conn, $current_lawyer_id;
    
    $client_id = $_POST['client_id'] ?? 0;
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $cin = sanitize_input($_POST['cin'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $notes = sanitize_input($_POST['notes'] ?? '');
    
    if (empty($full_name) || empty($cin)) {
        echo json_encode(['success' => false, 'message' => 'Nom et CIN sont obligatoires']);
        return;
    }
    
    try {
        // Multi-tenant: only update if client belongs to current lawyer
        $stmt = $conn->prepare("UPDATE clients SET full_name = ?, cin = ?, phone = ?, email = ?, address = ?, notes = ? 
                                WHERE id = ? AND lawyer_id = ?");
        $stmt->execute([$full_name, $cin, $phone, $email, $address, $notes, $client_id, $current_lawyer_id]);
        
        echo json_encode(['success' => true, 'message' => 'Client mis à jour avec succès']);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'Un client avec ce CIN existe déjà']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
        }
    }
}

function delete_client() {
    global $conn, $current_lawyer_id;
    
    $client_id = $_POST['client_id'] ?? 0;
    
    try {
        // Multi-tenant: only delete if client belongs to current lawyer
        $stmt = $conn->prepare("DELETE FROM clients WHERE id = ? AND lawyer_id = ?");
        $stmt->execute([$client_id, $current_lawyer_id]);
        
        echo json_encode(['success' => true, 'message' => 'Client supprimé avec succès']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}
?>

