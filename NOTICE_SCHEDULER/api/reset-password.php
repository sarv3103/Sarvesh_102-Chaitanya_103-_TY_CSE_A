<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);

$email = sanitizeInput($data['email'] ?? '');
$password = $data['password'] ?? '';
$confirmPassword = $data['confirm_password'] ?? '';

if (empty($email) || empty($password)) {
    jsonResponse(false, 'All fields are required');
}

if (strlen($password) < PASSWORD_MIN_LENGTH) {
    jsonResponse(false, 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters');
}

if ($password !== $confirmPassword) {
    jsonResponse(false, 'Passwords do not match');
}

$conn = getDBConnection();

// Update password
$passwordHash = hashPassword($password);
$stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
$stmt->bind_param("ss", $passwordHash, $email);

if ($stmt->execute()) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(true, 'Password reset successful');
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Password reset failed');
}
?>
