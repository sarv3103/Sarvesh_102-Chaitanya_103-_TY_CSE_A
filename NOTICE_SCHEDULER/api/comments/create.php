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

$noticeId = intval($data['notice_id'] ?? 0);
$commentText = sanitizeInput($data['comment_text'] ?? '');

if ($noticeId <= 0 || empty($commentText)) {
    jsonResponse(false, 'Notice ID and comment text are required');
}

$user = getCurrentUser();
$conn = getDBConnection();

// Verify user can access this notice (simplified check)
$stmt = $conn->prepare("SELECT notice_id FROM notices WHERE notice_id = ?");
$stmt->bind_param("i", $noticeId);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Notice not found');
}
$stmt->close();

// Add comment
$stmt = $conn->prepare("INSERT INTO comments (notice_id, user_id, comment_text) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $noticeId, $user['user_id'], $commentText);

if ($stmt->execute()) {
    $commentId = $stmt->insert_id;
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(true, 'Comment added successfully', ['comment_id' => $commentId]);
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to add comment');
}
?>
