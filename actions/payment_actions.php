<?php
/**
 * Cabinet360 - Payment CRUD Actions
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
        add_payment();
        break;
    case 'get':
        get_payment();
        break;
    case 'update':
        update_payment();
        break;
    case 'delete':
        delete_payment();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
}

function add_payment() {
    global $conn, $current_lawyer_id;
    
    $client_id = (int)($_POST['client_id'] ?? 0);
    $case_id = !empty($_POST['case_id']) ? (int)$_POST['case_id'] : null;
    $date = sanitize_input($_POST['date'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $method = sanitize_input($_POST['method'] ?? '');
    $status = sanitize_input($_POST['status'] ?? '');
    $notes = sanitize_input($_POST['notes'] ?? '');
    
    if (!$client_id || empty($date) || $amount <= 0 || empty($method) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis']);
        return;
    }
    
    try {
        // Multi-tenant: verify client belongs to current lawyer
        $stmt = $conn->prepare("SELECT id FROM clients WHERE id = ? AND lawyer_id = ?");
        $stmt->execute([$client_id, $current_lawyer_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Client non trouvé']);
            return;
        }
        
        // Multi-tenant: verify case belongs to current lawyer (if provided)
        if ($case_id) {
            $stmt = $conn->prepare("SELECT id FROM cases WHERE id = ? AND lawyer_id = ?");
            $stmt->execute([$case_id, $current_lawyer_id]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Dossier non trouvé']);
                return;
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO payments (lawyer_id, client_id, case_id, date, amount, method, status, notes) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$current_lawyer_id, $client_id, $case_id, $date, $amount, $method, $status, $notes]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Paiement enregistré avec succès',
            'payment_id' => $conn->lastInsertId()
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}

function get_payment() {
    global $conn, $current_lawyer_id;
    
    $payment_id = $_GET['id'] ?? 0;
    
    try {
        // Multi-tenant: verify payment belongs to current lawyer
        $stmt = $conn->prepare("SELECT p.*, c.full_name as client_name, cs.case_number 
                                FROM payments p 
                                JOIN clients c ON p.client_id = c.id 
                                LEFT JOIN cases cs ON p.case_id = cs.id 
                                WHERE p.id = ? AND p.lawyer_id = ?");
        $stmt->execute([$payment_id, $current_lawyer_id]);
        $payment = $stmt->fetch();
        
        if ($payment) {
            echo json_encode(['success' => true, 'payment' => $payment]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Paiement non trouvé']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}

function update_payment() {
    global $conn, $current_lawyer_id;
    
    $payment_id = (int)($_POST['payment_id'] ?? 0);
    $client_id = (int)($_POST['client_id'] ?? 0);
    $case_id = !empty($_POST['case_id']) ? (int)$_POST['case_id'] : null;
    $date = sanitize_input($_POST['date'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $method = sanitize_input($_POST['method'] ?? '');
    $status = sanitize_input($_POST['status'] ?? '');
    $notes = sanitize_input($_POST['notes'] ?? '');
    
    if (!$client_id || empty($date) || $amount <= 0 || empty($method) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis']);
        return;
    }
    
    try {
        // Multi-tenant: verify payment belongs to current lawyer
        $stmt = $conn->prepare("SELECT id FROM payments WHERE id = ? AND lawyer_id = ?");
        $stmt->execute([$payment_id, $current_lawyer_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Paiement non trouvé']);
            return;
        }
        
        // Multi-tenant: verify client belongs to current lawyer
        $stmt = $conn->prepare("SELECT id FROM clients WHERE id = ? AND lawyer_id = ?");
        $stmt->execute([$client_id, $current_lawyer_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Client non trouvé']);
            return;
        }
        
        // Multi-tenant: verify case belongs to current lawyer (if provided)
        if ($case_id) {
            $stmt = $conn->prepare("SELECT id FROM cases WHERE id = ? AND lawyer_id = ?");
            $stmt->execute([$case_id, $current_lawyer_id]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Dossier non trouvé']);
                return;
            }
        }
        
        $stmt = $conn->prepare("UPDATE payments SET client_id = ?, case_id = ?, date = ?, amount = ?, 
                                method = ?, status = ?, notes = ? WHERE id = ? AND lawyer_id = ?");
        $stmt->execute([$client_id, $case_id, $date, $amount, $method, $status, $notes, $payment_id, $current_lawyer_id]);
        
        echo json_encode(['success' => true, 'message' => 'Paiement mis à jour avec succès']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}

function delete_payment() {
    global $conn, $current_lawyer_id;
    
    $payment_id = $_POST['payment_id'] ?? 0;
    
    try {
        // Multi-tenant: only delete if payment belongs to current lawyer
        $stmt = $conn->prepare("DELETE FROM payments WHERE id = ? AND lawyer_id = ?");
        $stmt->execute([$payment_id, $current_lawyer_id]);
        
        echo json_encode(['success' => true, 'message' => 'Paiement supprimé avec succès']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}
?>

