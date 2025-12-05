<?php
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('Admin')) {
    jsonResponse(false, 'Unauthorized');
}

$userId = intval($_GET['user_id'] ?? 0);

if ($userId <= 0) {
    jsonResponse(false, 'Invalid user ID');
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT u.user_id, u.email, u.name, u.roll_no, u.role_id, u.department_id, u.class_id, 
                               u.is_verified, u.is_active, u.created_at,
                               r.role_name, d.department_name, d.department_code, c.class_name
                        FROM users u
                        JOIN roles r ON u.role_id = r.role_id
                        LEFT JOIN departments d ON u.department_id = d.department_id
                        LEFT JOIN classes c ON u.class_id = c.class_id
                        WHERE u.user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'User not found');
}

$user = $result->fetch_assoc();
$stmt->close();
closeDBConnection($conn);

jsonResponse(true, 'User details retrieved', $user);
?>
