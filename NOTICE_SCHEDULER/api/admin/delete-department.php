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

if ($departmentId <= 0) {
    jsonResponse(false, 'Invalid department ID');
}

$conn = getDBConnection();

// Check if department has users
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE department_id = ?");
$stmt->bind_param("i", $departmentId);
$stmt->execute();
$userCount = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

if ($userCount > 0) {
    closeDBConnection($conn);
    jsonResponse(false, "Cannot delete department. $userCount users are assigned to this department. Please reassign them first.");
}

// Check if department has classes
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM classes WHERE department_id = ?");
$stmt->bind_param("i", $departmentId);
$stmt->execute();
$classCount = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

if ($classCount > 0) {
    closeDBConnection($conn);
    jsonResponse(false, "Cannot delete department. $classCount classes are linked to this department. Please delete them first.");
}

// Delete department
$stmt = $conn->prepare("DELETE FROM departments WHERE department_id = ?");
$stmt->bind_param("i", $departmentId);

if ($stmt->execute()) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(true, 'Department deleted successfully');
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to delete department');
}
?>
