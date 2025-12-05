<?php
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);
$commentId = intval($data['comment_id'] ?? 0);

if ($commentId <= 0) {
    jsonResponse(false, 'Invalid comment ID');
}

$user = getCurrentUser();
$conn = getDBConnection();

// Check if user owns the comment or is admin
$stmt = $conn->prepare("SELECT user_id FROM comments WHERE comment_id = ?");
$stmt->bind_param("i", $commentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Comment not found');
}

$comment = $result->fetch_assoc();
$stmt->close();

if ($comment['user_id'] != $user['user_id'] && $user['role_name'] !== 'Admin') {
    closeDBConnection($conn);
    jsonResponse(false, 'You can only delete your own comments');
}

// Delete comment
$stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
$stmt->bind_param("i", $commentId);

if ($stmt->execute()) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(true, 'Comment deleted successfully');
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to delete comment');
}
?>
