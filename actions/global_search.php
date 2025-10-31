<?php
/**
 * Cabinet360 - Global Search
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$query = sanitize_input($_GET['query'] ?? '');

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Requête trop courte']);
    exit;
}

try {
    $search_term = "%$query%";
    $results = [
        'clients' => [],
        'cases' => [],
        'appointments' => []
    ];
    
    // Search Clients
    $stmt = $conn->prepare("SELECT id, full_name, cin, phone, email 
                            FROM clients 
                            WHERE full_name LIKE ? OR cin LIKE ? OR phone LIKE ? OR email LIKE ? 
                            LIMIT 5");
    $stmt->execute([$search_term, $search_term, $search_term, $search_term]);
    $results['clients'] = $stmt->fetchAll();
    
    // Search Cases
    $stmt = $conn->prepare("SELECT cs.id, cs.case_number, cs.type, cs.status, c.full_name as client_name 
                            FROM cases cs 
                            JOIN clients c ON cs.client_id = c.id 
                            WHERE cs.case_number LIKE ? OR c.full_name LIKE ? 
                            LIMIT 5");
    $stmt->execute([$search_term, $search_term]);
    $results['cases'] = $stmt->fetchAll();
    
    // Search Appointments
    $stmt = $conn->prepare("SELECT a.id, a.date, a.time, a.purpose, c.full_name as client_name 
                            FROM appointments a 
                            JOIN clients c ON a.client_id = c.id 
                            WHERE c.full_name LIKE ? OR a.purpose LIKE ? 
                            LIMIT 5");
    $stmt->execute([$search_term, $search_term]);
    $results['appointments'] = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'results' => $results]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
?>

