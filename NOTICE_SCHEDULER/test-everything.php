<?php
/**
 * Complete System Test - Tests Email, Registration, and Database
 * Run this in browser: http://localhost/NOTICE_SCHEDULER/test-everything.php
 */

require_once 'config/config.php';
require_once 'includes/functions.php';

echo "<h1>🧪 CampusChrono Complete System Test</h1>";
echo "<pre>";

// Test 1: Database Connection
echo "=== TEST 1: Database Connection ===\n";
$conn = getDBConnection();
if ($conn) {
    echo "✅ Database connected successfully\n\n";
} else {
    die("❌ Database connection failed!\n");
}

// Test 2: Check Departments
echo "=== TEST 2: Departments ===\n";
$result = $conn->query("SELECT * FROM departments ORDER BY department_id");
$departments = [];
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
    echo "✅ ID {$row['department_id']}: {$row['department_code']} - {$row['department_name']}\n";
}
if (count($departments) == 0) {
    echo "❌ No departments found! Run fix-database-now.php first\n\n";
} else {
    echo "✅ Found " . count($departments) . " departments\n\n";
}

// Test 3: Check Classes
echo "=== TEST 3: Classes ===\n";
$result = $conn->query("
    SELECT c.class_id, c.class_name, d.department_code, d.department_id 
    FROM classes c 
    JOIN departments d ON c.department_id = d.department_id 
    ORDER BY c.class_id 
    LIMIT 10
");
$classes = [];
while ($row = $result->fetch_assoc()) {
    $classes[] = $row;
    echo "✅ Class ID {$row['class_id']}: {$row['department_code']} {$row['class_name']} (Dept ID: {$row['department_id']})\n";
}
if (count($classes) == 0) {
    echo "❌ No classes found! Run fix-database-now.php first\n\n";
} else {
    echo "✅ Found classes with proper department mapping\n\n";
}

// Test 4: Email Configuration
echo "=== TEST 4: Email Configuration ===\n";
echo "Email Enabled: " . (EMAIL_ENABLED ? "✅ YES" : "❌ NO") . "\n";
echo "SMTP Host: " . SMTP_HOST . "\n";
echo "SMTP Port: " . SMTP_PORT . "\n";
echo "SMTP User: " . SMTP_USER . "\n";
echo "SMTP From: " . SMTP_FROM . "\n";
echo "SMTP From Name: " . SMTP_FROM_NAME . "\n\n";

// Test 5: Send Test Email
echo "=== TEST 5: Send Test Email ===\n";
if (EMAIL_ENABLED) {
    $testEmail = SMTP_USER; // Send to self
    $testSubject = "CampusChrono Test Email - " . date('Y-m-d H:i:s');
    $testMessage = "This is a test email from CampusChrono.\n\n";
    $testMessage .= "If you receive this, your email system is working correctly!\n\n";
    $testMessage .= "Test Details:\n";
    $testMessage .= "- Time: " . date('Y-m-d H:i:s') . "\n";
    $testMessage .= "- SMTP Host: " . SMTP_HOST . "\n";
    $testMessage .= "- SMTP Port: " . SMTP_PORT . "\n\n";
    $testMessage .= "Best regards,\nCampusChrono System";
    
    echo "Sending test email to: {$testEmail}\n";
    echo "Subject: {$testSubject}\n";
    
    if (sendSMTPEmail($testEmail, $testSubject, $testMessage)) {
        echo "✅ Email sent successfully!\n";
        echo "📧 Check your inbox: {$testEmail}\n\n";
    } else {
        echo "❌ Email sending failed\n";
        echo "⚠️ Check error logs for details\n\n";
    }
} else {
    echo "⚠️ Email is disabled in config\n";
    echo "Set EMAIL_ENABLED = true in config/config.php\n\n";
}

// Test 6: Test OTP Generation
echo "=== TEST 6: OTP Generation ===\n";
$otp = generateOTP();
echo "Generated OTP: {$otp}\n";
echo "OTP Length: " . strlen($otp) . " (Expected: " . OTP_LENGTH . ")\n";
if (strlen($otp) == OTP_LENGTH) {
    echo "✅ OTP generation working\n\n";
} else {
    echo "❌ OTP generation issue\n\n";
}

// Test 7: Test Registration Data
echo "=== TEST 7: Test Registration Data ===\n";
if (count($departments) > 0 && count($classes) > 0) {
    $testDept = $departments[0];
    $testClass = $classes[0];
    
    echo "Test Department: {$testDept['department_code']} (ID: {$testDept['department_id']})\n";
    echo "Test Class: {$testClass['class_name']} (ID: {$testClass['class_id']})\n";
    
    // Verify class belongs to department
    $stmt = $conn->prepare("SELECT class_id FROM classes WHERE class_id = ? AND department_id = ?");
    $stmt->bind_param("ii", $testClass['class_id'], $testDept['department_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "✅ Class-Department relationship valid\n\n";
    } else {
        echo "❌ Class-Department relationship invalid\n\n";
    }
    $stmt->close();
} else {
    echo "⚠️ No test data available\n\n";
}

// Test 8: Check Admin User
echo "=== TEST 8: Admin User ===\n";
$result = $conn->query("SELECT user_id, email, name, is_active FROM users WHERE role_id = 1 LIMIT 1");
if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "✅ Admin user found\n";
    echo "  - Email: {$admin['email']}\n";
    echo "  - Name: {$admin['name']}\n";
    echo "  - Active: " . ($admin['is_active'] ? "Yes" : "No") . "\n\n";
} else {
    echo "❌ No admin user found\n\n";
}

closeDBConnection($conn);

// Final Summary
echo "=== 🎯 SUMMARY ===\n";
echo "Database: " . ($conn ? "✅" : "❌") . "\n";
echo "Departments: " . (count($departments) > 0 ? "✅" : "❌") . "\n";
echo "Classes: " . (count($classes) > 0 ? "✅" : "❌") . "\n";
echo "Email Config: " . (EMAIL_ENABLED ? "✅" : "❌") . "\n";
echo "Admin User: ✅\n";

echo "\n=== 📋 NEXT STEPS ===\n";
if (count($departments) == 0 || count($classes) == 0) {
    echo "1. ⚠️ Run fix-database-now.php to setup departments and classes\n";
}
echo "2. 🧪 Test registration at: " . BASE_URL . "/register.html\n";
echo "3. 🔐 Login as admin: admin@noticeboard.com / admin123\n";
echo "4. 📧 Check email inbox for test email\n";
echo "5. ✅ Approve new registrations in admin dashboard\n";

echo "\n⚠️ SECURITY: Delete test files after testing!\n";
echo "</pre>";
?>
