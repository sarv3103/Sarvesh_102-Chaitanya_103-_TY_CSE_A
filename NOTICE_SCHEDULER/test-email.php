<?php
// Email testing script
require_once __DIR__ . '/includes/functions.php';

echo "<!DOCTYPE html><html><head><title>Email Test</title>";
echo "<style>body{font-family:Arial;padding:20px;}.success{color:green;}.error{color:red;}</style></head><body>";

echo "<h1>📧 Email System Test</h1>";

// Test email configuration
echo "<h2>Configuration Check</h2>";
echo "<p><strong>EMAIL_ENABLED:</strong> " . (EMAIL_ENABLED ? '<span class="success">✅ TRUE</span>' : '<span class="error">❌ FALSE</span>') . "</p>";
echo "<p><strong>SMTP_HOST:</strong> " . SMTP_HOST . "</p>";
echo "<p><strong>SMTP_PORT:</strong> " . SMTP_PORT . "</p>";
echo "<p><strong>SMTP_USER:</strong> " . SMTP_USER . "</p>";
echo "<p><strong>SMTP_FROM:</strong> " . SMTP_FROM . "</p>";
echo "<p><strong>SMTP_FROM_NAME:</strong> " . SMTP_FROM_NAME . "</p>";

if (!EMAIL_ENABLED) {
    echo "<p class='error'><strong>❌ Email is disabled. Set EMAIL_ENABLED = true in config.php</strong></p>";
    echo "</body></html>";
    exit;
}

// Test email sending
echo "<h2>Email Sending Test</h2>";

$testEmail = 'test@example.com'; // Change this to your email for testing
$testName = 'Test User';

echo "<h3>1. Testing OTP Email</h3>";
$otp = generateOTP();
$result1 = sendOTPEmail($testEmail, $otp, 'VERIFY');
echo "<p>Result: " . ($result1 ? '<span class="success">✅ SUCCESS</span>' : '<span class="error">❌ FAILED</span>') . "</p>";
echo "<p>OTP Generated: $otp</p>";

echo "<h3>2. Testing Application Submitted Email</h3>";
$result2 = sendApplicationSubmittedEmail($testEmail, $testName);
echo "<p>Result: " . ($result2 ? '<span class="success">✅ SUCCESS</span>' : '<span class="error">❌ FAILED</span>') . "</p>";

echo "<h3>3. Testing Approval Email</h3>";
$result3 = sendApprovalEmail($testEmail, $testName);
echo "<p>Result: " . ($result3 ? '<span class="success">✅ SUCCESS</span>' : '<span class="error">❌ FAILED</span>') . "</p>";

echo "<h3>4. Testing Rejection Email</h3>";
$result4 = sendRejectionEmail($testEmail, $testName, 'Test rejection reason');
echo "<p>Result: " . ($result4 ? '<span class="success">✅ SUCCESS</span>' : '<span class="error">❌ FAILED</span>') . "</p>";

// PHP mail configuration check
echo "<h2>PHP Mail Configuration</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Mail Function Available:</strong> " . (function_exists('mail') ? '<span class="success">✅ YES</span>' : '<span class="error">❌ NO</span>') . "</p>";

// Check if mail() function works
echo "<h3>Direct Mail Test</h3>";
$subject = 'Test Email from CampusChrono';
$message = 'This is a test email to verify mail functionality.';
$headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">\r\n";
$headers .= "Reply-To: " . SMTP_FROM . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$directResult = mail($testEmail, $subject, $message, $headers);
echo "<p>Direct mail() result: " . ($directResult ? '<span class="success">✅ SUCCESS</span>' : '<span class="error">❌ FAILED</span>') . "</p>";

if (!$directResult) {
    echo "<h3>Troubleshooting</h3>";
    echo "<ul>";
    echo "<li>Check if XAMPP Mercury Mail is running</li>";
    echo "<li>Or configure external SMTP in php.ini</li>";
    echo "<li>For Gmail, use App Password (not regular password)</li>";
    echo "<li>Enable 2-factor authentication on Gmail</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<h3>Summary</h3>";
$totalTests = 5;
$passedTests = ($result1 ? 1 : 0) + ($result2 ? 1 : 0) + ($result3 ? 1 : 0) + ($result4 ? 1 : 0) + ($directResult ? 1 : 0);
echo "<p><strong>Tests Passed:</strong> $passedTests / $totalTests</p>";

if ($passedTests == $totalTests) {
    echo "<p class='success'><strong>🎉 All email tests passed! Email system is working correctly.</strong></p>";
} else {
    echo "<p class='error'><strong>⚠️ Some email tests failed. Check configuration and troubleshooting steps above.</strong></p>";
}

echo "<p><strong>Note:</strong> Change \$testEmail variable in this script to your actual email address to receive test emails.</p>";

echo "</body></html>";
?>