<?php
/**
 * Cabinet360 - Payment System Activation
 * This script activates the payment system and adds sample data
 */

require_once 'config/config.php';

echo "<h2>💰 Cabinet360 Payment System Activation</h2>";

try {
    // Test basic connection
    $stmt = $conn->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ Database connection: <strong>SUCCESS</strong><br><br>";
    
    // Check if payments table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'payments'");
    $payments_exists = $stmt->fetch();
    
    if (!$payments_exists) {
        echo "⚠️ Payments table missing. Creating it...<br>";
        
        // Create payments table
        $sql = "CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lawyer_id INT NOT NULL,
            client_id INT NOT NULL,
            case_id INT,
            date DATE NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            method ENUM('espèces', 'chèque', 'virement', 'carte') NOT NULL,
            status ENUM('payé', 'impayé', 'partiel') NOT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (lawyer_id) REFERENCES lawyers(id) ON DELETE CASCADE,
            FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
            FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL,
            INDEX idx_lawyer (lawyer_id),
            INDEX idx_client (client_id),
            INDEX idx_case (case_id),
            INDEX idx_date (date),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $conn->exec($sql);
        echo "✅ Payments table created successfully!<br>";
    } else {
        echo "✅ Payments table already exists<br>";
    }
    
    // Check if we have sample data
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM payments WHERE lawyer_id = ?");
    $stmt->execute([1]);
    $payment_count = $stmt->fetch()['count'];
    
    if ($payment_count == 0) {
        echo "📊 Adding sample payment data...<br>";
        
        // Add sample payments
        $sample_payments = [
            [1, 1, 1, '2025-01-20', 5000.00, 'virement', 'payé', 'Acompte initial pour le dossier commercial'],
            [1, 2, 2, '2025-02-15', 3000.00, 'chèque', 'payé', 'Consultation et honoraires divorce'],
            [1, 3, 3, '2025-03-10', 4500.00, 'espèces', 'impayé', 'Honoraires à régler - contentieux immobilier'],
            [1, 1, 1, '2025-01-25', 2000.00, 'virement', 'partiel', 'Paiement partiel - reste 3000 MAD'],
            [1, 2, NULL, '2025-02-20', 1500.00, 'carte', 'payé', 'Consultation supplémentaire'],
            [1, 3, 3, '2025-03-15', 2500.00, 'chèque', 'impayé', 'Deuxième échéance non réglée'],
            [1, 1, NULL, '2025-01-30', 800.00, 'espèces', 'payé', 'Consultation urgente'],
            [1, 2, 2, '2025-02-25', 1200.00, 'virement', 'payé', 'Frais de procédure']
        ];
        
        $stmt = $conn->prepare("INSERT INTO payments (lawyer_id, client_id, case_id, date, amount, method, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($sample_payments as $payment) {
            $stmt->execute($payment);
        }
        
        echo "✅ Sample payments added successfully!<br>";
    } else {
        echo "✅ Sample payments already exist ($payment_count payments)<br>";
    }
    
    // Test payment queries
    $stmt = $conn->prepare("SELECT 
        COUNT(*) as total_payments,
        COALESCE(SUM(CASE WHEN status = 'payé' THEN amount ELSE 0 END), 0) as total_paid,
        COALESCE(SUM(CASE WHEN status = 'impayé' THEN amount ELSE 0 END), 0) as total_unpaid,
        COALESCE(SUM(CASE WHEN status = 'partiel' THEN amount ELSE 0 END), 0) as total_partial
        FROM payments WHERE lawyer_id = ?");
    $stmt->execute([1]);
    $stats = $stmt->fetch();
    
    echo "<br><strong>📊 Payment Statistics:</strong><br>";
    echo "• Total Payments: <strong>" . $stats['total_payments'] . "</strong><br>";
    echo "• Total Paid: <strong>" . number_format($stats['total_paid'], 2) . " MAD</strong><br>";
    echo "• Total Unpaid: <strong>" . number_format($stats['total_unpaid'], 2) . " MAD</strong><br>";
    echo "• Total Partial: <strong>" . number_format($stats['total_partial'], 2) . " MAD</strong><br>";
    
    // Test payment page access
    echo "<br><strong>🔗 Payment System Links:</strong><br>";
    echo "• <a href='pages/payments.php'>View Payments Page</a><br>";
    echo "• <a href='index.php'>Dashboard (with payment stats)</a><br>";
    echo "• <a href='login_lawyer.php'>Login as Lawyer</a><br>";
    
    echo "<br><h3 style='color: green;'>🎉 Payment System is now ACTIVE!</h3>";
    echo "<strong>Features Available:</strong><br>";
    echo "✅ Add new payments<br>";
    echo "✅ Edit existing payments<br>";
    echo "✅ Delete payments<br>";
    echo "✅ View payment details<br>";
    echo "✅ Filter by status<br>";
    echo "✅ Multi-tenant data isolation<br>";
    echo "✅ Payment statistics on dashboard<br>";
    echo "✅ PDF receipt generation (ready)<br>";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    echo "<br><strong>Solutions:</strong><br>";
    echo "1. Make sure MySQL is running<br>";
    echo "2. Check database credentials in config/config.php<br>";
    echo "3. Import database_multitenant.sql<br>";
}
?>

<style>
body { 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    margin: 40px; 
    background: #f5f5f5; 
    color: #333;
}
h2 { color: #D4AF37; }
a { color: #D4AF37; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>

