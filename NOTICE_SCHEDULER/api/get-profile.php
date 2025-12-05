<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT u.user_id, u.email, u.name, u.roll_no, u.is_verified, u.is_active, u.created_at,
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

jsonResponse(true, 'Profile retrieved successfully', $user);
?>
