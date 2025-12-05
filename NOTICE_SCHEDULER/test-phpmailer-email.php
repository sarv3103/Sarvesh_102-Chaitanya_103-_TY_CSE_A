<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

echo "📧 Testing PHPMailer Email System\n";
echo "=================================\n\n";

echo "📝 Configuration:\n";
echo "  SMTP Host: " . SMTP_HOST . "\n";
echo "  SMTP Port: " . SMTP_PORT . "\n";
echo "  SMTP User: " . SMTP_USER . "\n";
echo "  SMTP From: " . SMTP_FROM . "\n";
echo "  Email Enabled: " . (EMAIL_ENABLED ? 'YES' : 'NO') . "\n\n";

if (!EMAIL_ENABLED) {
    echo "❌ Email is disabled in config. Enable it first.\n";
    exit;
}

// Test email
$testEmail = 'campuschrono3103@gmail.com'; // Change this to your email for testing
$testOTP = '123456';

echo "🚀 Sending test OTP email to: $testEmail\n";
echo "📧 Test OTP: $testOTP\n\n";

$result = sendOTPEmail($testEmail, $testOTP, 'VERIFY');

if ($result) {
    echo "✅ SUCCESS! Email sent successfully!\n";
    echo "📬 Check your inbox for the OTP email.\n";
    echo "📧 If you don't see it, check spam folder.\n\n";
    
    echo "🎉 EMAIL SYSTEM IS WORKING!\n";
    echo "Now you can:\n";
    echo "1. Test registration with real email delivery\n";
    echo "2. Users will receive OTP via email\n";
    echo "3. Approval/rejection emails will be sent\n\n";
    
} else {
    echo "❌ FAILED! Email could not be sent.\n";
    echo "🔧 Troubleshooting:\n";
    echo "1. Check Gmail app password is correct\n";
    echo "2. Verify 2-factor authentication is enabled on Gmail\n";
    echo "3. Make sure 'Less secure app access' is configured if needed\n";
    echo "4. Check internet connection\n\n";
    
    echo "📋 Current app password: " . SMTP_PASS . "\n";
    echo "🔄 Try the alternative password: uzvp tqes ewor xpig\n\n";
}

echo "📊 Next Steps:\n";
echo "==============\n";
if ($result) {
    echo "✅ Email working - proceed to test registration\n";
    echo "🔗 Test registration: http://localhost/NOTICE_SCHEDULER/register.html\n";
} else {
    echo "🔧 Fix email configuration first\n";
    echo "📧 Update SMTP_PASS in config/config.php if needed\n";
}

echo "\n🎯 Complete Setup Guide: http://localhost/NOTICE_SCHEDULER/COMPLETE_SETUP_GUIDE.php\n";
?>