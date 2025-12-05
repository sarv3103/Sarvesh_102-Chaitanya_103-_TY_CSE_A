<?php
// Test registration after database fix
require_once 'config/config.php';

echo "🧪 Testing Registration System\n";
echo "==============================\n";

// Test data
$testData = [
    'role' => 'Student',
    'name' => 'Test Student',
    'email' => 'test.student.' . time() . '@college.edu',
    'password' => 'test123',
    'confirm_password' => 'test123',
    'department_id' => 1,
    'class_id' => 1,
    'roll_no' => 'CSE' . time()
];

echo "📝 Test Data:\n";
foreach ($testData as $key => $value) {
    echo "  $key: $value\n";
}
echo "\n";

// Make API call
$url = 'http://localhost/NOTICE_SCHEDULER/api/register.php';
$postData = json_encode($testData);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $postData
    ]
]);

echo "🚀 Making registration request...\n";
$response = file_get_contents($url, false, $context);
$httpCode = $http_response_header[0];

echo "📊 Response:\n";
echo "HTTP Status: $httpCode\n";
echo "Response Body: $response\n\n";

// Parse response
$responseData = json_decode($response, true);
if ($responseData) {
    if ($responseData['success']) {
        echo "✅ Registration successful!\n";
        if (isset($responseData['otp'])) {
            echo "🔑 OTP for testing: " . $responseData['otp'] . "\n";
        }
    } else {
        echo "❌ Registration failed: " . $responseData['message'] . "\n";
    }
} else {
    echo "❌ Invalid JSON response\n";
}

echo "\n🔍 Checking database for departments and classes...\n";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check department-class combination
    $stmt = $pdo->prepare("SELECT d.department_name, c.class_name FROM departments d JOIN classes c ON d.department_id = c.department_id WHERE d.department_id = ? AND c.class_id = ?");
    $stmt->execute([1, 1]);
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✅ Department-Class combination valid: {$result['department_name']} - {$result['class_name']}\n";
    } else {
        echo "❌ Department-Class combination invalid\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?>