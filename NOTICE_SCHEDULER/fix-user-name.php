<?php
/**
 * Fix User Name Issue
 * Check and fix the user with name "0"
 */

require_once 'config/config.php';

echo "<h1>🔧 Fix User Name Issue</h1>";
echo "<pre>";

$conn = getDBConnection();

// Find users with name "0"
echo "=== Finding Users with Name '0' ===\n";
$result = $conn->query("SELECT user_id, name, email, created_at FROM users WHERE name = '0'");

if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " user(s) with name '0':\n\n";
    
    while ($row = $result->fetch_assoc()) {
        echo "User ID: {$row['user_id']}\n";
        echo "Email: {$row['email']}\n";
        echo "Created: {$row['created_at']}\n";
        echo "\n";
        
        // Try to extract name from email
        $email = $row['email'];
        $emailParts = explode('@', $email);
        $localPart = $emailParts[0];
        
        // Try to create a readable name from email
        // Example: s23_kulkarni_sarvesh -> Sarvesh Kulkarni
        $nameParts = explode('_', $localPart);
        
        if (count($nameParts) >= 3) {
            // Format: s23_lastname_firstname
            $firstName = ucfirst(strtolower($nameParts[2]));
            $lastName = ucfirst(strtolower($nameParts[1]));
            $suggestedName = "$firstName $lastName";
        } else {
            // Just capitalize the local part
            $suggestedName = ucfirst(strtolower(str_replace('_', ' ', $localPart)));
        }
        
        echo "Suggested name from email: {$suggestedName}\n";
        echo "---\n\n";
        
        // Update the user
        $stmt = $conn->prepare("UPDATE users SET name = ? WHERE user_id = ?");
        $stmt->bind_param("si", $suggestedName, $row['user_id']);
        
        if ($stmt->execute()) {
            echo "✅ Updated user {$row['user_id']} name to: {$suggestedName}\n\n";
        } else {
            echo "❌ Failed to update user {$row['user_id']}\n\n";
        }
        $stmt->close();
    }
} else {
    echo "✅ No users found with name '0'\n\n";
}

// Show all users
echo "=== All Users ===\n";
$result = $conn->query("SELECT user_id, name, email, is_verified, is_active FROM users WHERE role_id != 1 ORDER BY created_at DESC LIMIT 10");

while ($row = $result->fetch_assoc()) {
    $status = $row['is_verified'] ? ($row['is_active'] ? '✅ Active' : '⏳ Pending') : '❌ Not Verified';
    echo "ID {$row['user_id']}: {$row['name']} ({$row['email']}) - {$status}\n";
}

closeDBConnection($conn);

echo "\n=== 🎯 NEXT STEPS ===\n";
echo "1. Check if names are now correct above\n";
echo "2. Try registering a new user\n";
echo "3. Check admin dashboard > Pending Approvals\n";
echo "4. Names should now appear correctly\n";
echo "\n";
echo "⚠️ If new registrations still show '0' as name:\n";
echo "   - Check browser console for JavaScript errors\n";
echo "   - Verify the 'name' field is being filled in the form\n";
echo "   - Check api/register.php is receiving the name parameter\n";
echo "</pre>";
?>
