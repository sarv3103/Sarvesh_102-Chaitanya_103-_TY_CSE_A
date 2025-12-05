<?php
// Login Diagnostic Tool
// This will tell you EXACTLY why login is failing

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h1>🔍 Login Diagnostic Tool</h1>";
echo "<hr>";

// Test 1: Database Connection
echo "<h2>Test 1: Database Connection</h2>";
$conn = getDBConnection();
if ($conn) {
    echo "<p style='color:green;'>✓ Database connected successfully</p>";
    $result = $conn->query("SELECT DATABASE()");
    $dbName = $result->fetch_row()[0];
    echo "<p>Database: <strong>$dbName</strong></p>";
} else {
    echo "<p style='color:red;'>✗ Database connection FAILED</p>";
    echo "<p><strong>Fix:</strong> Check if MySQL is running in XAMPP</p>";
    die();
}

// Test 2: Check if users table exists
echo "<hr><h2>Test 2: Users Table</h2>";
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows > 0) {
    echo "<p style='color:green;'>✓ Users table exists</p>";
} else {
    echo "<p style='color:red;'>✗ Users table NOT found</p>";
    echo "<p><strong>Fix:</strong> Import database/schema.sql in phpMyAdmin</p>";
    die();
}

// Test 3: Check admin user
echo "<hr><h2>Test 3: Admin User</h2>";
$email = 'admin@noticeboard.com';
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "<p style='color:green;'>✓ Admin user exists</p>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    echo "<tr><td>User ID</td><td>{$admin['user_id']}</td></tr>";
    echo "<tr><td>Email</td><td>{$admin['email']}</td></tr>";
    echo "<tr><td>Name</td><td>{$admin['name']}</td></tr>";
    echo "<tr><td>Role ID</td><td>{$admin['role_id']}</td></tr>";
    echo "<tr><td>Verified</td><td>" . ($admin['is_verified'] ? 'Yes' : 'No') . "</td></tr>";
    echo "<tr><td>Active</td><td>" . ($admin['is_active'] ? 'Yes' : 'No') . "</td></tr>";
    echo "<tr><td>Password Hash</td><td>" . substr($admin['password_hash'], 0, 40) . "...</td></tr>";
    echo "</table>";
    
    if (!$admin['is_verified']) {
        echo "<p style='color:red;'>⚠ Admin is NOT verified</p>";
    }
    if (!$admin['is_active']) {
        echo "<p style='color:red;'>⚠ Admin is NOT active</p>";
    }
} else {
    echo "<p style='color:red;'>✗ Admin user NOT found</p>";
    echo "<p><strong>Fix:</strong> Run <a href='fix-admin-password.php'>fix-admin-password.php</a></p>";
    die();
}
$stmt->close();

// Test 4: Password Hash Test
echo "<hr><h2>Test 4: Password Verification Test</h2>";
$testPassword = 'admin123';
$currentHash = $admin['password_hash'];

echo "<p>Testing password: <strong>$testPassword</strong></p>";
echo "<p>Against hash: <strong>" . substr($currentHash, 0, 40) . "...</strong></p>";

if (password_verify($testPassword, $currentHash)) {
    echo "<p style='color:green;'>✓ Password verification SUCCESSFUL!</p>";
    echo "<p><strong>This means login SHOULD work!</strong></p>";
} else {
    echo "<p style='color:red;'>✗ Password verification FAILED!</p>";
    echo "<p><strong>This is why login is not working!</strong></p>";
    echo "<p><strong>Fix:</strong> Run <a href='fix-admin-password.php'>fix-admin-password.php</a> to reset password</p>";
}

// Test 5: Create new hash and test
echo "<hr><h2>Test 5: Generate New Hash</h2>";
$newHash = password_hash($testPassword, PASSWORD_BCRYPT);
echo "<p>New hash generated: <strong>" . substr($newHash, 0, 40) . "...</strong></p>";

if (password_verify($testPassword, $newHash)) {
    echo "<p style='color:green;'>✓ New hash works correctly</p>";
    echo "<p>Your PHP password functions are working fine</p>";
} else {
    echo "<p style='color:red;'>✗ New hash verification failed</p>";
    echo "<p>There may be a PHP configuration issue</p>";
}

// Test 6: Check roles table
echo "<hr><h2>Test 6: Roles Table</h2>";
$result = $conn->query("SELECT * FROM roles");
if ($result->num_rows > 0) {
    echo "<p style='color:green;'>✓ Roles table has data</p>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Role ID</th><th>Role Name</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['role_id']}</td><td>{$row['role_name']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>✗ Roles table is empty</p>";
}

// Test 7: Test login API directly
echo "<hr><h2>Test 7: Simulate Login</h2>";
echo "<p>Simulating login with admin@noticeboard.com / admin123...</p>";

$loginEmail = 'admin@noticeboard.com';
$loginPassword = 'admin123';

$stmt = $conn->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.email = ?");
$stmt->bind_param("s", $loginEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p style='color:red;'>✗ User not found</p>";
} else {
    $user = $result->fetch_assoc();
    echo "<p style='color:green;'>✓ User found</p>";
    
    if (password_verify($loginPassword, $user['password_hash'])) {
        echo "<p style='color:green;'>✓ Password correct</p>";
        
        if (!$user['is_verified']) {
            echo "<p style='color:red;'>✗ User not verified</p>";
        } elseif (!$user['is_active']) {
            echo "<p style='color:red;'>✗ User not active</p>";
        } else {
            echo "<p style='color:green;'>✓ User verified and active</p>";
            echo "<h3 style='color:green;'>✓✓✓ LOGIN SHOULD WORK! ✓✓✓</h3>";
        }
    } else {
        echo "<p style='color:red;'>✗ Password incorrect</p>";
        echo "<h3 style='color:red;'>THIS IS THE PROBLEM!</h3>";
        echo "<p><strong>Solution:</strong> Run <a href='fix-admin-password.php'><strong>fix-admin-password.php</strong></a></p>";
    }
}
$stmt->close();

closeDBConnection($conn);

echo "<hr>";
echo "<h2>Summary & Next Steps</h2>";
echo "<ol>";
echo "<li>If password verification failed: <a href='fix-admin-password.php'><strong>Run fix-admin-password.php</strong></a></li>";
echo "<li>If all tests passed: <a href='index.html'><strong>Try logging in again</strong></a></li>";
echo "<li>If still not working: Check browser console (F12) for JavaScript errors</li>";
echo "</ol>";

echo "<hr>";
echo "<p style='color:orange;'><strong>SECURITY:</strong> Delete this file after fixing the issue!</p>";
?>
