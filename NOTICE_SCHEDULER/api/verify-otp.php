<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);

$email = sanitizeInput($data['email'] ?? '');
$otp = sanitizeInput($data['otp'] ?? '');
$type = sanitizeInput($data['type'] ?? 'VERIFY');

if (empty($email) || empty($otp)) {
    jsonResponse(false, 'Email and OTP are required');
}

$conn = getDBConnection();

// Verify OTP
$stmt = $conn->prepare("SELECT otp_id, user_id FROM otp_tokens WHERE email = ? AND otp_code = ? AND otp_type = ? AND expires_at > NOW() AND is_used = FALSE ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("sss", $email, $otp, $type);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Invalid or expired OTP');
}

$otpData = $result->fetch_assoc();
$stmt->close();

// Mark OTP as used
$stmt = $conn->prepare("UPDATE otp_tokens SET is_used = TRUE WHERE otp_id = ?");
$stmt->bind_param("i", $otpData['otp_id']);
$stmt->execute();
$stmt->close();

if ($type === 'VERIFY') {
    // Get user name for email
    $stmt = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $otpData['user_id']);
    $stmt->execute();
    $userName = $stmt->get_result()->fetch_assoc()['name'];
    $stmt->close();
    
    // Mark user as verified
    $stmt = $conn->prepare("UPDATE users SET is_verified = TRUE WHERE user_id = ?");
    $stmt->bind_param("i", $otpData['user_id']);
    $stmt->execute();
    $stmt->close();
    
    closeDBConnection($conn);
    
    // Send application submitted email
    sendApplicationSubmittedEmail($email, $userName);
    
    jsonResponse(true, 'Account verified successfully! Your application has been sent to admin for approval. You will receive an email once approved.');
} else {
    // For password reset, return success
    closeDBConnection($conn);
    jsonResponse(true, 'OTP verified successfully', ['email' => $email]);
}
?>
