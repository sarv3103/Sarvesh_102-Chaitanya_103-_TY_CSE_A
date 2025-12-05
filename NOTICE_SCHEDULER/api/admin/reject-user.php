<?php
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('Admin')) {
    jsonResponse(false, 'Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = intval($data['user_id'] ?? 0);
$reason = sanitizeInput($data['reason'] ?? '');

if ($userId <= 0) {
    jsonResponse(false, 'Invalid user ID');
}

if (empty($reason)) {
    jsonResponse(false, 'Rejection reason is required');
}

$conn = getDBConnection();

// Get user details before rejecting
$stmt = $conn->prepare("SELECT email, name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'User not found');
}

$user = $result->fetch_assoc();
$stmt->close();

// Delete user (rejection)
$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    $stmt->close();
    closeDBConnection($conn);
    
    // Send rejection email
    sendRejectionEmail($user['email'], $user['name'], $reason);
    
    jsonResponse(true, 'User rejected and notified');
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to reject user');
}
?>
