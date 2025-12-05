<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);

// Log received data for debugging
error_log("Registration data received: " . json_encode($data));

$role = sanitizeInput($data['role'] ?? '');
$name = sanitizeInput($data['name'] ?? '');
$email = sanitizeInput($data['email'] ?? '');
$password = $data['password'] ?? '';
$confirmPassword = $data['confirm_password'] ?? '';
$departmentId = intval($data['department_id'] ?? 0);
$classId = intval($data['class_id'] ?? 0);
$rollNo = sanitizeInput($data['roll_no'] ?? '');

// Log sanitized name
error_log("Sanitized name: '" . $name . "'");

// Validation
if (empty($role) || empty($name) || empty($email) || empty($password) || $departmentId <= 0) {
    error_log("Validation failed - Role: '$role', Name: '$name', Email: '$email', Dept: $departmentId");
    jsonResponse(false, 'All required fields must be filled');
}

if ($role !== 'Student' && $role !== 'Staff') {
    jsonResponse(false, 'Invalid role selected');
}

// Student-specific validation
if ($role === 'Student') {
    if ($classId <= 0 || empty($rollNo)) {
        jsonResponse(false, 'Students must provide class and roll number');
    }
}

if (!isValidEmail($email)) {
    jsonResponse(false, 'Invalid email address');
}

if (strlen($password) < PASSWORD_MIN_LENGTH) {
    jsonResponse(false, 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters');
}

if ($password !== $confirmPassword) {
    jsonResponse(false, 'Passwords do not match');
}

$conn = getDBConnection();

// Check if email already exists
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Email already registered');
}
$stmt->close();

// Verify class belongs to department (only for students)
if ($role === 'Student') {
    $stmt = $conn->prepare("SELECT class_id FROM classes WHERE class_id = ? AND department_id = ?");
    $stmt->bind_param("ii", $classId, $departmentId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        $stmt->close();
        closeDBConnection($conn);
        jsonResponse(false, 'Invalid class or department combination');
    }
    $stmt->close();
    
    // Check for duplicate roll number in the same class
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE class_id = ? AND roll_no = ?");
    $stmt->bind_param("is", $classId, $rollNo);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        closeDBConnection($conn);
        jsonResponse(false, 'Roll number already exists in this class');
    }
    $stmt->close();
}

// Get role_id based on selected role
$stmt = $conn->prepare("SELECT role_id FROM roles WHERE role_name = ?");
$stmt->bind_param("s", $role);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Invalid role');
}
$roleId = $result->fetch_assoc()['role_id'];
$stmt->close();

// Create user (inactive until verified)
$passwordHash = hashPassword($password);

if ($role === 'Student') {
    $stmt = $conn->prepare("INSERT INTO users (email, password_hash, name, roll_no, role_id, department_id, class_id, is_verified, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, FALSE, FALSE)");
    $stmt->bind_param("ssssiii", $email, $passwordHash, $name, $rollNo, $roleId, $departmentId, $classId);
} else {
    // Staff doesn't need class_id or roll_no
    $stmt = $conn->prepare("INSERT INTO users (email, password_hash, name, role_id, department_id, is_verified, is_active) VALUES (?, ?, ?, ?, ?, FALSE, FALSE)");
    $stmt->bind_param("ssiii", $email, $passwordHash, $name, $roleId, $departmentId);
}

if (!$stmt->execute()) {
    $error = $stmt->error;
    $stmt->close();
    closeDBConnection($conn);
    error_log("Registration failed: " . $error);
    jsonResponse(false, 'Registration failed: ' . $error);
}

$userId = $stmt->insert_id;
$stmt->close();

// Generate and save OTP
$otp = generateOTP();
$expiresAt = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));

$stmt = $conn->prepare("INSERT INTO otp_tokens (user_id, email, otp_code, otp_type, expires_at) VALUES (?, ?, ?, 'VERIFY', ?)");
$stmt->bind_param("isss", $userId, $email, $otp, $expiresAt);
$stmt->execute();
$stmt->close();

closeDBConnection($conn);

// Send registration confirmation email
sendRegistrationConfirmationEmail($email, $name);

// Send OTP email
if (sendOTPEmail($email, $otp, 'VERIFY')) {
    jsonResponse(true, 'Registration successful! Please check your email for OTP verification.', ['email' => $email]);
} else {
    jsonResponse(true, 'Registration successful! OTP: ' . $otp . ' (Email sending failed, showing OTP for testing)');
}
?>
