<?php
require __DIR__ . '/../../../vendor/autoload.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Enable verbose debug output (shows SMTP conversation)
    $mail->SMTPDebug = 2; 
    $mail->Debugoutput = 'html'; // outputs debug in browser
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'noreply.otp.plp@gmail.com'; 
    $mail->Password   = 'ilkgqlqrmvjxbjnt'; // your app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender & recipient
    $mail->setFrom('noreply.otp.plp@gmail.com', 'Pharma Plus System');
    $mail->addAddress('campos_charlesdustin@plpasig.edu.ph'); 

    // Email content
    $mail->Subject = 'SMTP Debug Test';
    $mail->Body    = 'Hello! This is a debug test for Gmail SMTP using PHPMailer.';

    $mail->send();
    echo "<p>Email sent successfully!</p>";
} catch (Exception $e) {
    echo "<p>Mailer Error: " . $mail->ErrorInfo . "</p>";
}
?>
