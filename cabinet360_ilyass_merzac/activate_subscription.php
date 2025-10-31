<?php
/**
 * Cabinet360 - Subscription System Activation
 * This script activates the subscription system and creates default plans
 */

require_once 'config/config.php';

echo "<h2>👑 Cabinet360 Subscription System Activation</h2>";

try {
    // Test basic connection
    $stmt = $conn->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ Database connection: <strong>SUCCESS</strong><br><br>";
    
    // Check if subscription_plans table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'subscription_plans'");
    $plans_exists = $stmt->fetch();
    
    if (!$plans_exists) {
        echo "⚠️ Subscription plans table missing. Creating it...<br>";
        
        // Create subscription_plans table
        $sql = "CREATE TABLE IF NOT EXISTS subscription_plans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            plan_name VARCHAR(50) NOT NULL UNIQUE,
            price_monthly DECIMAL(10,2) NOT NULL DEFAULT 0,
            max_clients INT DEFAULT 0,
            max_cases INT DEFAULT 0,
            storage_mb INT DEFAULT 100,
            features TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $conn->exec($sql);
        echo "✅ Subscription plans table created successfully!<br>";
    } else {
        echo "✅ Subscription plans table already exists<br>";
    }
    
    // Check if we have subscription plans
    $stmt = $conn->query("SELECT COUNT(*) as count FROM subscription_plans");
    $plan_count = $stmt->fetch()['count'];
    
    if ($plan_count == 0) {
        echo "📊 Creating default subscription plans...<br>";
        
        // Add default subscription plans
        $default_plans = [
            [
                'plan_name' => 'Free',
                'price' => 0,
                'billing_cycle' => 'monthly',
                'max_clients' => 5,
                'max_cases' => 10,
                'max_storage_mb' => 100,
                'features' => json_encode([
                    'basic_support' => true,
                    'email_notifications' => true,
                    'basic_reports' => true
                ])
            ],
            [
                'plan_name' => 'Pro',
                'price' => 299,
                'billing_cycle' => 'monthly',
                'max_clients' => 50,
                'max_cases' => 100,
                'max_storage_mb' => 1000,
                'features' => json_encode([
                    'priority_support' => true,
                    'email_notifications' => true,
                    'advanced_reports' => true,
                    'pdf_generation' => true,
                    'calendar_sync' => true
                ])
            ],
            [
                'plan_name' => 'Premium',
                'price' => 599,
                'billing_cycle' => 'monthly',
                'max_clients' => 0,
                'max_cases' => 0,
                'max_storage_mb' => 5000,
                'features' => json_encode([
                    'priority_support' => true,
                    'email_notifications' => true,
                    'advanced_reports' => true,
                    'pdf_generation' => true,
                    'calendar_sync' => true,
                    'api_access' => true,
                    'white_label' => true,
                    'custom_branding' => true
                ])
            ]
        ];
        
        $stmt = $conn->prepare("INSERT INTO subscription_plans (plan_name, price, billing_cycle, max_clients, max_cases, max_storage_mb, features) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($default_plans as $plan) {
            $stmt->execute([
                $plan['plan_name'],
                $plan['price_monthly'],
                $plan['max_clients'],
                $plan['max_cases'],
                $plan['storage_mb'],
                $plan['features']
            ]);
        }
        
        echo "✅ Default subscription plans created successfully!<br>";
    } else {
        echo "✅ Subscription plans already exist ($plan_count plans)<br>";
    }
    
    // Show subscription plans
    $stmt = $conn->query("SELECT * FROM subscription_plans ORDER BY price ASC");
    $plans = $stmt->fetchAll();
    
    echo "<br><strong>📊 Available Subscription Plans:</strong><br>";
    foreach ($plans as $plan) {
        $price = $plan['price'] == 0 ? 'FREE' : number_format($plan['price'], 0) . ' MAD/' . ($plan['billing_cycle'] ?? 'month');
        echo "• <strong>" . strtoupper($plan['plan_name']) . "</strong> - $price<br>";
        echo "  - Max Clients: " . ($plan['max_clients'] == 0 ? 'Unlimited' : $plan['max_clients']) . "<br>";
        echo "  - Max Cases: " . ($plan['max_cases'] == 0 ? 'Unlimited' : $plan['max_cases']) . "<br>";
        echo "  - Storage: " . $plan['max_storage_mb'] . " MB<br><br>";
    }
    
    // Test subscription page access
    echo "<br><strong>🔗 Subscription System Links:</strong><br>";
    echo "• <a href='pages/subscription.php'>View Subscription Page</a><br>";
    echo "• <a href='login_lawyer.php'>Login as Lawyer</a><br>";
    echo "• <a href='index.php'>Dashboard</a><br>";
    
    echo "<br><h3 style='color: green;'>🎉 Subscription System is now ACTIVE!</h3>";
    echo "<strong>Features Available:</strong><br>";
    echo "✅ View current subscription plan<br>";
    echo "✅ Compare available plans<br>";
    echo "✅ Plan upgrade interface (UI only)<br>";
    echo "✅ Multi-tenant subscription management<br>";
    echo "✅ Feature comparison<br>";
    echo "✅ Contact support integration<br>";
    
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

