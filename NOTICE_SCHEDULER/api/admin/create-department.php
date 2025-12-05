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

$departmentName = sanitizeInput($data['department_name'] ?? '');
$departmentCode = sanitizeInput($data['department_code'] ?? '');

if (empty($departmentName) || empty($departmentCode)) {
    jsonResponse(false, 'Department name and code are required');
}

$conn = getDBConnection();

// Check if department already exists
$stmt = $conn->prepare("SELECT department_id FROM departments WHERE department_name = ? OR department_code = ?");
$stmt->bind_param("ss", $departmentName, $departmentCode);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Department name or code already exists');
}
$stmt->close();

// Create department
$stmt = $conn->prepare("INSERT INTO departments (department_name, department_code) VALUES (?, ?)");
$stmt->bind_param("ss", $departmentName, $departmentCode);

if ($stmt->execute()) {
    $departmentId = $stmt->insert_id;
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(true, 'Department created successfully', ['department_id' => $departmentId]);
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to create department');
}
?>
