<?php
/**
 * CampusChrono Configuration File - EXAMPLE
 * 
 * INSTRUCTIONS:
 * 1. Copy this file and rename it to: config.php
 * 2. Update the values below with your actual credentials
 * 3. Never commit config.php to GitHub (it's in .gitignore)
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application configuration
define('APP_NAME', 'CampusChrono');
define('BASE_URL', 'http://localhost/NOTICE_SCHEDULER'); // Change this to your folder name

// Email configuration (for OTP and notifications)
// Get Gmail app password from: https://myaccount.google.com/apppasswords
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');           // ← Change this to your Gmail
define('SMTP_PASS', 'xxxx xxxx xxxx xxxx');            // ← Change this to your app password
define('SMTP_FROM', 'your-email@gmail.com');           // ← Change this to your Gmail
define('SMTP_FROM_NAME', 'CampusChrono System');

// Email is ENABLED - Set to false if you don't want to use email
define('EMAIL_ENABLED', true); // Set to false for testing without email

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
