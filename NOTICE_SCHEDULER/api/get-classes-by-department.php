<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$departmentId = intval($_GET['department_id'] ?? 0);

if ($departmentId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid department ID']);
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT * FROM classes WHERE department_id = ? AND is_active = TRUE ORDER BY class_name");
$stmt->bind_param("i", $departmentId);
$stmt->execute();
$result = $stmt->get_result();

$classes = [];
while ($row = $result->fetch_assoc()) {
    $classes[] = $row;
}

$stmt->close();
closeDBConnection($conn);

echo json_encode([
    'success' => true,
    'data' => $classes
]);
?>
