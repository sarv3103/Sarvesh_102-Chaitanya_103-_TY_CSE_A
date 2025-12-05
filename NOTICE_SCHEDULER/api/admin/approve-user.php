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

if ($userId <= 0) {
    jsonResponse(false, 'Invalid user ID');
}

$conn = getDBConnection();

// Get user details before approving
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

// Approve user
$stmt = $conn->prepare("UPDATE users SET is_active = TRUE WHERE user_id = ?");
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    $stmt->close();
    closeDBConnection($conn);
    
    // Send approval email
    sendApprovalEmail($user['email'], $user['name']);
    
    jsonResponse(true, 'User approved successfully');
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to approve user');
}
?>
