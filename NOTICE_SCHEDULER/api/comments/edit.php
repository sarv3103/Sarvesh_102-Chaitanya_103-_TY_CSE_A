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
$commentText = sanitizeInput($data['comment_text'] ?? '');

if ($commentId <= 0 || empty($commentText)) {
    jsonResponse(false, 'Comment ID and text are required');
}

$user = getCurrentUser();
$conn = getDBConnection();

// Check if user owns the comment
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

if ($comment['user_id'] != $user['user_id']) {
    closeDBConnection($conn);
    jsonResponse(false, 'You can only edit your own comments');
}

// Update comment
$stmt = $conn->prepare("UPDATE comments SET comment_text = ?, updated_at = CURRENT_TIMESTAMP WHERE comment_id = ?");
$stmt->bind_param("si", $commentText, $commentId);

if ($stmt->execute()) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(true, 'Comment updated successfully');
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to update comment');
}
?>
