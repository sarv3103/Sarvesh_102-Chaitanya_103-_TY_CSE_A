<?php
// Test script to check departments and classes in database
require_once __DIR__ . '/config/database.php';

echo "<h2>Testing Departments and Classes</h2>";

$conn = getDBConnection();

// Check departments
echo "<h3>Departments:</h3>";
$result = $conn->query("SELECT * FROM departments ORDER BY department_name");
if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Code</th><th>Active</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['department_id'] . "</td>";
        echo "<td>" . $row['department_name'] . "</td>";
        echo "<td>" . $row['department_code'] . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No departments found in database!</p>";
    echo "<p><strong>Solution:</strong> You need to import the database schema.</p>";
    echo "<p>1. Open phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></p>";
    echo "<p>2. Click 'Import' tab</p>";
    echo "<p>3. Choose file: <code>C:\\xampp\\htdocs\\NOTICE_SCHEDULER\\database\\schema.sql</code></p>";
    echo "<p>4. Click 'Go' button</p>";
}

// Check classes
echo "<h3>Classes:</h3>";
$result = $conn->query("SELECT c.*, d.department_name FROM classes c JOIN departments d ON c.department_id = d.department_id ORDER BY d.department_name, c.class_name");
if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Class Name</th><th>Department</th><th>Active</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['class_id'] . "</td>";
        echo "<td>" . $row['class_name'] . "</td>";
        echo "<td>" . $row['department_name'] . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No classes found in database!</p>";
}

// Test API endpoints
echo "<h3>Testing API Endpoints:</h3>";

echo "<h4>1. GET api/get-departments.php</h4>";
$apiUrl = 'http://localhost/NOTICE_SCHEDULER/api/get-departments.php';
echo "<p>URL: <a href='$apiUrl' target='_blank'>$apiUrl</a></p>";

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

echo "<h4>2. GET api/get-classes-by-department.php?department_id=1</h4>";
$apiUrl = 'http://localhost/NOTICE_SCHEDULER/api/get-classes-by-department.php?department_id=1';
echo "<p>URL: <a href='$apiUrl' target='_blank'>$apiUrl</a></p>";

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

closeDBConnection($conn);

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>If no departments shown above, import the database schema from <code>database/schema.sql</code></li>";
echo "<li>After import, run <code>fix-admin-password.php</code> to create admin user</li>";
echo "<li>Login as admin and create/manage departments and classes</li>";
echo "<li>Test registration page - departments should now load</li>";
echo "</ol>";
?>
