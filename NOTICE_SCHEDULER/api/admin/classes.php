<?php
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

$conn = getDBConnection();

$query = "SELECT c.*, d.department_name, d.department_code,
          (SELECT COUNT(*) FROM users WHERE class_id = c.class_id) as student_count
          FROM classes c 
          JOIN departments d ON c.department_id = d.department_id 
          WHERE c.is_active = TRUE
          ORDER BY d.department_name, c.class_name";
$result = $conn->query($query);

$classes = [];
while ($row = $result->fetch_assoc()) {
    $classes[] = $row;
}

closeDBConnection($conn);

jsonResponse(true, 'Classes retrieved successfully', $classes);
?>
