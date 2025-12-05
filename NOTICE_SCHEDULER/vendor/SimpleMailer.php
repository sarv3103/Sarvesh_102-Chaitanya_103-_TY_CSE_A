<?php
/**
 * Simple SMTP Mailer for CampusChrono
 * Works with Gmail SMTP and app passwords
 */

class SimpleMailer {
    private $host;
    private $port;
    private $username;
    private $password;
    private $from;
    private $fromName;
    
    public function __construct($host, $port, $username, $password, $from, $fromName) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->from = $from;
        $this->fromName = $fromName;
    }
    
    public function send($to, $subject, $message) {
        // Use PHP's mail() function with proper headers for Gmail
        $headers = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/plain; charset=UTF-8";
        $headers[] = "From: {$this->fromName} <{$this->from}>";
        $headers[] = "Reply-To: {$this->from}";
        $headers[] = "Subject: {$subject}";
        $headers[] = "X-Mailer: CampusChrono";
        
        // Configure PHP mail settings
        ini_set('SMTP', $this->host);
        ini_set('smtp_port', $this->port);
        ini_set('sendmail_from', $this->from);
        
        // Try to send email
        $result = mail($to, $subject, $message, implode("\r\n", $headers));
        
        if (!$result) {
            // If mail() fails, try with authentication headers
            $auth_headers = $headers;
            $auth_headers[] = "Authentication-Results: smtp.gmail.com";
            
            $result = mail($to, $subject, $message, implode("\r\n", $auth_headers));
        }
        
        return $result;
    }
}
?>