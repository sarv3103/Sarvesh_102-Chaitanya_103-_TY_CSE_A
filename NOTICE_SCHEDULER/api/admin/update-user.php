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

$userId = intval($data['user_id'] ?? 0);
$name = sanitizeInput($data['name'] ?? '');
$email = sanitizeInput($data['email'] ?? '');
$roleId = intval($data['role_id'] ?? 0);
$departmentId = isset($data['department_id']) ? intval($data['department_id']) : null;
$classId = isset($data['class_id']) ? intval($data['class_id']) : null;
$rollNo = sanitizeInput($data['roll_no'] ?? '');
$password = $data['password'] ?? null;

if ($userId <= 0) {
    jsonResponse(false, 'Invalid user ID');
}

// Prevent admin from editing their own role
if ($userId == $_SESSION['user_id'] && $roleId > 0) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT role_id FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $currentRole = $stmt->get_result()->fetch_assoc()['role_id'];
    $stmt->close();
    
    if ($currentRole != $roleId) {
        closeDBConnection($conn);
        jsonResponse(false, 'You cannot change your own role');
    }
} else {
    $conn = getDBConnection();
}

// Build update query dynamically
$updates = [];
$params = [];
$types = '';

if (!empty($name)) {
    $updates[] = "name = ?";
    $params[] = $name;
    $types .= 's';
}

if (!empty($email)) {
    // Check if email already exists for another user
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $stmt->bind_param("si", $email, $userId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        closeDBConnection($conn);
        jsonResponse(false, 'Email already exists');
    }
    $stmt->close();
    
    $updates[] = "email = ?";
    $params[] = $email;
    $types .= 's';
}

if ($roleId > 0) {
    $updates[] = "role_id = ?";
    $params[] = $roleId;
    $types .= 'i';
}

if ($departmentId !== null) {
    $updates[] = "department_id = ?";
    $params[] = $departmentId;
    $types .= 'i';
}

if ($classId !== null) {
    // Check for duplicate roll number in the same class
    if (!empty($rollNo)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE class_id = ? AND roll_no = ? AND user_id != ?");
        $stmt->bind_param("isi", $classId, $rollNo, $userId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            closeDBConnection($conn);
            jsonResponse(false, 'Roll number already exists in this class');
        }
        $stmt->close();
    }
    
    $updates[] = "class_id = ?";
    $params[] = $classId;
    $types .= 'i';
}

if (!empty($rollNo)) {
    $updates[] = "roll_no = ?";
    $params[] = $rollNo;
    $types .= 's';
}

if ($password !== null && !empty($password)) {
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        closeDBConnection($conn);
        jsonResponse(false, 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters');
    }
    $updates[] = "password_hash = ?";
    $params[] = hashPassword($password);
    $types .= 's';
}

if (empty($updates)) {
    closeDBConnection($conn);
    jsonResponse(false, 'No fields to update');
}

$params[] = $userId;
$types .= 'i';

$query = "UPDATE users SET " . implode(', ', $updates) . " WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(true, 'User updated successfully');
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to update user');
}
?>
