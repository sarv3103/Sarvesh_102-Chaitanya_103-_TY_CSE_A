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

if ($classId <= 0) {
    jsonResponse(false, 'Invalid class ID');
}

$conn = getDBConnection();

// Check if class has students
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE class_id = ?");
$stmt->bind_param("i", $classId);
$stmt->execute();
$studentCount = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

if ($studentCount > 0) {
    closeDBConnection($conn);
    jsonResponse(false, "Cannot delete class. $studentCount students are assigned to this class. Please reassign them first.");
}

// Delete class
$stmt = $conn->prepare("DELETE FROM classes WHERE class_id = ?");
$stmt->bind_param("i", $classId);

if ($stmt->execute()) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(true, 'Class deleted successfully');
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to delete class');
}
?>
