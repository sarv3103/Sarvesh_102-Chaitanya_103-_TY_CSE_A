<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Enable error logging for debugging
error_log("Login attempt started");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);

$email = sanitizeInput($data['email'] ?? '');
$password = $data['password'] ?? '';

error_log("Login attempt for email: " . $email);

if (empty($email) || empty($password)) {
    jsonResponse(false, 'Email and password are required');
}

$conn = getDBConnection();

if (!$conn) {
    error_log("Database connection failed");
    jsonResponse(false, 'Database connection error. Please check if MySQL is running.');
}

$stmt = $conn->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    error_log("User not found: " . $email);
    jsonResponse(false, 'Invalid email or password');
}

$user = $result->fetch_assoc();
$stmt->close();
closeDBConnection($conn);

error_log("User found, verifying password");

if (!verifyPassword($password, $user['password_hash'])) {
    error_log("Password verification failed for: " . $email);
    jsonResponse(false, 'Invalid email or password');
}

if (!$user['is_verified']) {
    error_log("User not verified: " . $email);
    jsonResponse(false, 'Please verify your email first');
}

if (!$user['is_active']) {
    error_log("User not active: " . $email);
    jsonResponse(false, 'Your account is pending admin approval');
}

// Set session
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['email'] = $user['email'];
$_SESSION['name'] = $user['name'];
$_SESSION['role'] = $user['role_name'];

error_log("Login successful for: " . $email . " as " . $user['role_name']);

jsonResponse(true, 'Login successful', [
    'role' => $user['role_name'],
    'name' => $user['name']
]);
?>
