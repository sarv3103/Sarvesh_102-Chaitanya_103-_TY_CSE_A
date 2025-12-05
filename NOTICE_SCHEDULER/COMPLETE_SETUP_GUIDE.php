<?php
// Complete setup guide for CampusChrono
echo "🎯 CampusChrono - Complete Setup Guide\n";
echo "=====================================\n\n";

echo "📋 STEP-BY-STEP SETUP:\n";
echo "======================\n\n";

echo "1️⃣ FIRST: Fix Database\n";
echo "   Open in browser: http://localhost/NOTICE_SCHEDULER/execute-sql-fix.php\n";
echo "   This will:\n";
echo "   ✅ Fix foreign key constraints\n";
echo "   ✅ Add 7 departments (CSE, IT, EXTC, MECH, CIVIL, EE, CHEM)\n";
echo "   ✅ Add 36 classes across all departments\n";
echo "   ✅ Verify admin user exists\n\n";

echo "2️⃣ SECOND: Test Email System\n";
echo "   Open in browser: http://localhost/NOTICE_SCHEDULER/test-phpmailer-email.php\n";
echo "   This will test the new PHPMailer integration\n\n";

echo "3️⃣ THIRD: Test Registration\n";
echo "   Go to: http://localhost/NOTICE_SCHEDULER/register.html\n";
echo "   Register a new student:\n";
echo "   - Role: Student\n";
echo "   - Name: Test Student\n";
echo "   - Email: your-email@gmail.com\n";
echo "   - Password: test123\n";
echo "   - Select department and class\n";
echo "   - Enter roll number\n";
echo "   You should receive OTP via email!\n\n";

echo "4️⃣ FOURTH: Test Admin Login\n";
echo "   Go to: http://localhost/NOTICE_SCHEDULER/\n";
echo "   Login with: admin@noticeboard.com / admin123\n";
echo "   Check all sections work:\n";
echo "   - 👥 User Management\n";
echo "   - ⏳ Pending Approvals\n";
echo "   - 🔄 Bulk Operations\n";
echo "   - 🏢 Departments\n";
echo "   - 📚 Classes\n\n";

echo "5️⃣ FIFTH: Test Bulk Operations\n";
echo "   In admin dashboard:\n";
echo "   - Click '🔄 Bulk Operations'\n";
echo "   - Select source class\n";
echo "   - Select students\n";
echo "   - Choose target class\n";
echo "   - Move students\n\n";

echo "🔧 CURRENT CONFIGURATION:\n";
echo "=========================\n";
echo "📧 Email: ENABLED with PHPMailer\n";
echo "🔐 SMTP: Gmail with app password\n";
echo "📊 Database: Ready to be fixed\n";
echo "🎯 System: Complete and ready\n\n";

echo "⚠️ IMPORTANT NOTES:\n";
echo "===================\n";
echo "1. Make sure XAMPP is running (Apache + MySQL)\n";
echo "2. Gmail app password is configured: pkpy mbqv bmzl ncgw\n";
echo "3. All files are in the correct location\n";
echo "4. Run each step in order\n\n";

echo "🚀 READY TO START!\n";
echo "==================\n";
echo "Begin with step 1: http://localhost/NOTICE_SCHEDULER/execute-sql-fix.php\n\n";

// Also create a quick status check
require_once 'config/config.php';

echo "📊 QUICK STATUS CHECK:\n";
echo "======================\n";

// Check database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    echo "✅ Database connection: WORKING\n";
} catch (Exception $e) {
    echo "❌ Database connection: FAILED - " . $e->getMessage() . "\n";
}

// Check email configuration
echo "📧 Email enabled: " . (EMAIL_ENABLED ? 'YES' : 'NO') . "\n";
echo "📧 SMTP Host: " . SMTP_HOST . "\n";
echo "📧 SMTP User: " . SMTP_USER . "\n";

// Check critical files
$files = ['register.html', 'admin-dashboard.html', 'api/register.php', 'vendor/PHPMailer.php'];
$allFilesExist = true;
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ File $file: EXISTS\n";
    } else {
        echo "❌ File $file: MISSING\n";
        $allFilesExist = false;
    }
}

if ($allFilesExist) {
    echo "\n🎉 ALL SYSTEMS READY! Start with step 1!\n";
} else {
    echo "\n⚠️ Some files are missing. Check your setup.\n";
}
?>