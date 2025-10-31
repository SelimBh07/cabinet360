<?php
/**
 * Cabinet360 - Note Actions
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$action = sanitize_input($_POST['action'] ?? '');

try {
    if ($action == 'add') {
        $entity_type = sanitize_input($_POST['entity_type']);
        $entity_id = (int)$_POST['entity_id'];
        $content = sanitize_input($_POST['content']);
        $is_important = isset($_POST['is_important']) ? 1 : 0;
        $user_id = $_SESSION['user_id'];
        
        $stmt = $conn->prepare("INSERT INTO notes (user_id, entity_type, entity_id, content, is_important) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $entity_type, $entity_id, $content, $is_important]);
        
        echo json_encode(['success' => true, 'message' => 'Note ajoutée avec succès']);
        
    } elseif ($action == 'delete') {
        $note_id = (int)$_POST['note_id'];
        
        $stmt = $conn->prepare("DELETE FROM notes WHERE id = ?");
        $stmt->execute([$note_id]);
        
        echo json_encode(['success' => true, 'message' => 'Note supprimée']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
?>
















