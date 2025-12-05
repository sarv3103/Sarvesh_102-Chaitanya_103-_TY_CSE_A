<?php
// Emergency fix - solve all issues immediately
require_once __DIR__ . '/config/database.php';

$conn = getDBConnection();

// Disable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Clear existing data
$conn->query("TRUNCATE TABLE classes");
$conn->query("TRUNCATE TABLE departments");

// Reset auto increment
$conn->query("ALTER TABLE departments AUTO_INCREMENT = 1");
$conn->query("ALTER TABLE classes AUTO_INCREMENT = 1");

// Insert departments
$departments = [
    ['Computer Science and Engineering', 'CSE'],
    ['Information Technology', 'IT'],
    ['Electronics and Telecommunication', 'EXTC'],
    ['Mechanical Engineering', 'MECH'],
    ['Civil Engineering', 'CIVIL']
];

foreach ($departments as $dept) {
    $stmt = $conn->prepare("INSERT INTO departments (department_name, department_code) VALUES (?, ?)");
    $stmt->bind_param("ss", $dept[0], $dept[1]);
    $stmt->execute();
    $stmt->close();
}

// Insert classes
$classes = [
    ['F.E.A', 1], ['S.Y.A', 1], ['T.Y.A', 1], ['B.E.A', 1],
    ['F.E.B', 1], ['S.Y.B', 1], ['T.Y.B', 1], ['B.E.B', 1],
    ['F.E.A', 2], ['S.Y.A', 2], ['T.Y.A', 2], ['B.E.A', 2],
    ['F.E.A', 3], ['S.Y.A', 3], ['T.Y.A', 3], ['B.E.A', 3],
    ['F.E.A', 4], ['S.Y.A', 4], ['T.Y.A', 4], ['B.E.A', 4],
    ['F.E.A', 5], ['S.Y.A', 5], ['T.Y.A', 5], ['B.E.A', 5]
];

foreach ($classes as $class) {
    $stmt = $conn->prepare("INSERT INTO classes (class_name, department_id) VALUES (?, ?)");
    $stmt->bind_param("si", $class[0], $class[1]);
    $stmt->execute();
    $stmt->close();
}

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

closeDBConnection($conn);

echo "Database fixed!";
?>