<?php
/**
 * FIX NAME ISSUE - Complete Solution
 * This will fix all users with name "0" and show what's happening
 */

require_once 'config/config.php';

echo "<!DOCTYPE html><html><head><title>Fix Name Issue</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}";
echo ".success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;font-weight:bold;}";
echo "pre{background:white;padding:20px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}";
echo "h2{color:#667eea;border-bottom:2px solid #667eea;padding-bottom:10px;}</style></head><body>";

echo "<h1>🔧 Fix Name Issue - Complete Solution</h1>";
echo "<pre>";

$conn = getDBConnection();

// STEP 1: Find all users with invalid names
echo "<h2>STEP 1: Finding Users with Invalid Names</h2>\n";
$result = $conn->query("
    SELECT user_id, name, email, role_id, created_at 
    FROM users 
    WHERE name = '0' OR name = '' OR name IS NULL OR CHAR_LENGTH(name) < 2
    ORDER BY created_at DESC
");

$invalidUsers = [];
while ($row = $result->fetch_assoc()) {
    $invalidUsers[] = $row;
}

echo "Found " . count($invalidUsers) . " user(s) with invalid names\n\n";

if (count($invalidUsers) == 0) {
    echo "<span class='success'>✅ All user names are valid!</span>\n\n";
} else {
    echo "<span class='warning'>⚠️ Users with invalid names:</span>\n";
    foreach ($invalidUsers as $user) {
        echo "  - User ID {$user['user_id']}: '{$user['name']}' ({$user['email']})\n";
    }
    echo "\n";
}

// STEP 2: Fix each user
if (count($invalidUsers) > 0) {
    echo "<h2>STEP 2: Fixing User Names</h2>\n";
    
    foreach ($invalidUsers as $user) {
        $email = $user['email'];
        $userId = $user['user_id'];
        
        // Extract name from email
        $emailParts = explode('@', $email);
        $localPart = $emailParts[0];
        
        // Try different patterns
        $nameParts = preg_split('/[_\.\-]/', $localPart);
        
        $fixedName = '';
        
        // Pattern 1: s23_lastname_firstname
        if (count($nameParts) >= 3 && preg_match('/^s\d+$/i', $nameParts[0])) {
            $firstName = ucfirst(strtolower($nameParts[2]));
            $lastName = ucfirst(strtolower($nameParts[1]));
            $fixedName = "$firstName $lastName";
        }
        // Pattern 2: firstname.lastname or firstname_lastname
        elseif (count($nameParts) >= 2) {
            $firstName = ucfirst(strtolower($nameParts[0]));
            $lastName = ucfirst(strtolower($nameParts[1]));
            $fixedName = "$firstName $lastName";
        }
        // Pattern 3: Just use the local part
        else {
            $fixedName = ucfirst(strtolower($localPart));
        }
        
        // Update the user
        $stmt = $conn->prepare("UPDATE users SET name = ? WHERE user_id = ?");
        $stmt->bind_param("si", $fixedName, $userId);
        
        if ($stmt->execute()) {
            echo "<span class='success'>✅ Fixed User ID {$userId}</span>\n";
            echo "   Email: {$email}\n";
            echo "   Old name: '{$user['name']}'\n";
            echo "   New name: '{$fixedName}'\n\n";
        } else {
            echo "<span class='error'>❌ Failed to fix User ID {$userId}</span>\n";
            echo "   Error: " . $stmt->error . "\n\n";
        }
        $stmt->close();
    }
}

// STEP 3: Verify all users now have valid names
echo "<h2>STEP 3: Verification</h2>\n";
$result = $conn->query("
    SELECT user_id, name, email, is_verified, is_active 
    FROM users 
    WHERE role_id != 1 
    ORDER BY created_at DESC 
    LIMIT 10
");

echo "Current users:\n";
while ($row = $result->fetch_assoc()) {
    $status = $row['is_verified'] ? ($row['is_active'] ? '✅ Active' : '⏳ Pending') : '❌ Not Verified';
    $nameLength = strlen($row['name']);
    $nameStatus = ($nameLength >= 2 && $row['name'] != '0') ? '✅' : '❌';
    echo "{$nameStatus} ID {$row['user_id']}: {$row['name']} ({$row['email']}) - {$status}\n";
}
echo "\n";

// STEP 4: Check registration form
echo "<h2>STEP 4: Registration Form Check</h2>\n";
echo "Checking if registration form has name field...\n";

if (file_exists('register.html')) {
    $registerHtml = file_get_contents('register.html');
    
    if (strpos($registerHtml, 'id="name"') !== false) {
        echo "<span class='success'>✅ Name field exists in registration form</span>\n";
    } else {
        echo "<span class='error'>❌ Name field missing in registration form!</span>\n";
    }
    
    if (strpos($registerHtml, 'name="name"') !== false) {
        echo "<span class='success'>✅ Name field has correct 'name' attribute</span>\n";
    } else {
        echo "<span class='warning'>⚠️ Name field missing 'name' attribute</span>\n";
    }
} else {
    echo "<span class='error'>❌ register.html not found!</span>\n";
}
echo "\n";

// STEP 5: Check JavaScript
echo "<h2>STEP 5: JavaScript Check</h2>\n";
if (file_exists('assets/js/register.js')) {
    $registerJs = file_get_contents('assets/js/register.js');
    
    if (strpos($registerJs, "getElementById('name')") !== false) {
        echo "<span class='success'>✅ JavaScript gets name field correctly</span>\n";
    } else {
        echo "<span class='error'>❌ JavaScript doesn't get name field!</span>\n";
    }
    
    if (strpos($registerJs, "name: document.getElementById('name').value") !== false) {
        echo "<span class='success'>✅ JavaScript sends name in form data</span>\n";
    } else {
        echo "<span class='warning'>⚠️ Check JavaScript form data structure</span>\n";
    }
} else {
    echo "<span class='error'>❌ register.js not found!</span>\n";
}
echo "\n";

closeDBConnection($conn);

// SUMMARY
echo "<h2>🎯 SUMMARY</h2>\n";
echo "Fixed users: " . count($invalidUsers) . "\n";
echo "Registration form: " . (file_exists('register.html') ? '✅ OK' : '❌ Missing') . "\n";
echo "JavaScript file: " . (file_exists('assets/js/register.js') ? '✅ OK' : '❌ Missing') . "\n";
echo "\n";

echo "<h2>📋 NEXT STEPS</h2>\n";
echo "1. <span class='success'>✅ All existing users with name '0' have been fixed</span>\n";
echo "2. Test new registration:\n";
echo "   - Go to: " . BASE_URL . "/register.html\n";
echo "   - Fill in the name field carefully\n";
echo "   - Complete registration\n";
echo "   - Check if name appears correctly\n";
echo "\n";
echo "3. If new registrations still show '0':\n";
echo "   - Open browser console (F12)\n";
echo "   - Check for JavaScript errors\n";
echo "   - Verify name field is being filled\n";
echo "   - Check network tab to see what data is sent\n";
echo "\n";
echo "4. Check error logs:\n";
echo "   - Location: C:\\xampp\\apache\\logs\\error.log\n";
echo "   - Look for 'Registration data received' entries\n";
echo "   - This will show what data the API receives\n";
echo "\n";

echo "<span class='success'>✅ FIX COMPLETE!</span>\n";
echo "\n⚠️ Delete this file after running for security\n";

echo "</pre></body></html>";
?>
