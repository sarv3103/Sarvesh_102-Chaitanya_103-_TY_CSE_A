<?php
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

$user = getCurrentUser();
if (!in_array($user['role_name'], ['Staff', 'Admin'])) {
    jsonResponse(false, 'Only staff and admin can create notices');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);

$title = sanitizeInput($data['title'] ?? '');
$content = sanitizeInput($data['content'] ?? '');
$targetType = sanitizeInput($data['target_type'] ?? ''); // 'all_students', 'all_staff', 'specific_class', 'everyone'
$targetClasses = $data['target_classes'] ?? []; // Array of class_ids
$isStaffOnly = ($targetType === 'all_staff') ? 1 : 0;

if (empty($title) || empty($content) || empty($targetType)) {
    jsonResponse(false, 'Title, content, and target type are required');
}

$conn = getDBConnection();

// Create notice
$stmt = $conn->prepare("INSERT INTO notices (title, content, sent_by_user_id, is_staff_only) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssii", $title, $content, $user['user_id'], $isStaffOnly);

if (!$stmt->execute()) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to create notice');
}

$noticeId = $stmt->insert_id;
$stmt->close();

// Add targets
$stmt = $conn->prepare("INSERT INTO notice_targets (notice_id, target_role_id, target_class_id) VALUES (?, ?, ?)");

if ($targetType === 'all_students') {
    $studentRoleId = 3; // Student role
    $stmt->bind_param("iii", $noticeId, $studentRoleId, $nullValue);
    $nullValue = null;
    $stmt->execute();
} elseif ($targetType === 'all_staff') {
    $staffRoleId = 2; // Staff role
    $stmt->bind_param("iii", $noticeId, $staffRoleId, $nullValue);
    $nullValue = null;
    $stmt->execute();
} elseif ($targetType === 'specific_class') {
    $studentRoleId = 3;
    foreach ($targetClasses as $classId) {
        $stmt->bind_param("iii", $noticeId, $studentRoleId, $classId);
        $stmt->execute();
    }
} elseif ($targetType === 'everyone') {
    // Add both students and staff
    $studentRoleId = 3;
    $staffRoleId = 2;
    $nullValue = null;
    
    $stmt->bind_param("iii", $noticeId, $studentRoleId, $nullValue);
    $stmt->execute();
    
    $stmt->bind_param("iii", $noticeId, $staffRoleId, $nullValue);
    $stmt->execute();
}

$stmt->close();
closeDBConnection($conn);

jsonResponse(true, 'Notice created successfully', ['notice_id' => $noticeId]);
?>
