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

$departmentId = intval($data['department_id'] ?? 0);
$departmentName = sanitizeInput($data['department_name'] ?? '');
$departmentCode = sanitizeInput($data['department_code'] ?? '');

if ($departmentId <= 0 || empty($departmentName) || empty($departmentCode)) {
    jsonResponse(false, 'All fields are required');
}

$conn = getDBConnection();

// Check if name/code already exists for other departments
$stmt = $conn->prepare("SELECT department_id FROM departments WHERE (department_name = ? OR department_code = ?) AND department_id != ?");
$stmt->bind_param("ssi", $departmentName, $departmentCode, $departmentId);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Department name or code already exists');
}
$stmt->close();

// Update department
$stmt = $conn->prepare("UPDATE departments SET department_name = ?, department_code = ? WHERE department_id = ?");
$stmt->bind_param("ssi", $departmentName, $departmentCode, $departmentId);

if ($stmt->execute()) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(true, 'Department updated successfully');
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to update department');
}
?>
