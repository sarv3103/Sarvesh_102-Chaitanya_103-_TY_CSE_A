<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$conn = getDBConnection();

$query = "SELECT * FROM departments WHERE is_active = TRUE ORDER BY department_name";
$result = $conn->query($query);

$departments = [];
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}

closeDBConnection($conn);

echo json_encode([
    'success' => true,
    'data' => $departments
]);
?>
