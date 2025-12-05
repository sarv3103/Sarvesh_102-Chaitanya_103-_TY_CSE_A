<?php
// Complete project setup script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>CampusChrono - Complete Setup</title>";
echo "<style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
.success { color: #28a745; font-weight: bold; }
.error { color: #dc3545; font-weight: bold; }
.warning { color: #ffc107; font-weight: bold; }
.step { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff; }
.completed { border-left-color: #28a745; }
.failed { border-left-color: #dc3545; }
h1 { color: #007bff; text-align: center; }
h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
.btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
.btn:hover { background: #0056b3; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🚀 CampusChrono - Complete Setup</h1>";
echo "<p style='text-align: center; color: #666;'>Automated setup and verification for your notice management system</p>";

$allPassed = true;

// Step 1: Check database connection
echo "<div class='step' id='step1'>";
echo "<h2>Step 1: Database Connection</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    $conn = getDBConnection();
    echo "<p class='success'>✅ Database connection successful</p>";
    echo "<p>Connected to: " . DB_NAME . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>Solution:</strong> Make sure MySQL is running and database exists</p>";
    $allPassed = false;
}
echo "</div>";

// Step 2: Check required tables
echo "<div class='step' id='step2'>";
echo "<h2>Step 2: Database Tables</h2>";
$requiredTables = ['roles', 'departments', 'classes', 'users', 'notices', 'notice_targets', 'otp_tokens', 'comments', 'notice_attachments', 'notice_views'];
$missingTables = [];

foreach ($requiredTables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<p class='success'>✅ Table '$table' exists</p>";
    } else {
        echo "<p class='error'>❌ Table '$table' missing</p>";
        $missingTables[] = $table;
        $allPassed = false;
    }
}

if (!empty($missingTables)) {
    echo "<p class='error'><strong>Missing tables:</strong> " . implode(', ', $missingTables) . "</p>";
    echo "<p><strong>Solution:</strong> Import database/schema.sql via phpMyAdmin</p>";
}
echo "</div>";

// Step 3: Check sample data
echo "<div class='step' id='step3'>";
echo "<h2>Step 3: Sample Data</h2>";

// Check roles
$result = $conn->query("SELECT COUNT(*) as count FROM roles");
$roleCount = $result->fetch_assoc()['count'];
echo "<p>Roles: " . ($roleCount >= 3 ? "<span class='success'>✅ $roleCount roles</span>" : "<span class='error'>❌ $roleCount roles (need 3)</span>") . "</p>";

// Check departments
$result = $conn->query("SELECT COUNT(*) as count FROM departments");
$deptCount = $result->fetch_assoc()['count'];
echo "<p>Departments: " . ($deptCount >= 5 ? "<span class='success'>✅ $deptCount departments</span>" : "<span class='error'>❌ $deptCount departments (need 5)</span>") . "</p>";

// Check classes
$result = $conn->query("SELECT COUNT(*) as count FROM classes");
$classCount = $result->fetch_assoc()['count'];
echo "<p>Classes: " . ($classCount >= 8 ? "<span class='success'>✅ $classCount classes</span>" : "<span class='error'>❌ $classCount classes (need 8)</span>") . "</p>";

if ($roleCount < 3 || $deptCount < 5 || $classCount < 8) {
    echo "<p class='error'><strong>Solution:</strong> Import database/schema.sql to get sample data</p>";
    $allPassed = false;
}
echo "</div>";

// Step 4: Check admin user
echo "<div class='step' id='step4'>";
echo "<h2>Step 4: Admin User</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE email = 'admin@noticeboard.com'");
$adminCount = $result->fetch_assoc()['count'];

if ($adminCount > 0) {
    echo "<p class='success'>✅ Admin user exists</p>";
    echo "<p><strong>Email:</strong> admin@noticeboard.com</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
} else {
    echo "<p class='error'>❌ Admin user not found</p>";
    echo "<p><strong>Solution:</strong> Run fix-admin-password.php</p>";
    $allPassed = false;
}
echo "</div>";

// Step 5: Check email configuration
echo "<div class='step' id='step5'>";
echo "<h2>Step 5: Email Configuration</h2>";
require_once __DIR__ . '/includes/functions.php';

echo "<p>EMAIL_ENABLED: " . (EMAIL_ENABLED ? '<span class="success">✅ TRUE</span>' : '<span class="error">❌ FALSE</span>') . "</p>";
echo "<p>SMTP_HOST: " . SMTP_HOST . "</p>";
echo "<p>SMTP_USER: " . SMTP_USER . "</p>";
echo "<p>SMTP_FROM_NAME: " . SMTP_FROM_NAME . "</p>";

if (EMAIL_ENABLED) {
    echo "<p class='success'>✅ Email system is enabled</p>";
} else {
    echo "<p class='warning'>⚠️ Email system is disabled (OTP will show on screen)</p>";
}
echo "</div>";

// Step 6: Test core functions
echo "<div class='step' id='step6'>";
echo "<h2>Step 6: Core Functions Test</h2>";

$functions = ['generateOTP', 'hashPassword', 'verifyPassword', 'sanitizeInput', 'isValidEmail', 'sendOTPEmail'];
$functionsPassed = 0;

foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "<p class='success'>✅ $func() available</p>";
        $functionsPassed++;
    } else {
        echo "<p class='error'>❌ $func() missing</p>";
        $allPassed = false;
    }
}

echo "<p><strong>Functions available:</strong> $functionsPassed / " . count($functions) . "</p>";
echo "</div>";

// Step 7: Check file permissions
echo "<div class='step' id='step7'>";
echo "<h2>Step 7: File Permissions</h2>";

$uploadDir = __DIR__ . '/uploads';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
    echo "<p class='success'>✅ Created uploads directory</p>";
} else {
    echo "<p class='success'>✅ Uploads directory exists</p>";
}

if (is_writable($uploadDir)) {
    echo "<p class='success'>✅ Uploads directory is writable</p>";
} else {
    echo "<p class='error'>❌ Uploads directory is not writable</p>";
    echo "<p><strong>Solution:</strong> Set permissions to 777 for uploads folder</p>";
    $allPassed = false;
}
echo "</div>";

// Step 8: API Endpoints Test
echo "<div class='step' id='step8'>";
echo "<h2>Step 8: API Endpoints</h2>";

$apiEndpoints = [
    'api/get-departments.php',
    'api/get-classes-by-department.php',
    'api/register.php',
    'api/login.php',
    'api/verify-otp.php',
    'api/admin/get-all-users.php',
    'api/admin/approve-user.php',
    'api/admin/reject-user.php',
    'api/admin/bulk-change-class.php'
];

$endpointsPassed = 0;
foreach ($apiEndpoints as $endpoint) {
    if (file_exists(__DIR__ . '/' . $endpoint)) {
        echo "<p class='success'>✅ $endpoint exists</p>";
        $endpointsPassed++;
    } else {
        echo "<p class='error'>❌ $endpoint missing</p>";
        $allPassed = false;
    }
}

echo "<p><strong>API endpoints available:</strong> $endpointsPassed / " . count($apiEndpoints) . "</p>";
echo "</div>";

// Final Summary
echo "<div class='step " . ($allPassed ? 'completed' : 'failed') . "'>";
echo "<h2>🎯 Setup Summary</h2>";

if ($allPassed) {
    echo "<p class='success'><strong>🎉 CONGRATULATIONS! Your CampusChrono system is fully set up and ready to use!</strong></p>";
    echo "<h3>What you can do now:</h3>";
    echo "<ul>";
    echo "<li>✅ Register new users</li>";
    echo "<li>✅ Login as admin and manage users</li>";
    echo "<li>✅ Create and manage notices</li>";
    echo "<li>✅ Use bulk operations</li>";
    echo "<li>✅ Send email notifications</li>";
    echo "</ul>";
    
    echo "<h3>Quick Links:</h3>";
    echo "<a href='index.html' class='btn'>🔐 Login Page</a>";
    echo "<a href='register.html' class='btn'>📝 Registration</a>";
    echo "<a href='test-email.php' class='btn'>📧 Test Email</a>";
    echo "<a href='debug-registration.php' class='btn'>🔍 Debug Registration</a>";
    
} else {
    echo "<p class='error'><strong>⚠️ Setup incomplete. Please fix the issues above before using the system.</strong></p>";
    echo "<h3>Common Solutions:</h3>";
    echo "<ul>";
    echo "<li>Import database/schema.sql via phpMyAdmin</li>";
    echo "<li>Run fix-admin-password.php to create admin user</li>";
    echo "<li>Check XAMPP Apache and MySQL are running</li>";
    echo "<li>Set proper file permissions for uploads folder</li>";
    echo "</ul>";
}

echo "</div>";

// Close database connection
if (isset($conn)) {
    closeDBConnection($conn);
}

echo "</div>";
echo "</body></html>";
?>