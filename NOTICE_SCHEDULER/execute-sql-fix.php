<?php
// Execute SQL fix for database
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔧 Executing database fix...\n";
    
    // Read and execute SQL file
    $sql = file_get_contents('quick-fix.sql');
    
    // Split by semicolon and execute each statement
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            echo "Executing: " . substr($statement, 0, 50) . "...\n";
            $pdo->exec($statement);
        }
    }
    
    echo "✅ Database fix completed successfully!\n";
    
    // Verify departments
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM departments");
    $deptCount = $stmt->fetch()['count'];
    echo "📊 Departments created: $deptCount\n";
    
    // Verify classes
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM classes");
    $classCount = $stmt->fetch()['count'];
    echo "📊 Classes created: $classCount\n";
    
    // Show sample data
    echo "\n📋 Sample departments:\n";
    $stmt = $pdo->query("SELECT department_id, department_name, department_code FROM departments LIMIT 5");
    while ($row = $stmt->fetch()) {
        echo "  {$row['department_id']}: {$row['department_name']} ({$row['department_code']})\n";
    }
    
    echo "\n📋 Sample classes:\n";
    $stmt = $pdo->query("SELECT c.class_id, c.class_name, d.department_code FROM classes c JOIN departments d ON c.department_id = d.department_id LIMIT 10");
    while ($row = $stmt->fetch()) {
        echo "  {$row['class_id']}: {$row['class_name']} - {$row['department_code']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>