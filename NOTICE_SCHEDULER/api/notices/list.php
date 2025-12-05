<?php
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

$user = getCurrentUser();
$conn = getDBConnection();

$notices = [];

if ($user['role_name'] === 'Admin') {
    // Admin sees all notices
    $query = "SELECT n.*, u.name as sender_name, u.email as sender_email 
              FROM notices n 
              JOIN users u ON n.sent_by_user_id = u.user_id 
              ORDER BY n.created_at DESC";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $notices[] = $row;
    }
} elseif ($user['role_name'] === 'Staff') {
    // Staff sees: staff-only notices + all student notices
    $query = "SELECT DISTINCT n.*, u.name as sender_name, u.email as sender_email 
              FROM notices n 
              JOIN users u ON n.sent_by_user_id = u.user_id 
              LEFT JOIN notice_targets nt ON n.notice_id = nt.notice_id 
              WHERE n.is_staff_only = 1 
              OR nt.target_role_id = 3 
              OR nt.target_role_id = 2
              ORDER BY n.created_at DESC";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $notices[] = $row;
    }
} elseif ($user['role_name'] === 'Student') {
    // Students see notices targeted to them
    $studentRoleId = 3;
    $classId = $user['class_id'];
    
    $query = "SELECT DISTINCT n.*, u.name as sender_name, u.email as sender_email 
              FROM notices n 
              JOIN users u ON n.sent_by_user_id = u.user_id 
              JOIN notice_targets nt ON n.notice_id = nt.notice_id 
              WHERE n.is_staff_only = 0 
              AND nt.target_role_id = ? 
              AND (nt.target_class_id IS NULL OR nt.target_class_id = ?)
              ORDER BY n.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $studentRoleId, $classId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $notices[] = $row;
    }
    $stmt->close();
}

closeDBConnection($conn);

jsonResponse(true, 'Notices retrieved successfully', $notices);
?>
