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

if ($noticeId <= 0) {
    jsonResponse(false, 'Invalid notice ID');
}

$user = getCurrentUser();
$conn = getDBConnection();

// Check if user can delete this notice
if ($user['role_name'] === 'Admin') {
    // Admin can delete any notice
    $stmt = $conn->prepare("DELETE FROM notices WHERE notice_id = ?");
    $stmt->bind_param("i", $noticeId);
} else {
    // Staff can only delete their own notices
    $stmt = $conn->prepare("DELETE FROM notices WHERE notice_id = ? AND sent_by_user_id = ?");
    $stmt->bind_param("ii", $noticeId, $user['user_id']);
}

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(true, 'Notice deleted successfully');
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to delete notice or unauthorized');
}
?>
