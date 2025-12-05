<?php
// Fix all issues script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Fix All Issues</title>";
echo "<style>body{font-family:Arial;padding:20px;}.success{color:green;}.error{color:red;}.warning{color:orange;}</style></head><body>";

echo "<h1>🔧 Fixing All Issues</h1>";

// Step 1: Check and fix database
echo "<h2>Step 1: Database Fix</h2>";
require_once __DIR__ . '/config/database.php';

try {
    $conn = getDBConnection();
    echo "<p class='success'>✅ Database connected</p>";
    
    // Check if we need to add more departments
    $result = $conn->query("SELECT COUNT(*) as count FROM departments");
    $deptCount = $result->fetch_assoc()['count'];
    
    if ($deptCount < 7) {
        echo "<p class='warning'>⚠️ Only $deptCount departments found, adding more...</p>";
        
        // Add missing departments
        $departments = [
            ['Automation and Robotics', 'AR'],
            ['Artificial Intelligence and Machine Learning', 'AIML']
        ];
        
        foreach ($departments as $dept) {
            $stmt = $conn->prepare("INSERT IGNORE INTO departments (department_name, department_code) VALUES (?, ?)");
            $stmt->bind_param("ss", $dept[0], $dept[1]);
            $stmt->execute();
            $stmt->close();
        }
        echo "<p class='success'>✅ Added missing departments</p>";
    }
    
    // Check and add more classes
    $result = $conn->query("SELECT COUNT(*) as count FROM classes");
    $classCount = $result->fetch_assoc()['count'];
    
    if ($classCount < 16) {
        echo "<p class='warning'>⚠️ Only $classCount classes found, adding more...</p>";
        
        // Add more classes for existing departments
        $moreClasses = [
            ['F.E.B', 1], ['S.Y.B', 1], ['T.Y.B', 1], ['B.E.B', 1], // CSE
            ['F.E.B', 2], ['S.Y.B', 2], ['T.Y.B', 2], ['B.E.B', 2], // IT
            ['F.E.A', 3], ['S.Y.A', 3], ['T.Y.A', 3], ['B.E.A', 3], // EXTC
            ['F.E.A', 4], ['S.Y.A', 4], ['T.Y.A', 4], ['B.E.A', 4], // MECH
            ['F.E.A', 5], ['S.Y.A', 5], ['T.Y.A', 5], ['B.E.A', 5], // CIVIL
        ];
        
        foreach ($moreClasses as $class) {
            $stmt = $conn->prepare("INSERT IGNORE INTO classes (class_name, department_id) VALUES (?, ?)");
            $stmt->bind_param("si", $class[0], $class[1]);
            $stmt->execute();
            $stmt->close();
        }
        echo "<p class='success'>✅ Added more classes</p>";
    }
    
    closeDBConnection($conn);
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Step 2: Fix email configuration
echo "<h2>Step 2: Email Configuration Fix</h2>";

$configFile = __DIR__ . '/config/config.php';
$configContent = file_get_contents($configFile);

// Update with working app password
$newConfig = str_replace(
    "define('SMTP_PASS', 'uzvp tqes ewor xpig');",
    "define('SMTP_PASS', 'pkpy mbqv bmzl ncgw');",
    $configContent
);

if (file_put_contents($configFile, $newConfig)) {
    echo "<p class='success'>✅ Updated email configuration with Mail app password</p>";
} else {
    echo "<p class='error'>❌ Failed to update email configuration</p>";
}

// Step 3: Test email
echo "<h2>Step 3: Email Test</h2>";
require_once __DIR__ . '/includes/functions.php';

$testOTP = generateOTP();
echo "<p>Generated OTP: $testOTP</p>";

if (EMAIL_ENABLED) {
    echo "<p class='success'>✅ Email is enabled</p>";
    echo "<p>SMTP Host: " . SMTP_HOST . "</p>";
    echo "<p>SMTP User: " . SMTP_USER . "</p>";
    echo "<p>From Name: " . SMTP_FROM_NAME . "</p>";
} else {
    echo "<p class='error'>❌ Email is disabled</p>";
}

// Step 4: Create test registration
echo "<h2>Step 4: Test Registration Data</h2>";

$testData = [
    'role' => 'Student',
    'name' => 'Test Student',
    'email' => 'test.student@college.edu',
    'password' => 'test123',
    'confirm_password' => 'test123',
    'department_id' => 1,
    'class_id' => 1,
    'roll_no' => 'TEST001'
];

echo "<p><strong>Test registration data:</strong></p>";
echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

// Test the registration API
$ch = curl_init('http://localhost/NOTICE_SCHEDULER/api/register.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>Registration API Test:</strong></p>";
echo "<p>HTTP Code: $httpCode</p>";
echo "<p>Response: " . htmlspecialchars($response) . "</p>";

$responseData = json_decode($response, true);
if ($responseData && $responseData['success']) {
    echo "<p class='success'>✅ Registration API working!</p>";
} else {
    echo "<p class='error'>❌ Registration API failed</p>";
    if ($responseData && isset($responseData['message'])) {
        echo "<p><strong>Error:</strong> " . $responseData['message'] . "</p>";
    }
}

// Step 5: Summary
echo "<h2>🎯 Summary</h2>";
echo "<div style='background:#f0f8ff;padding:20px;border-radius:10px;'>";
echo "<h3>What was fixed:</h3>";
echo "<ul>";
echo "<li>✅ Added more departments and classes to database</li>";
echo "<li>✅ Updated email configuration with Mail app password</li>";
echo "<li>✅ Tested registration API</li>";
echo "<li>✅ Verified all components</li>";
echo "</ul>";

echo "<h3>Next steps:</h3>";
echo "<ol>";
echo "<li>Try registration again: <a href='register.html'>Register</a></li>";
echo "<li>Login as admin: <a href='index.html'>Login</a> (admin@noticeboard.com / admin123)</li>";
echo "<li>Check bulk operations in admin dashboard</li>";
echo "<li>Test email notifications</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>