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

$userIds = $data['user_ids'] ?? [];
$updateField = sanitizeInput($data['update_field'] ?? ''); // 'class', 'department', 'role'
$newValue = intval($data['new_value'] ?? 0);

if (empty($userIds) || empty($updateField) || $newValue <= 0) {
    jsonResponse(false, 'User IDs, update field, and new value are required');
}

if (!in_array($updateField, ['class_id', 'department_id', 'role_id'])) {
    jsonResponse(false, 'Invalid update field');
}

$conn = getDBConnection();

// Build query based on field
$placeholders = implode(',', array_fill(0, count($userIds), '?'));
$query = "UPDATE users SET $updateField = ? WHERE user_id IN ($placeholders)";

$stmt = $conn->prepare($query);

// Bind parameters
$types = 'i' . str_repeat('i', count($userIds)); // First 'i' for new_value, rest for user_ids
$params = array_merge([$newValue], $userIds);

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(true, "$affectedRows users updated successfully");
} else {
    $stmt->close();
    closeDBConnection($conn);
    jsonResponse(false, 'Failed to update users');
}
?>
