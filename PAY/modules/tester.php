<?php
include MODULES_PATH . 'mailer.php';

$test = sendMail('cjdp.pogi@gmail.com', 'Test Email', '<p>This is a test message!</p>');

if ($test === true) {
    echo "Mail sent successfully!";
} else {
    echo "Mailer error: " . $test;
}
