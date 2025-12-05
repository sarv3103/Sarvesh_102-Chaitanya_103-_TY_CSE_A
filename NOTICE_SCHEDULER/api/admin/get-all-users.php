<?php
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('Admin')) {
    jsonResponse(false, 'Unauthorized');
}

$conn = getDBConnection();

$query = "SELECT u.user_id, u.email, u.name, u.roll_no, u.is_verified, u.is_active, u.created_at,
                 r.role_name, d.department_name, c.class_name
          FROM users u
          JOIN roles r ON u.role_id = r.role_id
          LEFT JOIN departments d ON u.department_id = d.department_id
          LEFT JOIN classes c ON u.class_id = c.class_id
          WHERE u.is_active = TRUE
          ORDER BY u.created_at DESC";

$result = $conn->query($query);

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

closeDBConnection($conn);

jsonResponse(true, 'Users retrieved successfully', $users);
?>
