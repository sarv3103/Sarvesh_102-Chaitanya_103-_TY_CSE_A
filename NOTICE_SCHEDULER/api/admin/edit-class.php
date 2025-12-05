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

$classId = intval($data['class_id'] ?? 0);
$className = sanitizeInput($data['class_name'] ?? '');
$departmentId = intval($data['department_id'] ?? 0);

if ($classId <= 0 || empty($className) || $departmentId <= 0) {
    jsonResponse(false, 'All fields are required');
}

$conn = getDBConnection();

// Check if class name already exists in this department (excluding current class)
$stmt = $conn->prepare("SELECT class_id FROM classes WHERE class_name = ? AND department_id = ? AND class_id != ?");
$stmt->bind_param("sii", $className, $departmentId, $classId);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Class name already exists in this department');
}
$stmt->close();

// Update class
$stmt = $conn->prepare("UPDATE classes SET class_name = ?, department_id = ? WHERE class_id = ?");
$stmt->bind_param("sii", $className, $departmentId, $classId);

if ($stmt->execute()) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(true, 'Class updated successfully');
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to update class');
}
?>
