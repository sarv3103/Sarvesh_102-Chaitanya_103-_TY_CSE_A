# Email Configuration Guide for Notice Sender

This guide will help you configure email functionality for OTP sending in the Notice Sender system.

## Option 1: Gmail SMTP (Recommended for Testing)

### Step 1: Enable 2-Step Verification
1. Go to your Google Account: https://myaccount.google.com/
2. Click on "Security" in the left sidebar
3. Under "Signing in to Google", click on "2-Step Verification"
4. Follow the steps to enable 2-Step Verification

### Step 2: Generate App Password
1. Go to: https://myaccount.google.com/apppasswords
2. Select "Mail" as the app
3. Select "Other (Custom name)" as the device
4. Enter "Notice Sender" as the name
5. Click "Generate"
6. **Copy the 16-character password** (it will look like: xxxx xxxx xxxx xxxx)
7. Save this password securely - you won't be able to see it again

### Step 3: Configure in Project
1. Open `config/config.php` file
2. Update the following lines:

```php


define('SMTP_USER', 'campuschrono3103@gmail.com'); // Your Gmail address
define('SMTP_PASS', 'uzvp tqes ewor xpig'); // The 16-char App Password
define('SMTP_FROM', 'campuschrono3103@gmail.com'); // Your Gmail address
define('EMAIL_ENABLED', true); // Change from false to true
```

### Step 4: Test
1. Try registering a new student account
2. Check if OTP is received in email
3. If not received, check spam folder

## Option 2: Other Email Providers

### For Outlook/Hotmail:
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@outlook.com');
define('SMTP_PASS', 'your-password');
define('SMTP_FROM', 'your-email@outlook.com');
define('EMAIL_ENABLED', true);
```

### For Yahoo Mail:
```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@yahoo.com');
define('SMTP_PASS', 'your-app-password'); // Generate from Yahoo Account Security
define('SMTP_FROM', 'your-email@yahoo.com');
define('EMAIL_ENABLED', true);
```

## Option 3: Use Without Email (Testing Mode)

If you don't want to configure email right now:

1. Keep `EMAIL_ENABLED` set to `false` in `config/config.php`
2. OTP will be displayed on the screen instead of being sent via email
3. This is useful for development and testing

```php
define('EMAIL_ENABLED', false); // OTP will show on screen
```

## Troubleshooting

### Email Not Sending
**Problem:** OTP is not being received

**Solutions:**
1. Check spam/junk folder
2. Verify App Password is correct (no spaces)
3. Ensure 2-Step Verification is enabled
4. Check if `EMAIL_ENABLED` is set to `true`
5. Verify SMTP credentials are correct

### "Less Secure App" Error
**Problem:** Gmail blocks the login attempt

**Solution:**
- Use App Password instead of regular password
- App Passwords bypass "less secure app" restrictions

### Connection Timeout
**Problem:** SMTP connection times out

**Solutions:**
1. Check if port 587 is open on your network
2. Try port 465 with SSL instead:
```php
define('SMTP_PORT', 465);
```
3. Check firewall settings

### Wrong Email Format
**Problem:** Emails look strange or broken

**Solution:**
- The current implementation uses PHP's `mail()` function
- For production, consider using PHPMailer library for better email formatting

## Advanced: Using PHPMailer (Optional)

For production environments, it's recommended to use PHPMailer:

### Installation:
```bash
composer require phpmailer/phpmailer
```

### Update `includes/functions.php`:
```php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOTPEmail($email, $otp, $type = 'VERIFY') {
    if (!EMAIL_ENABLED) {
        return false;
    }
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = ($type === 'VERIFY') ? 'Verify Your Account' : 'Reset Your Password';
        $mail->Body = "
            <h2>Notice Sender System</h2>
            <p>Your OTP is: <strong>$otp</strong></p>
            <p>This OTP is valid for " . OTP_EXPIRY_MINUTES . " minutes.</p>
            <p>If you didn't request this, please ignore this email.</p>
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}
```

## Security Best Practices

1. **Never commit credentials to Git**
   - Add `config/config.php` to `.gitignore`
   - Use environment variables in production

2. **Use App Passwords**
   - Never use your main email password
   - App passwords can be revoked if compromised

3. **Enable 2FA**
   - Always enable 2-Factor Authentication on your email account

4. **Monitor Usage**
   - Check for unusual email sending activity
   - Gmail has daily sending limits (500 emails/day for free accounts)

## Testing Checklist

- [ ] SMTP credentials configured
- [ ] EMAIL_ENABLED set to true
- [ ] Test student registration
- [ ] OTP received in email
- [ ] OTP verification works
- [ ] Test forgot password
- [ ] Password reset OTP received
- [ ] Check spam folder if not in inbox

## Support

If you continue to have issues:
1. Check PHP error logs
2. Verify your email provider's SMTP settings
3. Test with a different email provider
4. Use testing mode (EMAIL_ENABLED = false) temporarily

## Production Recommendations

For production deployment:
1. Use a dedicated email service (SendGrid, Mailgun, AWS SES)
2. Implement email queuing for better performance
3. Add email templates for better formatting
4. Monitor email delivery rates
5. Implement rate limiting to prevent abuse
