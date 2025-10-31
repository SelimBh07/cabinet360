<?php
/**
 * Cabinet360 SaaS - Multi-Tenant Helper Functions
 * Universal functions to update legacy queries to multi-tenant
 */

// These functions automatically add lawyer_id filter to queries

function add_client_multitenant($conn, $lawyer_id, $full_name, $cin, $phone, $email, $address, $notes) {
    $stmt = $conn->prepare("INSERT INTO clients (lawyer_id, full_name, cin, phone, email, address, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$lawyer_id, $full_name, $cin, $phone, $email, $address, $notes]);
}

function add_case_multitenant($conn, $lawyer_id, $client_id, $case_number, $type, $status, $lawyer_name, $date_opened, $notes, $document_path = null) {
    $stmt = $conn->prepare("INSERT INTO cases (lawyer_id, client_id, case_number, type, status, lawyer, date_opened, notes, document_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$lawyer_id, $client_id, $case_number, $type, $status, $lawyer_name, $date_opened, $notes, $document_path]);
}

function add_appointment_multitenant($conn, $lawyer_id, $client_id, $date, $time, $purpose, $location, $status) {
    $stmt = $conn->prepare("INSERT INTO appointments (lawyer_id, client_id, date, time, purpose, location, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$lawyer_id, $client_id, $date, $time, $purpose, $location, $status]);
}

function add_payment_multitenant($conn, $lawyer_id, $client_id, $case_id, $date, $amount, $method, $status, $notes) {
    $stmt = $conn->prepare("INSERT INTO payments (lawyer_id, client_id, case_id, date, amount, method, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$lawyer_id, $client_id, $case_id, $date, $amount, $method, $status, $notes]);
}

// Get functions with lawyer_id filter
function get_lawyer_clients($conn, $lawyer_id) {
    $stmt = $conn->prepare("SELECT * FROM clients WHERE lawyer_id = ? ORDER BY created_at DESC");
    $stmt->execute([$lawyer_id]);
    return $stmt->fetchAll();
}

function get_lawyer_cases($conn, $lawyer_id) {
    $stmt = $conn->prepare("SELECT cs.*, cl.full_name as client_name FROM cases cs JOIN clients cl ON cs.client_id = cl.id WHERE cs.lawyer_id = ? ORDER BY cs.created_at DESC");
    $stmt->execute([$lawyer_id]);
    return $stmt->fetchAll();
}

function get_lawyer_appointments($conn, $lawyer_id) {
    $stmt = $conn->prepare("SELECT a.*, c.full_name as client_name FROM appointments a JOIN clients c ON a.client_id = c.id WHERE a.lawyer_id = ? ORDER BY a.date DESC");
    $stmt->execute([$lawyer_id]);
    return $stmt->fetchAll();
}

function get_lawyer_payments($conn, $lawyer_id) {
    $stmt = $conn->prepare("SELECT p.*, c.full_name as client_name, cs.case_number FROM payments p JOIN clients c ON p.client_id = c.id LEFT JOIN cases cs ON p.case_id = cs.id WHERE p.lawyer_id = ? ORDER BY p.date DESC");
    $stmt->execute([$lawyer_id]);
    return $stmt->fetchAll();
}

?>






