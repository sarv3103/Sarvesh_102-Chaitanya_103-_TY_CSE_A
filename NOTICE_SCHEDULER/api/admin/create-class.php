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

$className = sanitizeInput($data['class_name'] ?? '');
$departmentId = intval($data['department_id'] ?? 0);

if (empty($className) || $departmentId <= 0) {
    jsonResponse(false, 'Class name and department are required');
}

$conn = getDBConnection();

// Check if class already exists in this department
$stmt = $conn->prepare("SELECT class_id FROM classes WHERE class_name = ? AND department_id = ?");
$stmt->bind_param("si", $className, $departmentId);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Class already exists in this department');
}
$stmt->close();

// Create class
$stmt = $conn->prepare("INSERT INTO classes (class_name, department_id) VALUES (?, ?)");
$stmt->bind_param("si", $className, $departmentId);

if ($stmt->execute()) {
    $classId = $stmt->insert_id;
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(true, 'Class created successfully', ['class_id' => $classId]);
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to create class');
}
?>
