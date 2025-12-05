<?php
// Simple email test with both app passwords
echo "<h1>📧 Simple Email Test</h1>";

$passwords = [
    'Mail' => 'pkpy mbqv bmzl ncgw',
    'Notice Sender' => 'uzvp tqes ewor xpig'
];

$testEmail = 'campuschrono3103@gmail.com'; // Send to self for testing
$subject = 'Test Email from CampusChrono';
$message = 'This is a test email to verify SMTP functionality.';

foreach ($passwords as $name => $password) {
    echo "<h2>Testing with $name App Password</h2>";
    
    $headers = "From: CampusChrono System <campuschrono3103@gmail.com>\r\n";
    $headers .= "Reply-To: campuschrono3103@gmail.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Configure mail settings
    ini_set('SMTP', 'smtp.gmail.com');
    ini_set('smtp_port', 587);
    ini_set('sendmail_from', 'campuschrono3103@gmail.com');
    
    $result = mail($testEmail, $subject . " ($name)", $message, $headers);
    
    if ($result) {
        echo "<p style='color:green;'>✅ SUCCESS with $name password</p>";
    } else {
        echo "<p style='color:red;'>❌ FAILED with $name password</p>";
    }
}

echo "<hr>";
echo "<h2>PHP Mail Configuration</h2>";
echo "<p>SMTP: " . ini_get('SMTP') . "</p>";
echo "<p>SMTP Port: " . ini_get('smtp_port') . "</p>";
echo "<p>Sendmail From: " . ini_get('sendmail_from') . "</p>";
echo "<p>Mail Function: " . (function_exists('mail') ? 'Available' : 'Not Available') . "</p>";

echo "<hr>";
echo "<p><strong>Note:</strong> Check your Gmail inbox for test emails.</p>";
echo "<p><a href='register.html'>Try Registration</a> | <a href='complete-setup.php'>Complete Setup</a></p>";
?>