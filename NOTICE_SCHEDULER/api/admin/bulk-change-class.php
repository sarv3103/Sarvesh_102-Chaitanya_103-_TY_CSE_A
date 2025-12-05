<?php
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('Admin')) {
    jsonResponse(false, 'Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);

$fromClassId = intval($data['from_class_id'] ?? 0);
$toClassId = intval($data['to_class_id'] ?? 0);
$userIds = $data['user_ids'] ?? [];

if ($fromClassId <= 0 && empty($userIds)) {
    jsonResponse(false, 'Either from_class_id or user_ids must be provided');
}

if ($toClassId <= 0) {
    jsonResponse(false, 'Target class is required');
}

$conn = getDBConnection();

// Verify target class exists
$stmt = $conn->prepare("SELECT class_id, class_name, department_id FROM classes WHERE class_id = ?");
$stmt->bind_param("i", $toClassId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Target class not found');
}

$targetClass = $result->fetch_assoc();
$stmt->close();

// Build query based on input
if (!empty($userIds)) {
    // Update specific users
    $placeholders = implode(',', array_fill(0, count($userIds), '?'));
    $query = "UPDATE users SET class_id = ?, department_id = ? WHERE user_id IN ($placeholders) AND role_id = (SELECT role_id FROM roles WHERE role_name = 'Student')";
    
    $stmt = $conn->prepare($query);
    $types = 'ii' . str_repeat('i', count($userIds));
    $params = array_merge([$toClassId, $targetClass['department_id']], $userIds);
    $stmt->bind_param($types, ...$params);
} else {
    // Update all students from a specific class
    $query = "UPDATE users SET class_id = ?, department_id = ? WHERE class_id = ? AND role_id = (SELECT role_id FROM roles WHERE role_name = 'Student')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $toClassId, $targetClass['department_id'], $fromClassId);
}

if ($stmt->execute()) {
    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(true, "Successfully moved $affectedRows student(s) to {$targetClass['class_name']}");
} else {
    $error = $stmt->error;
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to update students: ' . $error);
}
?>
