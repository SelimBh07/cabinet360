<?php
/**
 * Cabinet360 - Quick Subscription Test
 * Test the subscription system without database connection
 */

echo "<h2>👑 Cabinet360 Subscription System - Test Results</h2>";

// Test 1: Check if subscription page exists
$subscription_file = 'pages/subscription.php';
if (file_exists($subscription_file)) {
    echo "✅ <strong>Subscription page exists:</strong> pages/subscription.php<br>";
} else {
    echo "❌ <strong>Subscription page missing:</strong> pages/subscription.php<br>";
}

// Test 2: Check if fix script exists
$fix_file = 'fix_subscription_error.php';
if (file_exists($fix_file)) {
    echo "✅ <strong>Fix script exists:</strong> fix_subscription_error.php<br>";
} else {
    echo "❌ <strong>Fix script missing:</strong> fix_subscription_error.php<br>";
}

// Test 3: Check if activation script exists
$activation_file = 'activate_subscription.php';
if (file_exists($activation_file)) {
    echo "✅ <strong>Activation script exists:</strong> activate_subscription.php<br>";
} else {
    echo "❌ <strong>Activation script missing:</strong> activate_subscription.php<br>";
}

echo "<br><strong>🔗 Test Links:</strong><br>";
echo "• <a href='pages/subscription.php' target='_blank'>View Subscription Page</a><br>";
echo "• <a href='fix_subscription_error.php' target='_blank'>Run Database Fix</a><br>";
echo "• <a href='activate_subscription.php' target='_blank'>Run Activation Script</a><br>";

echo "<br><strong>📋 What to Test:</strong><br>";
echo "1. <strong>Database Fix:</strong> Run fix_subscription_error.php first<br>";
echo "2. <strong>Subscription Page:</strong> Visit pages/subscription.php<br>";
echo "3. <strong>Expected Results:</strong><br>";
echo "   ✅ No database errors<br>";
echo "   ✅ Current subscription (FREE PLAN)<br>";
echo "   ✅ Three subscription plans displayed<br>";
echo "   ✅ Professional pricing and features<br>";

echo "<br><h3 style='color: #D4AF37;'>🎯 Next Steps:</h3>";
echo "<ol>";
echo "<li><strong>Start XAMPP MySQL</strong> if not running</li>";
echo "<li><strong>Run Database Fix:</strong> <a href='fix_subscription_error.php'>fix_subscription_error.php</a></li>";
echo "<li><strong>Test Subscription:</strong> <a href='pages/subscription.php'>pages/subscription.php</a></li>";
echo "<li><strong>Login as Lawyer:</strong> <a href='login_lawyer.php'>login_lawyer.php</a></li>";
echo "</ol>";

echo "<br><strong>💡 If you see database errors:</strong><br>";
echo "• Make sure XAMPP MySQL is running (green light)<br>";
echo "• Check database credentials in config/config.php<br>";
echo "• Import database_multitenant.sql if needed<br>";
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
ol { margin-left: 20px; }
li { margin-bottom: 8px; }
</style>
