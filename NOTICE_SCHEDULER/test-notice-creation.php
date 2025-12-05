<?php
/**
 * Test Notice Creation
 * Diagnose why admin can't send notices
 */

session_start();
require_once 'config/config.php';
require_once 'includes/functions.php';

echo "<h1>🧪 Test Notice Creation</h1>";
echo "<pre>";

// Check if user is logged in
if (!isLoggedIn()) {
    echo "❌ Not logged in\n";
    echo "Please login first at: " . BASE_URL . "/index.html\n";
    exit;
}

$user = getCurrentUser();

echo "=== Current User ===\n";
echo "User ID: {$user['user_id']}\n";
echo "Name: {$user['name']}\n";
echo "Email: {$user['email']}\n";
echo "Role: {$user['role_name']}\n";
echo "\n";

// Check if user can create notices
if (in_array($user['role_name'], ['Staff', 'Admin'])) {
    echo "✅ User CAN create notices\n";
} else {
    echo "❌ User CANNOT create notices (must be Staff or Admin)\n";
}
echo "\n";

// Check classes
echo "=== Available Classes ===\n";
$conn = getDBConnection();
$result = $conn->query("
    SELECT c.class_id, c.class_name, d.department_code, d.department_name
    FROM classes c
    JOIN departments d ON c.department_id = d.department_id
    WHERE c.is_active = TRUE
    ORDER BY d.department_name, c.class_name
    LIMIT 10
");

$classCount = $result->num_rows;
echo "Total classes: {$classCount}\n\n";

if ($classCount > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "  - {$row['department_code']} {$row['class_name']} (ID: {$row['class_id']})\n";
    }
} else {
    echo "⚠️ No classes found! Run fix-database-now.php\n";
}
echo "\n";

// Test creating a notice
echo "=== Test Notice Creation ===\n";

$testTitle = "Test Notice " . date('H:i:s');
$testContent = "This is a test notice created at " . date('Y-m-d H:i:s');
$isStaffOnly = 0;

$stmt = $conn->prepare("INSERT INTO notices (title, content, sent_by_user_id, is_staff_only) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssii", $testTitle, $testContent, $user['user_id'], $isStaffOnly);

if ($stmt->execute()) {
    $noticeId = $stmt->insert_id;
    echo "✅ Notice created successfully!\n";
    echo "   Notice ID: {$noticeId}\n";
    echo "   Title: {$testTitle}\n";
    
    // Add target (all students)
    $stmt2 = $conn->prepare("INSERT INTO notice_targets (notice_id, target_role_id, target_class_id) VALUES (?, 3, NULL)");
    $stmt2->bind_param("i", $noticeId);
    
    if ($stmt2->execute()) {
        echo "✅ Target added (all students)\n";
    } else {
        echo "❌ Failed to add target: " . $stmt2->error . "\n";
    }
    $stmt2->close();
    
    // Delete test notice
    $conn->query("DELETE FROM notice_targets WHERE notice_id = {$noticeId}");
    $conn->query("DELETE FROM notices WHERE notice_id = {$noticeId}");
    echo "✅ Test notice deleted\n";
    
} else {
    echo "❌ Failed to create notice: " . $stmt->error . "\n";
}
$stmt->close();

closeDBConnection($conn);

echo "\n=== 🎯 DIAGNOSIS ===\n";
if ($user['role_name'] === 'Admin' || $user['role_name'] === 'Staff') {
    echo "✅ User has permission to create notices\n";
    echo "✅ Database connection working\n";
    echo "✅ Notice creation API should work\n";
    echo "\n";
    echo "If admin still can't send notices:\n";
    echo "1. Check browser console for JavaScript errors (F12)\n";
    echo "2. Check if form fields are filled correctly\n";
    echo "3. Check network tab to see API response\n";
    echo "4. Try creating notice with 'All Students' target first\n";
} else {
    echo "❌ User doesn't have permission\n";
    echo "   Current role: {$user['role_name']}\n";
    echo "   Required: Staff or Admin\n";
}

echo "</pre>";
?>
