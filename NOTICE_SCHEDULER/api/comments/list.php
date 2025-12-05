<?php
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

$noticeId = intval($_GET['notice_id'] ?? 0);

if ($noticeId <= 0) {
    jsonResponse(false, 'Invalid notice ID');
}

$user = getCurrentUser();
$conn = getDBConnection();

$query = "SELECT c.*, u.name as user_name, u.email as user_email, r.role_name 
          FROM comments c 
          JOIN users u ON c.user_id = u.user_id 
          JOIN roles r ON u.role_id = r.role_id 
          WHERE c.notice_id = ? 
          ORDER BY c.created_at ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $noticeId);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    $row['can_edit'] = ($row['user_id'] == $user['user_id']);
    $row['can_delete'] = ($row['user_id'] == $user['user_id'] || $user['role_name'] === 'Admin');
    $comments[] = $row;
}

$stmt->close();
closeDBConnection($conn);

jsonResponse(true, 'Comments retrieved successfully', $comments);
?>
