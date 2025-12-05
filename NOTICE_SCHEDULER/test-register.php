<?php
// Test registration API to see actual errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Registration API</h2>";

// Test data for Student
$studentData = [
    'role' => 'Student',
    'name' => 'Test Student',
    'email' => 'test.student@college.edu',
    'password' => 'test123',
    'confirm_password' => 'test123',
    'department_id' => 1,
    'class_id' => 1,
    'roll_no' => 'CS001'
];

// Test data for Staff
$staffData = [
    'role' => 'Staff',
    'name' => 'Test Staff',
    'email' => 'test.staff@college.edu',
    'password' => 'test123',
    'confirm_password' => 'test123',
    'department_id' => 1
];

echo "<h3>Test 1: Student Registration</h3>";
echo "<pre>Data: " . json_encode($studentData, JSON_PRETTY_PRINT) . "</pre>";

$ch = curl_init('http://localhost/NOTICE_SCHEDULER/api/register.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($studentData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<pre>Response: " . htmlspecialchars($response) . "</pre>";

echo "<hr>";

echo "<h3>Test 2: Staff Registration</h3>";
echo "<pre>Data: " . json_encode($staffData, JSON_PRETTY_PRINT) . "</pre>";

$ch = curl_init('http://localhost/NOTICE_SCHEDULER/api/register.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($staffData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<pre>Response: " . htmlspecialchars($response) . "</pre>";

echo "<hr>";

echo "<h3>Check Database Connection</h3>";
require_once __DIR__ . '/config/database.php';
try {
    $conn = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Check if roles exist
    $result = $conn->query("SELECT * FROM roles");
    echo "<p>Roles in database: " . $result->num_rows . "</p>";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['role_name'] . " (ID: " . $row['role_id'] . ")<br>";
    }
    
    // Check if departments exist
    $result = $conn->query("SELECT * FROM departments WHERE is_active = TRUE");
    echo "<p>Active departments: " . $result->num_rows . "</p>";
    
    // Check if classes exist
    $result = $conn->query("SELECT * FROM classes WHERE is_active = TRUE");
    echo "<p>Active classes: " . $result->num_rows . "</p>";
    
    closeDBConnection($conn);
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Check if functions.php loads correctly</h3>";
try {
    require_once __DIR__ . '/includes/functions.php';
    echo "<p style='color: green;'>✅ functions.php loaded successfully</p>";
    
    // Test if functions exist
    $functions = ['generateOTP', 'sendOTPEmail', 'sendRegistrationConfirmationEmail', 'sendApprovalEmail', 'hashPassword'];
    foreach ($functions as $func) {
        if (function_exists($func)) {
            echo "✅ $func() exists<br>";
        } else {
            echo "❌ $func() NOT FOUND<br>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error loading functions.php: " . $e->getMessage() . "</p>";
}
?>
