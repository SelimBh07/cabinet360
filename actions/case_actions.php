<?php
/**
 * Cabinet360 - Case CRUD Actions
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
        add_case();
        break;
    case 'get':
        get_case();
        break;
    case 'update':
        update_case();
        break;
    case 'delete':
        delete_case();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
}

function add_case() {
    global $conn;
    
    $case_number = sanitize_input($_POST['case_number'] ?? '');
    $client_id = (int)($_POST['client_id'] ?? 0);
    $description = sanitize_input($_POST['description'] ?? '');
    $type = sanitize_input($_POST['type'] ?? '');
    $status = sanitize_input($_POST['status'] ?? '');
    $lawyer = sanitize_input($_POST['lawyer'] ?? '');
    $date_opened = sanitize_input($_POST['date_opened'] ?? '');
    $notes = sanitize_input($_POST['notes'] ?? '');
    
    if (empty($case_number) || !$client_id || empty($type) || empty($status) || empty($date_opened)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis']);
        return;
    }
    
    // Handle file upload
    $document_path = '';
    if (isset($_FILES['document']) && $_FILES['document']['error'] == UPLOAD_ERR_OK) {
        $upload_result = handle_file_upload($_FILES['document']);
        if ($upload_result['success']) {
            $document_path = $upload_result['path'];
        } else {
            echo json_encode($upload_result);
            return;
        }
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO cases (client_id, case_number, description, type, status, lawyer, date_opened, notes, document_path) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$client_id, $case_number, $description, $type, $status, $lawyer, $date_opened, $notes, $document_path]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Dossier créé avec succès',
            'case_id' => $conn->lastInsertId()
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'Un dossier avec ce numéro existe déjà']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
        }
    }
}

function get_case() {
    global $conn;
    
    $case_id = $_GET['id'] ?? 0;
    
    try {
        $stmt = $conn->prepare("SELECT cs.*, cl.full_name as client_name FROM cases cs 
                                JOIN clients cl ON cs.client_id = cl.id 
                                WHERE cs.id = ?");
        $stmt->execute([$case_id]);
        $case = $stmt->fetch();
        
        if ($case) {
            echo json_encode(['success' => true, 'case' => $case]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Dossier non trouvé']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}

function update_case() {
    global $conn;
    
    $case_id = (int)($_POST['case_id'] ?? 0);
    $case_number = sanitize_input($_POST['case_number'] ?? '');
    $client_id = (int)($_POST['client_id'] ?? 0);
    $description = sanitize_input($_POST['description'] ?? '');
    $type = sanitize_input($_POST['type'] ?? '');
    $status = sanitize_input($_POST['status'] ?? '');
    $lawyer = sanitize_input($_POST['lawyer'] ?? '');
    $date_opened = sanitize_input($_POST['date_opened'] ?? '');
    $notes = sanitize_input($_POST['notes'] ?? '');
    
    if (empty($case_number) || !$client_id || empty($type) || empty($status) || empty($date_opened)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis']);
        return;
    }
    
    // Handle file upload if new file provided
    $document_path = null;
    if (isset($_FILES['document']) && $_FILES['document']['error'] == UPLOAD_ERR_OK) {
        $upload_result = handle_file_upload($_FILES['document']);
        if ($upload_result['success']) {
            $document_path = $upload_result['path'];
            
            // Delete old file
            try {
                $stmt = $conn->prepare("SELECT document_path FROM cases WHERE id = ?");
                $stmt->execute([$case_id]);
                $old_doc = $stmt->fetch();
                if ($old_doc && $old_doc['document_path'] && file_exists(__DIR__ . '/../' . $old_doc['document_path'])) {
                    unlink(__DIR__ . '/../' . $old_doc['document_path']);
                }
            } catch (Exception $e) {
                // Ignore deletion errors
            }
        } else {
            echo json_encode($upload_result);
            return;
        }
    }
    
    try {
        if ($document_path) {
            $stmt = $conn->prepare("UPDATE cases SET client_id = ?, case_number = ?, description = ?, type = ?, status = ?, 
                                    lawyer = ?, date_opened = ?, notes = ?, document_path = ? WHERE id = ?");
            $stmt->execute([$client_id, $case_number, $description, $type, $status, $lawyer, $date_opened, $notes, $document_path, $case_id]);
        } else {
            $stmt = $conn->prepare("UPDATE cases SET client_id = ?, case_number = ?, description = ?, type = ?, status = ?, 
                                    lawyer = ?, date_opened = ?, notes = ? WHERE id = ?");
            $stmt->execute([$client_id, $case_number, $description, $type, $status, $lawyer, $date_opened, $notes, $case_id]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Dossier mis à jour avec succès']);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'Un dossier avec ce numéro existe déjà']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
        }
    }
}

function delete_case() {
    global $conn;
    
    $case_id = $_POST['case_id'] ?? 0;
    
    try {
        // Get document path to delete file
        $stmt = $conn->prepare("SELECT document_path FROM cases WHERE id = ?");
        $stmt->execute([$case_id]);
        $case = $stmt->fetch();
        
        // Delete case from database
        $stmt = $conn->prepare("DELETE FROM cases WHERE id = ?");
        $stmt->execute([$case_id]);
        
        // Delete associated file if exists
        if ($case && $case['document_path'] && file_exists(__DIR__ . '/../' . $case['document_path'])) {
            unlink(__DIR__ . '/../' . $case['document_path']);
        }
        
        echo json_encode(['success' => true, 'message' => 'Dossier supprimé avec succès']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}

function handle_file_upload($file) {
    $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $allowed_extensions = ['pdf', 'doc', 'docx'];
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'Le fichier est trop volumineux (max 5MB)'];
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['success' => false, 'message' => 'Type de fichier non autorisé'];
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'path' => 'uploads/' . $filename];
    } else {
        return ['success' => false, 'message' => 'Erreur lors du téléchargement du fichier'];
    }
}
?>

