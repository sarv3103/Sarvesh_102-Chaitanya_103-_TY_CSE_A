<?php
// Download and setup PHPMailer for email functionality
echo "📧 Setting up PHPMailer for CampusChrono\n";
echo "========================================\n\n";

// Create vendor directory if it doesn't exist
if (!file_exists('vendor')) {
    mkdir('vendor', 0777, true);
    echo "✅ Created vendor directory\n";
}

// Download PHPMailer files
$phpmailerFiles = [
    'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php',
    'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php'
];

echo "📥 Downloading PHPMailer files...\n";
foreach ($phpmailerFiles as $filename => $url) {
    $content = file_get_contents($url);
    if ($content !== false) {
        file_put_contents("vendor/$filename", $content);
        echo "✅ Downloaded $filename\n";
    } else {
        echo "❌ Failed to download $filename\n";
    }
}

echo "\n🎉 PHPMailer setup complete!\n";
echo "Now run this file in your browser: http://localhost/NOTICE_SCHEDULER/setup-phpmailer.php\n";
?>