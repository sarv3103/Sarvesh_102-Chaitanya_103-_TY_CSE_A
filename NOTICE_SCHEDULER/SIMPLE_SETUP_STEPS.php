<?php
echo "🎯 CampusChrono - Simple Setup Steps\n";
echo "====================================\n\n";

echo "✅ STEP 1: Database Setup\n";
echo "=========================\n";
echo "Go to: http://localhost/NOTICE_SCHEDULER/execute-sql-fix.php\n";
echo "This will fix the database and add departments/classes.\n\n";

echo "✅ STEP 2: Test Registration (OTP on Screen)\n";
echo "============================================\n";
echo "Go to: http://localhost/NOTICE_SCHEDULER/register.html\n";
echo "Register a student - OTP will show on screen (no email needed).\n\n";

echo "✅ STEP 3: Test Admin Login\n";
echo "===========================\n";
echo "Go to: http://localhost/NOTICE_SCHEDULER/\n";
echo "Login: admin@noticeboard.com / admin123\n\n";

echo "✅ STEP 4: Test Bulk Operations\n";
echo "===============================\n";
echo "In admin dashboard, click '🔄 Bulk Operations'\n";
echo "You should see the bulk class change interface.\n\n";

echo "📧 EMAIL NOTE:\n";
echo "==============\n";
echo "Email is DISABLED for now. OTP shows on registration screen.\n";
echo "This is actually better for testing!\n\n";

echo "🚀 START HERE: http://localhost/NOTICE_SCHEDULER/execute-sql-fix.php\n";

// Quick system check
require_once 'config/config.php';

echo "\n📊 SYSTEM STATUS:\n";
echo "=================\n";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    echo "✅ Database: CONNECTED\n";
} catch (Exception $e) {
    echo "❌ Database: FAILED - " . $e->getMessage() . "\n";
}

echo "📧 Email: " . (EMAIL_ENABLED ? 'ENABLED' : 'DISABLED (OTP on screen)') . "\n";

$files = ['register.html', 'admin-dashboard.html', 'api/register.php'];
foreach ($files as $file) {
    echo (file_exists($file) ? "✅" : "❌") . " File $file\n";
}

echo "\n🎯 Everything is ready! Start with step 1!\n";
?>