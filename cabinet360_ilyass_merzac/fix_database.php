<?php
/**
 * Cabinet360 - Quick Database Fix & Test
 */

require_once 'config/config.php';

echo "<h2>🔧 Cabinet360 Database Fix & Test</h2>";

try {
    // Test basic connection
    $stmt = $conn->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ Database connection: <strong>SUCCESS</strong><br><br>";
    
    // Check if tasks table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'tasks'");
    $tasks_exists = $stmt->fetch();
    
    if (!$tasks_exists) {
        echo "⚠️ Tasks table missing. Creating it...<br>";
        
        // Create tasks table
        $sql = "CREATE TABLE IF NOT EXISTS tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lawyer_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            case_id INT,
            priority ENUM('urgente', 'haute', 'moyenne', 'basse') DEFAULT 'moyenne',
            status ENUM('à_faire', 'en_cours', 'terminée', 'annulée') DEFAULT 'à_faire',
            due_date DATE,
            assigned_to INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (lawyer_id) REFERENCES lawyers(id) ON DELETE CASCADE,
            FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL,
            FOREIGN KEY (assigned_to) REFERENCES lawyers(id) ON DELETE SET NULL,
            INDEX idx_lawyer (lawyer_id),
            INDEX idx_status (status),
            INDEX idx_priority (priority),
            INDEX idx_due_date (due_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $conn->exec($sql);
        echo "✅ Tasks table created successfully!<br>";
        
        // Add sample tasks
        $stmt = $conn->prepare("INSERT INTO tasks (lawyer_id, title, description, case_id, priority, status, due_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([1, 'Préparer la défense', 'Analyser les documents du dossier', 1, 'haute', 'à_faire', '2025-01-25']);
        $stmt->execute([1, 'Contacter le client', 'Appeler le client pour mise à jour', 1, 'moyenne', 'en_cours', '2025-01-20']);
        $stmt->execute([1, 'Rédiger le contrat', 'Préparer le contrat de divorce', 2, 'urgente', 'à_faire', '2025-01-18']);
        
        echo "✅ Sample tasks added!<br>";
    } else {
        echo "✅ Tasks table already exists<br>";
    }
    
    // Test tasks query
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tasks WHERE lawyer_id = ?");
    $stmt->execute([1]);
    $task_count = $stmt->fetch()['count'];
    echo "✅ Found <strong>$task_count</strong> tasks for lawyer ID 1<br><br>";
    
    // Test all tables
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<strong>📊 Database Tables:</strong><br>";
    foreach ($tables as $table) {
        echo "✅ $table<br>";
    }
    
    echo "<br><h3 style='color: green;'>🎉 Everything is working!</h3>";
    echo "<strong>You can now:</strong><br>";
    echo "• <a href='signup.php'>Create a new lawyer account</a><br>";
    echo "• <a href='login_lawyer.php'>Login as lawyer</a><br>";
    echo "• <a href='admin/login.php'>Login as admin</a><br>";
    echo "• <a href='pages/tasks.php'>View tasks page</a><br>";
    
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

