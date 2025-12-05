<?php
// EMERGENCY FIX - Reset Admin Password
// Run this file ONCE then delete it

require_once __DIR__ . '/config/database.php';

echo "<h1>Emergency Admin Password Fix</h1>";
echo "<hr>";

$conn = getDBConnection();

if (!$conn) {
    die("<p style='color:red;'>ERROR: Cannot connect to database. Check if MySQL is running.</p>");
}

echo "<p style='color:green;'>✓ Database connected</p>";

// Create correct password hash
$email = 'admin@noticeboard.com';
$password = 'admin123';
$correctHash = password_hash($password, PASSWORD_BCRYPT);

echo "<p>Email: <strong>$email</strong></p>";
echo "<p>Password: <strong>$password</strong></p>";
echo "<p>New Hash: <strong>" . substr($correctHash, 0, 30) . "...</strong></p>";
echo "<hr>";

// Check if admin exists
$stmt = $conn->prepare("SELECT user_id, password_hash FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Admin exists - update password
    $admin = $result->fetch_assoc();
    echo "<p>Admin user found (ID: {$admin['user_id']})</p>";
    echo "<p>Old hash: " . substr($admin['password_hash'], 0, 30) . "...</p>";
    
    $stmt->close();
    
    // Update with correct hash
    $stmt = $conn->prepare("UPDATE users SET password_hash = ?, is_verified = 1, is_active = 1 WHERE email = ?");
    $stmt->bind_param("ss", $correctHash, $email);
    
    if ($stmt->execute()) {
        echo "<h2 style='color:green;'>✓ SUCCESS!</h2>";
        echo "<p>Admin password has been reset successfully!</p>";
        echo "<p><strong>You can now login with:</strong></p>";
        echo "<p>Email: admin@noticeboard.com</p>";
        echo "<p>Password: admin123</p>";
    } else {
        echo "<p style='color:red;'>✗ Failed to update password: " . $stmt->error . "</p>";
    }
} else {
    // Admin doesn't exist - create it
    echo "<p style='color:orange;'>Admin user not found. Creating new admin...</p>";
    $stmt->close();
    
    // Get admin role ID
    $roleResult = $conn->query("SELECT role_id FROM roles WHERE role_name = 'Admin'");
    if ($roleResult->num_rows === 0) {
        die("<p style='color:red;'>ERROR: Admin role not found in database. Please re-import schema.sql</p>");
    }
    $roleId = $roleResult->fetch_assoc()['role_id'];
    
    // Create admin user
    $name = 'System Admin';
    $stmt = $conn->prepare("INSERT INTO users (email, password_hash, name, role_id, is_verified, is_active) VALUES (?, ?, ?, ?, 1, 1)");
    $stmt->bind_param("sssi", $email, $correctHash, $name, $roleId);
    
    if ($stmt->execute()) {
        echo "<h2 style='color:green;'>✓ SUCCESS!</h2>";
        echo "<p>Admin user created successfully!</p>";
        echo "<p><strong>You can now login with:</strong></p>";
        echo "<p>Email: admin@noticeboard.com</p>";
        echo "<p>Password: admin123</p>";
    } else {
        echo "<p style='color:red;'>✗ Failed to create admin: " . $stmt->error . "</p>";
    }
}

$stmt->close();
closeDBConnection($conn);

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><a href='index.html'><strong>Go to Login Page</strong></a></li>";
echo "<li>Login with: admin@noticeboard.com / admin123</li>";
echo "<li><strong style='color:red;'>DELETE THIS FILE (fix-admin-password.php) for security!</strong></li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>Test Password Verification:</strong></p>";
$testHash = password_hash('admin123', PASSWORD_BCRYPT);
if (password_verify('admin123', $testHash)) {
    echo "<p style='color:green;'>✓ Password hashing is working correctly on your server</p>";
} else {
    echo "<p style='color:red;'>✗ Password hashing NOT working - PHP configuration issue</p>";
}
?>
