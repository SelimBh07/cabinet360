<?php
/**
 * Update user preferences endpoint
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/UserPreferences.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$key = $input['key'] ?? '';
$value = $input['value'] ?? '';

if (empty($key) || empty($value)) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

$preferences = new UserPreferences($conn, $_SESSION['lawyer_id']);

switch ($key) {
    case 'theme':
        if (!in_array($value, ['light', 'dark'])) {
            echo json_encode(['success' => false, 'message' => 'Thème invalide']);
            exit;
        }
        $success = $preferences->updateTheme($value);
        break;

    case 'language':
        if (!in_array($value, ['fr', 'en'])) {
            echo json_encode(['success' => false, 'message' => 'Langue invalide']);
            exit;
        }
        $success = $preferences->updateLanguage($value);
        break;

    case 'notifications':
        $settings = json_decode($value, true);
        if (!is_array($settings)) {
            echo json_encode(['success' => false, 'message' => 'Paramètres de notification invalides']);
            exit;
        }
        $success = $preferences->updateNotificationSettings(
            $settings['email'] ?? false,
            $settings['appointment'] ?? false,
            $settings['payment'] ?? false
        );
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Paramètre inconnu']);
        exit;
}

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Préférence mise à jour avec succès' : 'Erreur lors de la mise à jour'
]);