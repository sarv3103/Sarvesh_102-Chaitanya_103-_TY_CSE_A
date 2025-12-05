<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);

$email = sanitizeInput($data['email'] ?? '');

if (empty($email)) {
    jsonResponse(false, 'Email is required');
}

$conn = getDBConnection();

// Check if user exists
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Email not found');
}

$userId = $result->fetch_assoc()['user_id'];
$stmt->close();

// Generate OTP
$otp = generateOTP();
$expiresAt = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));

$stmt = $conn->prepare("INSERT INTO otp_tokens (user_id, email, otp_code, otp_type, expires_at) VALUES (?, ?, ?, 'RESET', ?)");
$stmt->bind_param("isss", $userId, $email, $otp, $expiresAt);
$stmt->execute();
$stmt->close();

closeDBConnection($conn);

// Send OTP
if (sendOTPEmail($email, $otp, 'RESET')) {
    jsonResponse(true, 'OTP sent to your email');
} else {
    jsonResponse(true, 'OTP: ' . $otp . ' (Email sending failed, showing OTP for testing)');
}
?>
