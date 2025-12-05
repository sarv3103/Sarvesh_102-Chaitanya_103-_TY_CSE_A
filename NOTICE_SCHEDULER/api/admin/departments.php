<?php
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('Admin')) {
    jsonResponse(false, 'Unauthorized');
}

$conn = getDBConnection();

$query = "SELECT d.*, 
          (SELECT COUNT(*) FROM users WHERE department_id = d.department_id) as user_count,
          (SELECT COUNT(*) FROM classes WHERE department_id = d.department_id) as class_count
          FROM departments d 
          ORDER BY d.department_name";

$result = $conn->query($query);

$departments = [];
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}

closeDBConnection($conn);

jsonResponse(true, 'Departments retrieved successfully', $departments);
?>
