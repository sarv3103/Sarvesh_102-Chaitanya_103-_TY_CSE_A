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

// Get notice details
$query = "SELECT n.*, u.name as sender_name, u.email as sender_email, u.user_id as sender_id
          FROM notices n 
          JOIN users u ON n.sent_by_user_id = u.user_id 
          WHERE n.notice_id = ?";

$stmt = $conn->prepare($query);
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

// Get attachments
$stmt = $conn->prepare("SELECT * FROM notice_attachments WHERE notice_id = ?");
$stmt->bind_param("i", $noticeId);
$stmt->execute();
$result = $stmt->get_result();

$attachments = [];
while ($row = $result->fetch_assoc()) {
    $attachments[] = $row;
}
$stmt->close();

// Get view count
$stmt = $conn->prepare("SELECT COUNT(*) as view_count FROM notice_views WHERE notice_id = ?");
$stmt->bind_param("i", $noticeId);
$stmt->execute();
$viewCount = $stmt->get_result()->fetch_assoc()['view_count'];
$stmt->close();

// Get comment count
$stmt = $conn->prepare("SELECT COUNT(*) as comment_count FROM comments WHERE notice_id = ?");
$stmt->bind_param("i", $noticeId);
$stmt->execute();
$commentCount = $stmt->get_result()->fetch_assoc()['comment_count'];
$stmt->close();

// Get viewers (only for creator, admin, staff)
$viewers = [];
if ($user['role_name'] === 'Admin' || $user['role_name'] === 'Staff' || $notice['sender_id'] == $user['user_id']) {
    $query = "SELECT nv.viewed_at, u.name, u.email, r.role_name 
              FROM notice_views nv 
              JOIN users u ON nv.user_id = u.user_id 
              JOIN roles r ON u.role_id = r.role_id 
              WHERE nv.notice_id = ? 
              ORDER BY nv.viewed_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $noticeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $viewers[] = $row;
    }
    $stmt->close();
}

// Record view
$stmt = $conn->prepare("INSERT INTO notice_views (notice_id, user_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE viewed_at = CURRENT_TIMESTAMP");
$stmt->bind_param("ii", $noticeId, $user['user_id']);
$stmt->execute();
$stmt->close();

closeDBConnection($conn);

jsonResponse(true, 'Notice details retrieved', [
    'notice' => $notice,
    'attachments' => $attachments,
    'view_count' => $viewCount,
    'comment_count' => $commentCount,
    'viewers' => $viewers,
    'can_edit' => ($notice['sender_id'] == $user['user_id']),
    'can_delete' => ($notice['sender_id'] == $user['user_id'] || $user['role_name'] === 'Admin')
]);
?>
