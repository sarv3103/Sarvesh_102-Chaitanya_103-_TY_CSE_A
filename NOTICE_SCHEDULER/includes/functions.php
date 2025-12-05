<?php
require_once __DIR__ . '/../config/config.php';

// Generate OTP
function generateOTP() {
    return str_pad(rand(0, 999999), OTP_LENGTH, '0', STR_PAD_LEFT);
}

// Send email with OTP using SMTP
function sendOTPEmail($email, $otp, $type = 'VERIFY') {
    // If email is not enabled, return false to show OTP on screen
    if (!EMAIL_ENABLED) {
        return false;
    }
    
    $subject = ($type === 'VERIFY') ? 'Verify Your Account - CampusChrono' : 'Reset Your Password - CampusChrono';
    $message = "Hello,\n\n";
    $message .= "Your OTP is: $otp\n\n";
    $message .= "This OTP is valid for " . OTP_EXPIRY_MINUTES . " minutes.\n\n";
    $message .= "If you didn't request this, please ignore this email.\n\n";
    $message .= "Regards,\n" . SMTP_FROM_NAME;
    
    return sendSMTPEmail($email, $subject, $message);
}

// Send registration confirmation email (with OTP)
function sendRegistrationConfirmationEmail($email, $name) {
    // This is now just for logging - OTP email is the main one
    // Keeping for backward compatibility
    return true;
}

// SMTP Email Function using PHPMailer with STARTTLS support
function sendSMTPEmail($to, $subject, $message) {
    if (!EMAIL_ENABLED) {
        return false;
    }
    
    require_once __DIR__ . '/../vendor/PHPMailer-6.9.1/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/PHPMailer-6.9.1/src/SMTP.php';
    require_once __DIR__ . '/../vendor/PHPMailer-6.9.1/src/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email failed to send to: $to - Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Fallback email function (not used but kept for reference)
function sendEmailWithPassword($to, $subject, $message, $password) {
    return sendSMTPEmail($to, $subject, $message);
}

// Send application submitted email (after OTP verification)
function sendApplicationSubmittedEmail($email, $name) {
    if (!EMAIL_ENABLED) {
        return false;
    }
    
    $subject = 'Application Submitted for Approval - CampusChrono';
    $message = "Dear $name,\n\n";
    $message .= "Thank you for registering with CampusChrono!\n\n";
    $message .= "Your email has been verified successfully and your application has been submitted to the administrator for approval.\n\n";
    $message .= "You will receive another email once your account is reviewed. This usually takes 1-2 business days.\n\n";
    $message .= "What happens next:\n";
    $message .= "- Admin will review your application\n";
    $message .= "- If approved, you'll receive a confirmation email and can login\n";
    $message .= "- If rejected, you'll receive an email with the reason\n\n";
    $message .= "If you have any questions, please contact your institution's administrator.\n\n";
    $message .= "Best regards,\n";
    $message .= "CampusChrono Team\n";
    $message .= SMTP_FROM_NAME;
    
    return sendSMTPEmail($email, $subject, $message);
}

// Send account approval email
function sendApprovalEmail($email, $name) {
    if (!EMAIL_ENABLED) {
        return false;
    }
    
    $subject = 'Congratulations! Your Account Has Been Approved - CampusChrono';
    $message = "Dear $name,\n\n";
    $message .= "Congratulations! We are pleased to inform you that your CampusChrono account has been approved by the administrator.\n\n";
    $message .= "Your account is now active and ready to use!\n\n";
    $message .= "You can now log in to your account and start using all the features of CampusChrono:\n";
    $message .= "- View notices from your institution\n";
    $message .= "- Comment on notices\n";
    $message .= "- Stay updated with important announcements\n\n";
    $message .= "Login URL: " . BASE_URL . "\n";
    $message .= "Use your registered email and password to login.\n\n";
    $message .= "If you have any questions or need assistance, please contact your institution's administrator.\n\n";
    $message .= "Welcome to CampusChrono! We're excited to have you on board.\n\n";
    $message .= "Best regards,\n";
    $message .= "CampusChrono Team\n";
    $message .= SMTP_FROM_NAME;
    
    return sendSMTPEmail($email, $subject, $message);
}

// Send account rejection email
function sendRejectionEmail($email, $name, $reason) {
    if (!EMAIL_ENABLED) {
        return false;
    }
    
    $subject = 'Application Status: Rejected - CampusChrono';
    $message = "Dear $name,\n\n";
    $message .= "We regret to inform you that your CampusChrono registration application has been rejected by the administrator.\n\n";
    $message .= "Reason for rejection:\n";
    $message .= "\"$reason\"\n\n";
    $message .= "What you can do:\n";
    $message .= "- Review the rejection reason carefully\n";
    $message .= "- Contact your institution's administrator if you have questions\n";
    $message .= "- If eligible, you may re-register with correct information\n\n";
    $message .= "If you believe this is an error or need clarification, please contact your institution's administrator immediately.\n\n";
    $message .= "We apologize for any inconvenience.\n\n";
    $message .= "Best regards,\n";
    $message .= "CampusChrono Team\n";
    $message .= SMTP_FROM_NAME;
    
    return sendSMTPEmail($email, $subject, $message);
}

// Upload file
function uploadFile($file, $noticeId) {
    $uploadDir = __DIR__ . '/../uploads/notices/';
    
    // Create directory if not exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and PDF allowed.'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large. Maximum 5MB allowed.'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'notice_' . $noticeId . '_' . time() . '_' . uniqid() . '.' . $extension;
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return [
            'success' => true,
            'file_name' => $file['name'],
            'file_path' => 'uploads/notices/' . $fileName,
            'file_type' => $file['type'],
            'file_size' => $file['size']
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to upload file.'];
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = getDBConnection();
    $userId = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT u.*, r.role_name, c.class_name, d.department_name 
                            FROM users u 
                            JOIN roles r ON u.role_id = r.role_id 
                            LEFT JOIN classes c ON u.class_id = c.class_id 
                            LEFT JOIN departments d ON u.department_id = d.department_id 
                            WHERE u.user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $stmt->close();
    closeDBConnection($conn);
    
    return $user;
}

// Check user role
function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role_name'] === $role;
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

// JSON response
function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
?>
