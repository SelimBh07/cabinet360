<?php
/**
 * Cabinet360 - Task Actions
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$action = sanitize_input($_POST['action'] ?? $_GET['action'] ?? '');

try {
    if ($action == 'get') {
        // Get single task details
        $task_id = (int)$_GET['id'];
        
        $stmt = $conn->prepare("SELECT t.*, c.case_number, u.full_name as assigned_name 
                               FROM tasks t 
                               LEFT JOIN cases c ON t.case_id = c.id 
                               LEFT JOIN users u ON t.assigned_to = u.id 
                               WHERE t.id = ?");
        $stmt->execute([$task_id]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($task) {
            echo json_encode(['success' => true, 'task' => $task]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tâche introuvable']);
        }
        
    } elseif ($action == 'add') {
        $title = sanitize_input($_POST['title']);
        $description = sanitize_input($_POST['description'] ?? '');
        $case_id = !empty($_POST['case_id']) ? (int)$_POST['case_id'] : null;
        $priority = sanitize_input($_POST['priority']);
        $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        $assigned_to = $_SESSION['user_id'];
        
        $stmt = $conn->prepare("INSERT INTO tasks (title, description, case_id, assigned_to, priority, due_date) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $case_id, $assigned_to, $priority, $due_date]);
        
        echo json_encode(['success' => true, 'message' => 'Tâche créée avec succès']);
        
    } elseif ($action == 'update_status') {
        $task_id = (int)$_POST['task_id'];
        $status = sanitize_input($_POST['status']);
        
        $completed_at = ($status == 'terminée') ? date('Y-m-d H:i:s') : null;
        
        $stmt = $conn->prepare("UPDATE tasks SET status = ?, completed_at = ? WHERE id = ?");
        $stmt->execute([$status, $completed_at, $task_id]);
        
        echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
        
    } elseif ($action == 'delete') {
        $task_id = (int)$_POST['task_id'];
        
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);
        
        echo json_encode(['success' => true, 'message' => 'Tâche supprimée']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
?>

