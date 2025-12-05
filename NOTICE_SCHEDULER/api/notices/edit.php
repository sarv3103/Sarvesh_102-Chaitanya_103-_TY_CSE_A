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
$title = sanitizeInput($data['title'] ?? '');
$content = sanitizeInput($data['content'] ?? '');

if ($noticeId <= 0 || empty($title) || empty($content)) {
    jsonResponse(false, 'Notice ID, title, and content are required');
}

$user = getCurrentUser();
$conn = getDBConnection();

// Check if user is the creator
$stmt = $conn->prepare("SELECT sent_by_user_id FROM notices WHERE notice_id = ?");
$stmt->bind_param("i", $noticeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Notice not found');
}

$notice = $result->fetch_assoc();
$stmt->close();

if ($notice['sent_by_user_id'] != $user['user_id']) {
    closeDBConnection($conn);
    jsonResponse(false, 'You can only edit your own notices');
}

// Update notice
$stmt = $conn->prepare("UPDATE notices SET title = ?, content = ?, updated_at = CURRENT_TIMESTAMP WHERE notice_id = ?");
$stmt->bind_param("ssi", $title, $content, $noticeId);

if ($stmt->execute()) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(true, 'Notice updated successfully');
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to update notice');
}
?>
