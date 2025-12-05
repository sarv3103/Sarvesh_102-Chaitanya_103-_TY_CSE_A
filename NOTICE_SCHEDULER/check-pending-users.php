<?php
require_once 'config/database.php';

echo "<h1>Check Pending Users</h1>";
echo "<pre>";

$conn = getDBConnection();

// Check all users
echo "=== ALL USERS ===\n";
$result = $conn->query("SELECT user_id, name, email, role_id, is_verified, is_active FROM users ORDER BY user_id");
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['user_id']} | {$row['name']} | {$row['email']} | Role: {$row['role_id']} | Verified: " . ($row['is_verified'] ? 'YES' : 'NO') . " | Active: " . ($row['is_active'] ? 'YES' : 'NO') . "\n";
}

// Check pending users (verified but not active)
echo "\n=== PENDING USERS (verified=1, active=0) ===\n";
$result = $conn->query("SELECT user_id, name, email, role_id, is_verified, is_active FROM users WHERE is_verified = 1 AND is_active = 0");
$count = 0;
while ($row = $result->fetch_assoc()) {
    $count++;
    echo "ID: {$row['user_id']} | {$row['name']} | {$row['email']} | Role: {$row['role_id']}\n";
}

if ($count == 0) {
    echo "No pending users found!\n";
    echo "\nTo test pending approvals:\n";
    echo "1. Register a new user at: http://localhost/NOTICE_SCHEDULER/register.html\n";
    echo "2. Verify the OTP from email\n";
    echo "3. User will appear in pending approvals\n";
} else {
    echo "\nFound {$count} pending user(s)\n";
}

// Check unverified users
echo "\n=== UNVERIFIED USERS (verified=0) ===\n";
$result = $conn->query("SELECT user_id, name, email, is_verified, is_active FROM users WHERE is_verified = 0");
$count = 0;
while ($row = $result->fetch_assoc()) {
    $count++;
    echo "ID: {$row['user_id']} | {$row['name']} | {$row['email']}\n";
}
echo "Found {$count} unverified user(s)\n";

closeDBConnection($conn);

echo "</pre>";
?>
