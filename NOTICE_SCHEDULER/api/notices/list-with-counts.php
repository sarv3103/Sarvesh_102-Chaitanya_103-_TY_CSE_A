<?php
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

$user = getCurrentUser();
$conn = getDBConnection();

$notices = [];

if ($user['role_name'] === 'Admin') {
    // Admin sees all notices
    $query = "SELECT n.*, u.name as sender_name, u.email as sender_email,
              (SELECT COUNT(*) FROM notice_views WHERE notice_id = n.notice_id) as view_count,
              (SELECT COUNT(*) FROM comments WHERE notice_id = n.notice_id) as comment_count
              FROM notices n 
              JOIN users u ON n.sent_by_user_id = u.user_id 
              ORDER BY n.created_at DESC";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        // Get targeting info
        $row['target_info'] = getNoticeTargetInfo($conn, $row['notice_id']);
        $notices[] = $row;
    }
} elseif ($user['role_name'] === 'Staff') {
    // Staff sees: staff-only notices + all student notices
    $query = "SELECT DISTINCT n.*, u.name as sender_name, u.email as sender_email,
              (SELECT COUNT(*) FROM notice_views WHERE notice_id = n.notice_id) as view_count,
              (SELECT COUNT(*) FROM comments WHERE notice_id = n.notice_id) as comment_count
              FROM notices n 
              JOIN users u ON n.sent_by_user_id = u.user_id 
              LEFT JOIN notice_targets nt ON n.notice_id = nt.notice_id 
              WHERE n.is_staff_only = 1 
              OR nt.target_role_id = 3 
              OR nt.target_role_id = 2
              ORDER BY n.created_at DESC";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        // Get targeting info
        $row['target_info'] = getNoticeTargetInfo($conn, $row['notice_id']);
        $notices[] = $row;
    }
} elseif ($user['role_name'] === 'Student') {
    // Students see notices targeted to them
    $studentRoleId = 3;
    $classId = $user['class_id'];
    
    $query = "SELECT DISTINCT n.*, u.name as sender_name, u.email as sender_email,
              (SELECT COUNT(*) FROM notice_views WHERE notice_id = n.notice_id) as view_count,
              (SELECT COUNT(*) FROM comments WHERE notice_id = n.notice_id) as comment_count
              FROM notices n 
              JOIN users u ON n.sent_by_user_id = u.user_id 
              JOIN notice_targets nt ON n.notice_id = nt.notice_id 
              WHERE n.is_staff_only = 0 
              AND nt.target_role_id = ? 
              AND (nt.target_class_id IS NULL OR nt.target_class_id = ?)
              ORDER BY n.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $studentRoleId, $classId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Get targeting info
        $row['target_info'] = getNoticeTargetInfo($conn, $row['notice_id']);
        $notices[] = $row;
    }
    $stmt->close();
}

// Function to get notice targeting information
function getNoticeTargetInfo($conn, $noticeId) {
    $query = "SELECT nt.target_role_id, nt.target_class_id, 
              c.class_name, d.department_name, d.department_code
              FROM notice_targets nt
              LEFT JOIN classes c ON nt.target_class_id = c.class_id
              LEFT JOIN departments d ON c.department_id = d.department_id
              WHERE nt.notice_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $noticeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $targets = [];
    while ($row = $result->fetch_assoc()) {
        $targets[] = $row;
    }
    $stmt->close();
    
    // Determine target type
    if (empty($targets)) {
        return ['type' => 'unknown', 'label' => ''];
    }
    
    $hasStaff = false;
    $hasStudents = false;
    $classes = [];
    $departments = [];
    
    foreach ($targets as $target) {
        if ($target['target_role_id'] == 2) {
            $hasStaff = true;
        }
        if ($target['target_role_id'] == 3) {
            $hasStudents = true;
            if ($target['target_class_id']) {
                $classes[] = $target['department_code'] . ' ' . $target['class_name'];
                if (!in_array($target['department_code'], $departments)) {
                    $departments[] = $target['department_code'];
                }
            }
        }
    }
    
    // Determine label
    if ($hasStaff && !$hasStudents) {
        return ['type' => 'staff_only', 'label' => 'Staff Only'];
    } elseif ($hasStaff && $hasStudents && empty($classes)) {
        return ['type' => 'everyone', 'label' => 'Everyone'];
    } elseif ($hasStudents && empty($classes)) {
        return ['type' => 'all_students', 'label' => 'All Students'];
    } elseif (!empty($classes)) {
        if (count($classes) == 1) {
            return ['type' => 'specific_class', 'label' => $classes[0]];
        } else {
            // Multiple classes - check if same department
            if (count(array_unique($departments)) == 1) {
                return ['type' => 'department', 'label' => $departments[0] . ' (' . count($classes) . ' classes)'];
            } else {
                return ['type' => 'multiple_classes', 'label' => count($classes) . ' Classes'];
            }
        }
    }
    
    return ['type' => 'unknown', 'label' => ''];
}

closeDBConnection($conn);

jsonResponse(true, 'Notices retrieved successfully', $notices);
?>
