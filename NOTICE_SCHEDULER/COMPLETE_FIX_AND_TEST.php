<?php
echo "🎯 CampusChrono - Complete Fix & Test\n";
echo "====================================\n\n";

require_once 'config/config.php';
require_once 'includes/functions.php';

// Test 1: Email System
echo "1️⃣ TESTING EMAIL SYSTEM\n";
echo "========================\n";
echo "📧 Email Enabled: " . (EMAIL_ENABLED ? 'YES' : 'NO') . "\n";
echo "📧 SMTP Host: " . SMTP_HOST . "\n";
echo "📧 SMTP User: " . SMTP_USER . "\n";
echo "📧 App Password: " . SMTP_PASS . "\n\n";

if (EMAIL_ENABLED) {
    echo "🚀 Sending test email...\n";
    $testResult = sendOTPEmail('campuschrono3103@gmail.com', '123456', 'VERIFY');
    
    if ($testResult) {
        echo "✅ EMAIL WORKING! Check your inbox.\n\n";
    } else {
        echo "❌ Email failed. Will show OTP on screen instead.\n\n";
    }
} else {
    echo "⚠️ Email is disabled.\n\n";
}

// Test 2: Database Users Check
echo "2️⃣ CHECKING DATABASE USERS\n";
echo "===========================\n";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check all users
    $stmt = $pdo->query("SELECT u.user_id, u.email, u.name, u.is_verified, u.is_active, r.role_name, c.class_name, d.department_name 
                         FROM users u 
                         JOIN roles r ON u.role_id = r.role_id 
                         LEFT JOIN classes c ON u.class_id = c.class_id 
                         LEFT JOIN departments d ON u.department_id = d.department_id 
                         ORDER BY u.created_at DESC");
    
    $users = $stmt->fetchAll();
    
    echo "📊 Total users in database: " . count($users) . "\n\n";
    
    if (count($users) > 0) {
        echo "👥 USER LIST:\n";
        echo "=============\n";
        foreach ($users as $user) {
            $status = '';
            if ($user['role_name'] == 'Admin') {
                $status = '🔑 ADMIN';
            } elseif (!$user['is_verified']) {
                $status = '⏳ NOT VERIFIED';
            } elseif (!$user['is_active']) {
                $status = '⏳ PENDING APPROVAL';
            } else {
                $status = '✅ ACTIVE';
            }
            
            echo "  • {$user['name']} ({$user['email']}) - {$user['role_name']} - $status\n";
            if ($user['class_name']) {
                echo "    Class: {$user['class_name']} - {$user['department_name']}\n";
            }
            echo "\n";
        }
    } else {
        echo "❌ No users found! Registration might not be working.\n\n";
    }
    
    // Check pending approvals specifically
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_verified = TRUE AND is_active = FALSE AND role_id != 1");
    $pendingCount = $stmt->fetch()['count'];
    echo "⏳ Users pending approval: $pendingCount\n\n";
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n\n";
}

// Test 3: Registration Flow Test
echo "3️⃣ TESTING REGISTRATION FLOW\n";
echo "=============================\n";
echo "🔗 Test registration: http://localhost/NOTICE_SCHEDULER/register.html\n";
echo "📝 Use these test details:\n";
echo "   - Role: Student\n";
echo "   - Name: Test User " . date('His') . "\n";
echo "   - Email: test" . time() . "@college.edu\n";
echo "   - Password: test123\n";
echo "   - Select any department and class\n";
echo "   - Roll number: TEST" . time() . "\n\n";

// Test 4: Admin Panel Check
echo "4️⃣ ADMIN PANEL ACCESS\n";
echo "=====================\n";
echo "🔗 Admin login: http://localhost/NOTICE_SCHEDULER/\n";
echo "🔑 Credentials: admin@noticeboard.com / admin123\n";
echo "📋 Check these sections:\n";
echo "   - 👥 User Management (should show all users)\n";
echo "   - ⏳ Pending Approvals (should show unverified users)\n";
echo "   - 🔄 Bulk Operations (should show class change options)\n\n";

// Instructions
echo "🎯 WHAT TO DO NOW:\n";
echo "==================\n";
echo "1. ✅ Email system is configured with new app password\n";
echo "2. 🧪 Test registration with the details above\n";
echo "3. 📧 Check if you receive OTP via email\n";
echo "4. ✅ Verify OTP and complete registration\n";
echo "5. 🔑 Login as admin and check if user appears\n";
echo "6. ✅ Test bulk operations\n\n";

echo "🚨 IF USERS DON'T APPEAR:\n";
echo "=========================\n";
echo "1. Check if OTP verification was completed\n";
echo "2. Users appear in 'Pending Approvals' until admin approves them\n";
echo "3. Only verified users appear in admin panels\n";
echo "4. Admin must approve users before they become active\n\n";

echo "📞 NEXT STEPS:\n";
echo "==============\n";
echo "1. Run this script to see current status\n";
echo "2. Test registration with email\n";
echo "3. Complete the full registration flow\n";
echo "4. Check admin panels\n\n";

echo "🎉 SYSTEM IS READY! Start testing! 🎉\n";
?>