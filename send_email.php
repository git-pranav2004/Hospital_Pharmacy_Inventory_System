<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Gmail's SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = '22bt04118@gsfcuniversity.ac.in'; // Your Gmail address
    $mail->Password = 'Pranav$2004';   // Your Gmail password or App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use SSL
    $mail->Port = 587;
    $mail->SMTPDebug = 2; // Output detailed debug information

    $mail->setFrom('fromhpis@gmail.com', 'Your Name');
    $mail->addAddress('recipient_email@example.com', 'Recipient Name'); // Add a recipient

    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body    = 'This is a test email sent via Gmail SMTP using PHPMailer.';

    $mail->send();
    echo 'Email sent successfully!';
} catch (Exception $e) {
    echo 'Email could not be sent. Error: ', $mail->ErrorInfo;
}
?>