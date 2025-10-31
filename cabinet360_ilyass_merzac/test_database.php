<?php
/**
 * Database Connection Test
 */

require_once 'config/config.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test basic connection
    $stmt = $conn->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ Database connection: <strong>SUCCESS</strong><br>";
    
    // Test if tables exist
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<br><strong>Tables found:</strong><br>";
    foreach ($tables as $table) {
        echo "✅ $table<br>";
    }
    
    // Test lawyers table
    if (in_array('lawyers', $tables)) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM lawyers");
        $count = $stmt->fetch()['count'];
        echo "<br>✅ Lawyers table has <strong>$count</strong> records<br>";
        
        // Show sample lawyer
        $stmt = $conn->query("SELECT id, cabinet_name, lawyer_name, email FROM lawyers LIMIT 1");
        $lawyer = $stmt->fetch();
        if ($lawyer) {
            echo "✅ Sample lawyer: <strong>" . htmlspecialchars($lawyer['lawyer_name']) . "</strong> (" . htmlspecialchars($lawyer['email']) . ")<br>";
        }
    }
    
    // Test admin table
    if (in_array('admin_users', $tables)) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM admin_users");
        $count = $stmt->fetch()['count'];
        echo "✅ Admin users table has <strong>$count</strong> records<br>";
    }
    
    echo "<br><h3 style='color: green;'>🎉 Database is ready! You can now:</h3>";
    echo "• <a href='signup.php'>Create a new lawyer account</a><br>";
    echo "• <a href='login_lawyer.php'>Login as lawyer</a><br>";
    echo "• <a href='admin/login.php'>Login as admin</a><br>";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    echo "<br><strong>Possible solutions:</strong><br>";
    echo "1. Make sure MySQL is running<br>";
    echo "2. Create database 'cabinet360_saas'<br>";
    echo "3. Import database_multitenant.sql<br>";
    echo "4. Check database credentials in config/config.php<br>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
h2 { color: #333; }
</style>

