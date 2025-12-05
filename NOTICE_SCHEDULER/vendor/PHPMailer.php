<?php
/**
 * PHPMailer - PHP email creation and transport class.
 * Simplified version for CampusChrono
 */

namespace PHPMailer\PHPMailer;

class PHPMailer
{
    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS = 'ssl';
    
    public $isSMTP = false;
    public $Host = '';
    public $SMTPAuth = false;
    public $Username = '';
    public $Password = '';
    public $SMTPSecure = '';
    public $Port = 587;
    public $From = '';
    public $FromName = '';
    public $Subject = '';
    public $Body = '';
    public $isHTML = false;
    
    private $to = [];
    private $errorInfo = '';
    
    public function isSMTP()
    {
        $this->isSMTP = true;
    }
    
    public function addAddress($address, $name = '')
    {
        $this->to[] = ['address' => $address, 'name' => $name];
    }
    
    public function send()
    {
        if (!$this->isSMTP) {
            return $this->sendMail();
        }
        
        return $this->sendSMTP();
    }
    
    private function sendMail()
    {
        $headers = "From: {$this->FromName} <{$this->From}>\r\n";
        $headers .= "Reply-To: {$this->From}\r\n";
        $headers .= "X-Mailer: PHPMailer\r\n";
        
        if ($this->isHTML) {
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        }
        
        foreach ($this->to as $recipient) {
            $result = mail($recipient['address'], $this->Subject, $this->Body, $headers);
            if (!$result) {
                $this->errorInfo = 'Mail function failed';
                return false;
            }
        }
        
        return true;
    }
    
    private function sendSMTP()
    {
        // Simple SMTP implementation
        $socket = fsockopen($this->Host, $this->Port, $errno, $errstr, 30);
        
        if (!$socket) {
            $this->errorInfo = "Connection failed: $errstr ($errno)";
            return false;
        }
        
        // Read server response
        $response = fgets($socket, 512);
        if (substr($response, 0, 3) != '220') {
            $this->errorInfo = "Server not ready: $response";
            fclose($socket);
            return false;
        }
        
        // EHLO
        fputs($socket, "EHLO localhost\r\n");
        $response = fgets($socket, 512);
        
        // STARTTLS
        if ($this->SMTPSecure == 'tls') {
            fputs($socket, "STARTTLS\r\n");
            $response = fgets($socket, 512);
            if (substr($response, 0, 3) != '220') {
                $this->errorInfo = "STARTTLS failed: $response";
                fclose($socket);
                return false;
            }
            
            // Enable crypto
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                $this->errorInfo = "TLS encryption failed";
                fclose($socket);
                return false;
            }
            
            // EHLO again after STARTTLS
            fputs($socket, "EHLO localhost\r\n");
            $response = fgets($socket, 512);
        }
        
        // AUTH LOGIN
        if ($this->SMTPAuth) {
            fputs($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 512);
            if (substr($response, 0, 3) != '334') {
                $this->errorInfo = "AUTH failed: $response";
                fclose($socket);
                return false;
            }
            
            // Send username
            fputs($socket, base64_encode($this->Username) . "\r\n");
            $response = fgets($socket, 512);
            if (substr($response, 0, 3) != '334') {
                $this->errorInfo = "Username rejected: $response";
                fclose($socket);
                return false;
            }
            
            // Send password
            fputs($socket, base64_encode($this->Password) . "\r\n");
            $response = fgets($socket, 512);
            if (substr($response, 0, 3) != '235') {
                $this->errorInfo = "Password rejected: $response";
                fclose($socket);
                return false;
            }
        }
        
        // MAIL FROM
        fputs($socket, "MAIL FROM: <{$this->From}>\r\n");
        $response = fgets($socket, 512);
        if (substr($response, 0, 3) != '250') {
            $this->errorInfo = "MAIL FROM rejected: $response";
            fclose($socket);
            return false;
        }
        
        // RCPT TO
        foreach ($this->to as $recipient) {
            fputs($socket, "RCPT TO: <{$recipient['address']}>\r\n");
            $response = fgets($socket, 512);
            if (substr($response, 0, 3) != '250') {
                $this->errorInfo = "RCPT TO rejected: $response";
                fclose($socket);
                return false;
            }
        }
        
        // DATA
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, 512);
        if (substr($response, 0, 3) != '354') {
            $this->errorInfo = "DATA rejected: $response";
            fclose($socket);
            return false;
        }
        
        // Email headers and body
        $email = "From: {$this->FromName} <{$this->From}>\r\n";
        $email .= "To: " . $this->to[0]['address'] . "\r\n";
        $email .= "Subject: {$this->Subject}\r\n";
        if ($this->isHTML) {
            $email .= "Content-Type: text/html; charset=UTF-8\r\n";
        }
        $email .= "\r\n";
        $email .= $this->Body . "\r\n";
        $email .= ".\r\n";
        
        fputs($socket, $email);
        $response = fgets($socket, 512);
        if (substr($response, 0, 3) != '250') {
            $this->errorInfo = "Message rejected: $response";
            fclose($socket);
            return false;
        }
        
        // QUIT
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        return true;
    }
    
    public function getErrorInfo()
    {
        return $this->errorInfo;
    }
}

class Exception extends \Exception
{
}
?>