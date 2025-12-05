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

$title = sanitizeInput($_POST['title'] ?? '');
$content = sanitizeInput($_POST['content'] ?? '');
$targetType = sanitizeInput($_POST['target_type'] ?? '');
$targetClasses = isset($_POST['target_classes']) ? json_decode($_POST['target_classes'], true) : [];
$targetDepartments = isset($_POST['target_departments']) ? json_decode($_POST['target_departments'], true) : [];
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
    $studentRoleId = 3;
    $nullValue = null;
    $stmt->bind_param("iii", $noticeId, $studentRoleId, $nullValue);
    $stmt->execute();
} elseif ($targetType === 'all_staff') {
    $staffRoleId = 2;
    $nullValue = null;
    $stmt->bind_param("iii", $noticeId, $staffRoleId, $nullValue);
    $stmt->execute();
} elseif ($targetType === 'specific_department') {
    // Get all classes in selected departments
    $studentRoleId = 3;
    $placeholders = implode(',', array_fill(0, count($targetDepartments), '?'));
    $deptQuery = "SELECT class_id FROM classes WHERE department_id IN ($placeholders)";
    $deptStmt = $conn->prepare($deptQuery);
    $deptStmt->bind_param(str_repeat('i', count($targetDepartments)), ...$targetDepartments);
    $deptStmt->execute();
    $deptResult = $deptStmt->get_result();
    
    while ($row = $deptResult->fetch_assoc()) {
        $classId = $row['class_id'];
        $stmt->bind_param("iii", $noticeId, $studentRoleId, $classId);
        $stmt->execute();
    }
    $deptStmt->close();
} elseif ($targetType === 'specific_class') {
    $studentRoleId = 3;
    foreach ($targetClasses as $classId) {
        $stmt->bind_param("iii", $noticeId, $studentRoleId, $classId);
        $stmt->execute();
    }
} elseif ($targetType === 'everyone') {
    $studentRoleId = 3;
    $staffRoleId = 2;
    $nullValue = null;
    
    $stmt->bind_param("iii", $noticeId, $studentRoleId, $nullValue);
    $stmt->execute();
    
    $stmt->bind_param("iii", $noticeId, $staffRoleId, $nullValue);
    $stmt->execute();
}

$stmt->close();

// Handle file uploads
$uploadedFiles = [];
if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
    $fileCount = count($_FILES['attachments']['name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
        $file = [
            'name' => $_FILES['attachments']['name'][$i],
            'type' => $_FILES['attachments']['type'][$i],
            'tmp_name' => $_FILES['attachments']['tmp_name'][$i],
            'size' => $_FILES['attachments']['size'][$i]
        ];
        
        $uploadResult = uploadFile($file, $noticeId);
        
        if ($uploadResult['success']) {
            // Save to database
            $stmt = $conn->prepare("INSERT INTO notice_attachments (notice_id, file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $noticeId, $uploadResult['file_name'], $uploadResult['file_path'], $uploadResult['file_type'], $uploadResult['file_size']);
            $stmt->execute();
            $stmt->close();
            
            $uploadedFiles[] = $uploadResult['file_name'];
        }
    }
}

closeDBConnection($conn);

jsonResponse(true, 'Notice created successfully', [
    'notice_id' => $noticeId,
    'uploaded_files' => $uploadedFiles
]);
?>
