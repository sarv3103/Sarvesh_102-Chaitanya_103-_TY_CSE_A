<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

echo "📧 Testing Email System\n";
echo "======================\n";

$testEmail = 'campuschrono3103@gmail.com';
$testOTP = '123456';

echo "📝 Test Configuration:\n";
echo "  SMTP Host: " . SMTP_HOST . "\n";
echo "  SMTP Port: " . SMTP_PORT . "\n";
echo "  SMTP User: " . SMTP_USER . "\n";
echo "  SMTP From: " . SMTP_FROM . "\n";
echo "  Email Enabled: " . (EMAIL_ENABLED ? 'YES' : 'NO') . "\n";
echo "  Test Email: $testEmail\n";
echo "  Test OTP: $testOTP\n\n";

echo "🚀 Sending test OTP email...\n";
$result = sendOTPEmail($testEmail, $testOTP, 'VERIFY');

if ($result) {
    echo "✅ Email sent successfully!\n";
} else {
    echo "❌ Email failed to send\n";
    echo "📋 Error details may be in PHP error log\n";
}

echo "\n🧪 Testing direct mail() function...\n";
$subject = 'Test Email - CampusChrono';
$message = "This is a test email from CampusChrono system.\n\nTest OTP: $testOTP\n\nIf you receive this, email is working!";
$headers = "From: CampusChrono System <campuschrono3103@gmail.com>\r\n";
$headers .= "Reply-To: campuschrono3103@gmail.com\r\n";

$directResult = mail($testEmail, $subject, $message, $headers);

if ($directResult) {
    echo "✅ Direct mail() function worked!\n";
} else {
    echo "❌ Direct mail() function failed\n";
    echo "📋 Check your XAMPP mail configuration\n";
}

echo "\n📊 Summary:\n";
echo "  OTP Email Function: " . ($result ? 'WORKING' : 'FAILED') . "\n";
echo "  Direct Mail Function: " . ($directResult ? 'WORKING' : 'FAILED') . "\n";

if (!$result && !$directResult) {
    echo "\n🔧 Troubleshooting:\n";
    echo "  1. Check XAMPP sendmail configuration\n";
    echo "  2. Verify Gmail app password is correct\n";
    echo "  3. Check if 2-factor authentication is enabled\n";
    echo "  4. Verify less secure apps setting (if applicable)\n";
}
?>