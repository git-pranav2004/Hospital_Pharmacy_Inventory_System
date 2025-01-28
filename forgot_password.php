<?php
session_start();
require 'db.php';
require 'aes_helpers.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

// Forgot Password Feature
if (isset($_POST['forgot_password'])) {
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');

    // Check if username exists
    $hashedUsername = hash('sha256', $username);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND role = 'Admin'");
    $stmt->execute([':username' => $hashedUsername]);
    $admin = $stmt->fetch();

    if ($admin) {
        // Generate reset token and expiry
        $resetToken = bin2hex(random_bytes(32));
        $resetExpiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valid for 1 hour

        // Store reset token in database
        $stmt = $pdo->prepare("UPDATE users SET reset_token = :token, reset_token_expiry = :expiry WHERE id = :id");
        $stmt->execute([
            ':token' => $resetToken,
            ':expiry' => $resetExpiry,
            ':id' => $admin['id']
        ]);

        // Send reset email
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@example.com'; // Your SMTP email address
            $mail->Password = 'your-email-password'; // Your SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('your-email@example.com', 'Hospital Pharmacy Inventory');
            $mail->addAddress($admin['email']);

            $resetLink = "http://yourdomain.com/reset_password.php?token=$resetToken";

            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Hi $username,\n\nYou requested a password reset. Click the link below to reset your password:\n\n$resetLink\n\nThis link will expire in 1 hour.\n\nBest Regards,\nTeam";

            if ($mail->send()) {
                $success = "A password reset link has been sent to your registered email address.";
            } else {
                $error = "Failed to send the email. Please try again later.";
            }
        } catch (Exception $e) {
            $error = "Mailer Error: " . $mail->ErrorInfo;
        }
    } else {
        $error = "Username not found.";
    }
}
?>