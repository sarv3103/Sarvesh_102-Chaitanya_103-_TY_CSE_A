<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Update last activity timestamp
if (isLoggedIn()) {
    $_SESSION['last_activity'] = time();
}

// Check session timeout (30 minutes of inactivity)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    jsonResponse(false, 'Session expired due to inactivity');
}

if (!isLoggedIn()) {
    jsonResponse(false, 'Not authenticated');
}

$user = getCurrentUser();

if (!$user) {
    jsonResponse(false, 'User not found');
}

jsonResponse(true, 'Authenticated', [
    'user_id' => $user['user_id'],
    'name' => $user['name'],
    'email' => $user['email'],
    'role' => $user['role_name'],
    'department_id' => $user['department_id'],
    'class_id' => $user['class_id']
]);
?>
