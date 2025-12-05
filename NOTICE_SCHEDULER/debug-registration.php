<?php
// Comprehensive registration debugging script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Registration Debug</title>";
echo "<style>
body { font-family: Arial; padding: 20px; }
.success { color: green; }
.error { color: red; }
.warning { color: orange; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background: #f2f2f2; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
</style></head><body>";

echo "<h1>🔍 Registration System Debug</h1>";

// Step 1: Check database connection
echo "<h2>Step 1: Database Connection</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    $conn = getDBConnection();
    echo "<p class='success'>✅ Database connected successfully</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "</body></html>";
    exit;
}

// Step 2: Check required tables
echo "<h2>Step 2: Check Required Tables</h2>";
$tables = ['roles', 'departments', 'classes', 'users', 'otp_tokens'];
$allTablesExist = true;
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<p class='success'>✅ Table '$table' exists</p>";
    } else {
        echo "<p class='error'>❌ Table '$table' NOT FOUND</p>";
        $allTablesExist = false;
    }
}

if (!$allTablesExist) {
    echo "<p class='error'><strong>SOLUTION:</strong> Import database/schema.sql via phpMyAdmin</p>";
}

// Step 3: Check roles
echo "<h2>Step 3: Check Roles</h2>";
$result = $conn->query("SELECT * FROM roles");
if ($result->num_rows > 0) {
    echo "<table><tr><th>Role ID</th><th>Role Name</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['role_id']}</td><td>{$row['role_name']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ No roles found! Import schema.sql</p>";
}

// Step 4: Check departments
echo "<h2>Step 4: Check Departments</h2>";
$result = $conn->query("SELECT * FROM departments WHERE is_active = TRUE");
if ($result->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Name</th><th>Code</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['department_id']}</td><td>{$row['department_name']}</td><td>{$row['department_code']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ No departments found! Import schema.sql</p>";
}

// Step 5: Check classes
echo "<h2>Step 5: Check Classes</h2>";
$result = $conn->query("SELECT c.*, d.department_name FROM classes c JOIN departments d ON c.department_id = d.department_id WHERE c.is_active = TRUE");
if ($result->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Class Name</th><th>Department</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['class_id']}</td><td>{$row['class_name']}</td><td>{$row['department_name']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ No classes found! Import schema.sql</p>";
}

// Step 6: Check functions.php
echo "<h2>Step 6: Check Functions</h2>";
try {
    require_once __DIR__ . '/includes/functions.php';
    echo "<p class='success'>✅ functions.php loaded</p>";
    
    $functions = [
        'generateOTP',
        'sendOTPEmail',
        'sendRegistrationConfirmationEmail',
        'sendApprovalEmail',
        'hashPassword',
        'verifyPassword',
        'isValidEmail',
        'sanitizeInput',
        'jsonResponse',
        'getDBConnection'
    ];
    
    echo "<table><tr><th>Function</th><th>Status</th></tr>";
    foreach ($functions as $func) {
        $exists = function_exists($func);
        $status = $exists ? "<span class='success'>✅ Exists</span>" : "<span class='error'>❌ Missing</span>";
        echo "<tr><td>$func()</td><td>$status</td></tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error loading functions.php: " . $e->getMessage() . "</p>";
}

// Step 7: Test Student Registration
echo "<h2>Step 7: Test Student Registration</h2>";
$testEmail = 'test.student.' . time() . '@college.edu';
$studentData = [
    'role' => 'Student',
    'name' => 'Test Student',
    'email' => $testEmail,
    'password' => 'test123',
    'confirm_password' => 'test123',
    'department_id' => 1,
    'class_id' => 1,
    'roll_no' => 'TEST001'
];

echo "<p><strong>Test Data:</strong></p>";
echo "<pre>" . json_encode($studentData, JSON_PRETTY_PRINT) . "</pre>";

$ch = curl_init('http://localhost/NOTICE_SCHEDULER/api/register.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($studentData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
echo "<p><strong>Response:</strong></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

$responseData = json_decode($response, true);
if ($responseData && $responseData['success']) {
    echo "<p class='success'>✅ Student registration successful!</p>";
} else {
    echo "<p class='error'>❌ Student registration failed</p>";
    if ($responseData && isset($responseData['message'])) {
        echo "<p><strong>Error:</strong> " . $responseData['message'] . "</p>";
    }
}

// Step 8: Test Staff Registration
echo "<h2>Step 8: Test Staff Registration</h2>";
$testEmail = 'test.staff.' . time() . '@college.edu';
$staffData = [
    'role' => 'Staff',
    'name' => 'Test Staff',
    'email' => $testEmail,
    'password' => 'test123',
    'confirm_password' => 'test123',
    'department_id' => 1
];

echo "<p><strong>Test Data:</strong></p>";
echo "<pre>" . json_encode($staffData, JSON_PRETTY_PRINT) . "</pre>";

$ch = curl_init('http://localhost/NOTICE_SCHEDULER/api/register.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($staffData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
echo "<p><strong>Response:</strong></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

$responseData = json_decode($response, true);
if ($responseData && $responseData['success']) {
    echo "<p class='success'>✅ Staff registration successful!</p>";
} else {
    echo "<p class='error'>❌ Staff registration failed</p>";
    if ($responseData && isset($responseData['message'])) {
        echo "<p><strong>Error:</strong> " . $responseData['message'] . "</p>";
    }
}

// Step 9: Check recent registrations
echo "<h2>Step 9: Recent Test Registrations</h2>";
$result = $conn->query("SELECT user_id, email, name, role_id, is_verified, is_active, created_at FROM users WHERE email LIKE 'test.%@college.edu' ORDER BY created_at DESC LIMIT 5");
if ($result->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Email</th><th>Name</th><th>Role ID</th><th>Verified</th><th>Active</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['role_id']}</td>";
        echo "<td>" . ($row['is_verified'] ? 'Yes' : 'No') . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No test registrations found</p>";
}

closeDBConnection($conn);

// Summary
echo "<hr><h2>📋 Summary</h2>";
echo "<p>If all tests passed, registration is working correctly!</p>";
echo "<p>If any tests failed, check the error messages above.</p>";
echo "<p><strong>Common Issues:</strong></p>";
echo "<ul>";
echo "<li>Database not imported → Import database/schema.sql</li>";
echo "<li>No departments/classes → Import database/schema.sql</li>";
echo "<li>bind_param errors → Check parameter types match</li>";
echo "<li>Email errors → Check config/config.php SMTP settings</li>";
echo "</ul>";

echo "</body></html>";
?>
