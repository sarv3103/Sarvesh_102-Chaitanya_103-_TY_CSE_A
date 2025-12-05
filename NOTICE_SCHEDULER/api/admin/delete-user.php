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

// Prevent admin from deleting themselves
if ($userId == $_SESSION['user_id']) {
    jsonResponse(false, 'You cannot delete your own account');
}

$conn = getDBConnection();

// Check if user exists
$stmt = $conn->prepare("SELECT user_id, name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'User not found');
}

$stmt->close();

// Delete user (CASCADE will handle related records)
$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(true, 'User deleted successfully');
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to delete user');
}
?>
