<?php
session_start();
require 'db.php';
require 'vendor/autoload.php'; // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];

    // Fetch the receptionist's data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND role = 'Receptionist'");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Password matches, set session variables
        $_SESSION['is_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Send login email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = '22bt04118@gsfcuniversity.ac.in';
            $mail->Password = 'Pranav$2004';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('22bt04118@gsfcuniversity.ac.in', 'Hospital Pharmacy System');
            $mail->addAddress($user['email']);
            $mail->Subject = 'Login Notification';
            $mail->Body = "Hi {$user['username']},\n\nYou have successfully logged in as a Receptionist.\n\nRegards,\nHospital Pharmacy Team";

            $mail->send();
        } catch (Exception $e) {
            $error = "Logged in, but email could not be sent.";
        }

        // Redirect to Receptionist Dashboard
        header('Location: receptionist_dashboard.php');
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receptionist Login</title>
</head>
<body>
    <h2>Receptionist Login</h2>
    <?php if ($error): ?><p style="color:red;"><?php echo $error; ?></p><?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>