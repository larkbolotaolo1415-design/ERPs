<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:/xampp/htdocs/SIA/vendor/autoload.php';
require 'C:/xampp/htdocs/SIA/server/core/connection.php';

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "invalid_email";
    exit;
}

$stmt = $connection->prepare("SELECT * FROM employees WHERE LOWER(email) = LOWER(?)");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "not_found";
    exit;
}

$otp = strval(random_int(100000, 999999));
$expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

$update = $connection->prepare(
    "UPDATE employees SET otp_code = ?, otp_expiry = ? WHERE email COLLATE utf8mb4_general_ci = ?"
);
$update->bind_param("sss", $otp, $expiry, $email);
$update->execute();

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'noreply.otp.plp@gmail.com';
    $mail->Password = 'ilkgqlqrmvjxbjnt';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('noreply.otp.plp@gmail.com', 'Pharma Plus System');
    $mail->addAddress($email);

    $mail->Subject = 'Your OTP Code';
    $mail->Body = "Hello,\n\nYour One-Time Password (OTP) is: $otp\nThis code will expire in 5 minutes.\n\nPamantasan ng Lungsod ng Pasig - OTP System";

    $mail->send();
    echo "sent";

} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}

$update->close();
$stmt->close();
$connection->close();
?>
