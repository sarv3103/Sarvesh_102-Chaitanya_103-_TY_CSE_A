<?php
/**
 * Database Fix Script - Execute this to fix department/class issues
 * Run this in browser: http://localhost/NOTICE_SCHEDULER/fix-database-now.php
 */

require_once 'config/database.php';

echo "<h1>🔧 CampusChrono Database Fix</h1>";
echo "<pre>";

$conn = getDBConnection();

if (!$conn) {
    die("❌ Database connection failed!");
}

echo "✅ Database connected\n\n";

// Disable foreign key checks
echo "🔄 Disabling foreign key checks...\n";
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Clear dependent tables
echo "🗑️ Clearing dependent tables...\n";
$conn->query("DELETE FROM notice_targets");
echo "  - Cleared notice_targets\n";

$conn->query("DELETE FROM notice_views");
echo "  - Cleared notice_views\n";

$conn->query("DELETE FROM comments");
echo "  - Cleared comments\n";

$conn->query("DELETE FROM notices");
echo "  - Cleared notices\n";

$conn->query("DELETE FROM users WHERE role_id != 1");
echo "  - Cleared non-admin users\n";

$conn->query("DELETE FROM classes");
echo "  - Cleared classes\n";

$conn->query("DELETE FROM departments");
echo "  - Cleared departments\n";

// Reset auto increment
echo "\n🔄 Resetting auto increment...\n";
$conn->query("ALTER TABLE departments AUTO_INCREMENT = 1");
$conn->query("ALTER TABLE classes AUTO_INCREMENT = 1");
echo "  - Reset complete\n";

// Insert departments
echo "\n📚 Creating departments...\n";
$departments = [
    ['Computer Science and Engineering', 'CSE'],
    ['Information Technology', 'IT'],
    ['Electronics and Telecommunication', 'EXTC'],
    ['Mechanical Engineering', 'MECH'],
    ['Civil Engineering', 'CIVIL'],
    ['Electrical Engineering', 'EE'],
    ['Chemical Engineering', 'CHEM']
];

$stmt = $conn->prepare("INSERT INTO departments (department_name, department_code) VALUES (?, ?)");
foreach ($departments as $dept) {
    $stmt->bind_param("ss", $dept[0], $dept[1]);
    $stmt->execute();
    echo "  ✓ {$dept[1]} - {$dept[0]}\n";
}
$stmt->close();

// Insert classes
echo "\n🏫 Creating classes...\n";
$classes = [
    // CSE Classes (dept_id = 1)
    ['F.E.A', 1], ['S.Y.A', 1], ['T.Y.A', 1], ['B.E.A', 1],
    ['F.E.B', 1], ['S.Y.B', 1], ['T.Y.B', 1], ['B.E.B', 1],
    // IT Classes (dept_id = 2)
    ['F.E.A', 2], ['S.Y.A', 2], ['T.Y.A', 2], ['B.E.A', 2],
    ['F.E.B', 2], ['S.Y.B', 2], ['T.Y.B', 2], ['B.E.B', 2],
    // EXTC Classes (dept_id = 3)
    ['F.E.A', 3], ['S.Y.A', 3], ['T.Y.A', 3], ['B.E.A', 3],
    // MECH Classes (dept_id = 4)
    ['F.E.A', 4], ['S.Y.A', 4], ['T.Y.A', 4], ['B.E.A', 4],
    // CIVIL Classes (dept_id = 5)
    ['F.E.A', 5], ['S.Y.A', 5], ['T.Y.A', 5], ['B.E.A', 5],
    // EE Classes (dept_id = 6)
    ['F.E.A', 6], ['S.Y.A', 6], ['T.Y.A', 6], ['B.E.A', 6],
    // CHEM Classes (dept_id = 7)
    ['F.E.A', 7], ['S.Y.A', 7], ['T.Y.A', 7], ['B.E.A', 7]
];

$stmt = $conn->prepare("INSERT INTO classes (class_name, department_id) VALUES (?, ?)");
$count = 0;
foreach ($classes as $class) {
    $stmt->bind_param("si", $class[0], $class[1]);
    $stmt->execute();
    $count++;
}
$stmt->close();
echo "  ✓ Created {$count} classes\n";

// Re-enable foreign key checks
echo "\n🔄 Re-enabling foreign key checks...\n";
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Verify data
echo "\n✅ Verification:\n";
$result = $conn->query("SELECT COUNT(*) as count FROM departments");
$deptCount = $result->fetch_assoc()['count'];
echo "  - Departments: {$deptCount}\n";

$result = $conn->query("SELECT COUNT(*) as count FROM classes");
$classCount = $result->fetch_assoc()['count'];
echo "  - Classes: {$classCount}\n";

// Show sample data
echo "\n📋 Sample Department-Class Mapping:\n";
$result = $conn->query("
    SELECT d.department_code, c.class_id, c.class_name 
    FROM classes c 
    JOIN departments d ON c.department_id = d.department_id 
    ORDER BY c.class_id 
    LIMIT 10
");
while ($row = $result->fetch_assoc()) {
    echo "  - Class ID {$row['class_id']}: {$row['department_code']} {$row['class_name']}\n";
}

closeDBConnection($conn);

echo "\n🎉 Database fix complete!\n";
echo "\n✅ You can now:\n";
echo "  1. Register new users\n";
echo "  2. Test email system\n";
echo "  3. Use all features\n";
echo "\n⚠️ IMPORTANT: Delete this file after running for security!\n";
echo "</pre>";
?>
