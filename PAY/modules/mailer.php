<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

function sendMail($to, $subject, $body)
{
    $mail = new PHPMailer(true);

    // TODO: ilagay yung ilang details here sa config for easier configurations

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'payrollforgotpass@gmail.com';
        $mail->Password = 'cwot tods oyqf avts';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('payrollforgotpass@gmail.com', 'Payroll System');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
