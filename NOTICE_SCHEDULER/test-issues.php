<?php
/**
 * Test Script for Current Issues
 * Tests: Email names, Pending users, Bulk operations
 */

require_once 'config/config.php';
require_once 'includes/functions.php';

echo "<h1>🧪 Testing Current Issues</h1>";
echo "<pre>";

$conn = getDBConnection();

// Test 1: Check if users have names
echo "=== TEST 1: Check User Names ===\n";
$result = $conn->query("SELECT user_id, name, email, is_verified, is_active FROM users WHERE role_id != 1 LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo "User ID {$row['user_id']}: {$row['name']} ({$row['email']})\n";
    echo "  - Verified: " . ($row['is_verified'] ? 'YES' : 'NO') . "\n";
    echo "  - Active: " . ($row['is_active'] ? 'YES' : 'NO') . "\n";
}
echo "\n";

// Test 2: Check pending users (verified but not active)
echo "=== TEST 2: Pending Users (Verified but Not Active) ===\n";
$result = $conn->query("
    SELECT u.user_id, u.name, u.email, u.is_verified, u.is_active, r.role_name, 
           c.class_name, d.department_name, u.roll_no, u.created_at
    FROM users u
    JOIN roles r ON u.role_id = r.role_id
    LEFT JOIN classes c ON u.class_id = c.class_id
    LEFT JOIN departments d ON u.department_id = d.department_id
    WHERE u.is_verified = 1 AND u.is_active = 0
    ORDER BY u.created_at DESC
");

$pendingCount = $result->num_rows;
echo "Found {$pendingCount} pending user(s)\n\n";

if ($pendingCount > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "📋 Pending User:\n";
        echo "  - Name: {$row['name']}\n";
        echo "  - Email: {$row['email']}\n";
        echo "  - Role: {$row['role_name']}\n";
        echo "  - Department: " . ($row['department_name'] ?? 'N/A') . "\n";
        echo "  - Class: " . ($row['class_name'] ?? 'N/A') . "\n";
        echo "  - Roll No: " . ($row['roll_no'] ?? 'N/A') . "\n";
        echo "  - Registered: {$row['created_at']}\n";
        echo "\n";
    }
} else {
    echo "✅ No pending users found\n";
    echo "To test:\n";
    echo "  1. Register a new user at: " . BASE_URL . "/register.html\n";
    echo "  2. Verify OTP from email\n";
    echo "  3. User will appear as pending\n";
}
echo "\n";

// Test 3: Check classes for bulk operations
echo "=== TEST 3: Classes for Bulk Operations ===\n";
$result = $conn->query("
    SELECT c.class_id, c.class_name, d.department_name, d.department_code,
           COUNT(u.user_id) as student_count
    FROM classes c
    JOIN departments d ON c.department_id = d.department_id
    LEFT JOIN users u ON c.class_id = u.class_id AND u.role_id = 3
    GROUP BY c.class_id
    ORDER BY d.department_name, c.class_name
    LIMIT 10
");

echo "Available classes:\n";
while ($row = $result->fetch_assoc()) {
    echo "  - {$row['department_code']} {$row['class_name']}: {$row['student_count']} students\n";
}
echo "\n";

// Test 4: Test email function with name
echo "=== TEST 4: Test Email Function ===\n";
$testName = "Test User";
$testEmail = SMTP_USER;

echo "Testing sendApplicationSubmittedEmail()...\n";
echo "  - Name: {$testName}\n";
echo "  - Email: {$testEmail}\n";

if (EMAIL_ENABLED) {
    if (sendApplicationSubmittedEmail($testEmail, $testName)) {
        echo "✅ Email sent successfully!\n";
        echo "📧 Check inbox: {$testEmail}\n";
        echo "   Look for: 'Application Submitted for Approval'\n";
        echo "   Should contain: 'Dear {$testName}'\n";
    } else {
        echo "❌ Email failed to send\n";
    }
} else {
    echo "⚠️ Email is disabled\n";
}
echo "\n";

// Test 5: Check admin dashboard sections
echo "=== TEST 5: Admin Dashboard Sections ===\n";
echo "Check these sections in admin dashboard:\n";
echo "  1. ⏳ Pending Approvals - Should show {$pendingCount} user(s)\n";
echo "  2. 🔄 Bulk Operations - Should show class dropdowns\n";
echo "  3. 👥 User Management - Should show all active users\n";
echo "\n";

// Test 6: Simulate API call
echo "=== TEST 6: Simulate API Response ===\n";
$result = $conn->query("
    SELECT u.*, r.role_name, c.class_name, d.department_name, d.department_code 
    FROM users u 
    JOIN roles r ON u.role_id = r.role_id 
    LEFT JOIN classes c ON u.class_id = c.class_id 
    LEFT JOIN departments d ON u.department_id = d.department_id 
    WHERE u.is_verified = 1 AND u.is_active = 0
    LIMIT 1
");

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "Sample pending user data:\n";
    echo json_encode([
        'user_id' => $user['user_id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role_name' => $user['role_name'],
        'department_name' => $user['department_name'],
        'class_name' => $user['class_name'],
        'roll_no' => $user['roll_no'],
        'created_at' => $user['created_at']
    ], JSON_PRETTY_PRINT);
} else {
    echo "No pending users to show sample data\n";
}
echo "\n";

closeDBConnection($conn);

echo "=== 🎯 SUMMARY ===\n";
echo "1. User names: Check if names appear in test above\n";
echo "2. Pending users: {$pendingCount} found\n";
echo "3. Classes: Available for bulk operations\n";
echo "4. Email: " . (EMAIL_ENABLED ? "Enabled" : "Disabled") . "\n";
echo "\n";

echo "=== 📋 NEXT STEPS ===\n";
if ($pendingCount == 0) {
    echo "1. Register a new user to test pending approvals\n";
    echo "2. Verify OTP from email\n";
    echo "3. Check admin dashboard > Pending Approvals\n";
} else {
    echo "1. Login as admin: " . BASE_URL . "/index.html\n";
    echo "2. Go to 'Pending Approvals' section\n";
    echo "3. You should see {$pendingCount} user(s) waiting\n";
    echo "4. Approve or reject them\n";
}
echo "\n";

echo "=== 🔧 TROUBLESHOOTING ===\n";
echo "If pending users not showing:\n";
echo "  - Check browser console for JavaScript errors\n";
echo "  - Check api/admin/users.php is returning data\n";
echo "  - Verify user is logged in as admin\n";
echo "\n";
echo "If bulk operations blank:\n";
echo "  - Check browser console for errors\n";
echo "  - Verify classes exist in database\n";
echo "  - Check api/admin/classes.php is working\n";
echo "\n";
echo "If email name missing:\n";
echo "  - Check user has 'name' field in database\n";
echo "  - Verify sendApplicationSubmittedEmail() receives name parameter\n";
echo "</pre>";
?>
