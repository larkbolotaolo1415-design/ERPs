<?php
/**
 * PHPMailer Configuration Example
 * Document Management System
 * 
 * This file contains example code for configuring PHPMailer with Gmail SMTP.
 * Replace the placeholder credentials with your actual Gmail credentials.
 * 
 * IMPORTANT: 
 * 1. Install PHPMailer via Composer: composer require phpmailer/phpmailer
 * 2. Enable 2-Factor Authentication on your Gmail account
 * 3. Generate an App Password for this application
 * 4. Replace the placeholder credentials below
 */

// PHPMailer functionality is now active
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

function sendPasswordResetEmail($to_email, $to_name, $reset_token) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'managementsystemdocument@gmail.com';
        $mail->Password   = 'athh fkxr rvzu dhce';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('noreply@dms.com', 'Document Management System');
        $mail->addAddress($to_email, $to_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - Document Management System';
        
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])) . "/reset_password.php?token=" . $reset_token;
        
        $mail->Body = "
        <html>
        <head>
            <title>Password Reset Request</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
                .button:hover { background: #5a6fd8; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Password Reset Request</h2>
                </div>
                <div class='content'>
                    <p>Hello " . htmlspecialchars($to_name) . ",</p>
                    <p>You have requested to reset your password for the Document Management System.</p>
                    <p>Click the button below to reset your password:</p>
                    <p style='text-align: center;'>
                        <a href='" . $reset_link . "' class='button'>Reset Password</a>
                    </p>
                    <p><strong>Important:</strong> This link will expire in 30 minutes for security reasons.</p>
                    <p>If you did not request this password reset, please ignore this email and your password will remain unchanged.</p>
                    <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; background: #eee; padding: 10px; border-radius: 5px; font-family: monospace;'>" . $reset_link . "</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from Document Management System.<br>
                    Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "Password Reset Request\n\nHello " . $to_name . ",\n\nYou have requested to reset your password for the Document Management System.\n\nClick the link below to reset your password:\n" . $reset_link . "\n\nThis link will expire in 30 minutes.\n\nIf you did not request this password reset, please ignore this email.\n\nDocument Management System";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Example usage in forgot_password.php:
// Replace the mail() function call with:
/*
if (sendPasswordResetEmail($email, $user['name'], $token)) {
    $success_message = 'Password reset link has been sent to your email address.';
} else {
    $error_message = 'Failed to send email. Please try again later.';
}
*/

// Instructions for Gmail Setup:
echo "
<!-- 
GMAIL SETUP INSTRUCTIONS:

1. INSTALL PHPMailer:
   - Run: composer require phpmailer/phpmailer
   - Or download from: https://github.com/PHPMailer/PHPMailer

2. ENABLE 2-FACTOR AUTHENTICATION:
   - Go to your Google Account settings
   - Navigate to Security
   - Enable 2-Step Verification

3. GENERATE APP PASSWORD:
   - In Google Account settings, go to Security
   - Under '2-Step Verification', click 'App passwords'
   - Select 'Mail' and 'Other (custom name)'
   - Enter 'DMS System' as the name
   - Copy the generated 16-character password

4. UPDATE CREDENTIALS:
   - Replace 'your-email@gmail.com' with your Gmail address
   - Replace 'your-app-password' with the generated app password

5. UNCOMMENT THE CODE:
   - Remove the /* and */ comments around the PHPMailer code
   - Update the forgot_password.php file to use sendPasswordResetEmail()

6. TEST THE CONFIGURATION:
   - Try the forgot password functionality
   - Check your email for the reset link

SECURITY NOTES:
- Never commit real credentials to version control
- Use environment variables for production
- Consider using a dedicated email service for production
- Monitor email sending limits and quotas
-->
";
?>
