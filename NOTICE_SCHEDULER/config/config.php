<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application configuration
define('APP_NAME', 'CampusChrono');
define('BASE_URL', 'http://localhost/NOTICE_SCHEDULER'); // Change this if you rename the folder

// Email configuration (for OTP)
// CONFIGURED AND READY TO USE
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'campuschrono3103@gmail.com');
define('SMTP_PASS', 'qhvy psqt gsiy amex'); // CampusChrono-SMTP App Password
define('SMTP_FROM', 'campuschrono3103@gmail.com');
define('SMTP_FROM_NAME', 'CampusChrono System');

// Email is ENABLED with new app password
define('EMAIL_ENABLED', true); // ENABLED - Using CampusChrono-SMTP app password

// OTP configuration
define('OTP_EXPIRY_MINUTES', 5);
define('OTP_LENGTH', 6);

// Security
define('PASSWORD_MIN_LENGTH', 6);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database config
require_once __DIR__ . '/database.php';
?>
