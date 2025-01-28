<?php
session_start();
require 'db.php'; // Include your database connection file
require 'vendor/autoload.php'; // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function for decryption
function str_openssl_dec($encryptedStr, $iv) {
    $key = "!1@2#3$4%5^6&7*8"; // Use the same key as during encryption
    $cipher = "aes-256-cbc";
    $options = 0;
    return openssl_decrypt($encryptedStr, $cipher, $key, $options, $iv);
}

$error = '';
$success = '';

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed!");
    }

    // Get and sanitize input values
    $inputUsername = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $inputPassword = $_POST['password'];

    // Generate the same IV used during encryption
    $iv = '1234567890123456'; // Ensure this matches the IV used in encryption

    // Fetch all admin users from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'Admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll();

    $authenticated = false;

    foreach ($admins as $admin) {
        // Decrypt the username
        $decryptedUsername = str_openssl_dec($admin['username'], $iv);
        $decryptedEmail = str_openssl_dec($admin['email'], $iv);

        // Validate username and password
        if ($decryptedUsername === $inputUsername && password_verify($inputPassword, $admin['password_hash'])) {
            // Login successful
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['username'] = $decryptedUsername;

            // Optional: Send login success email
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = '22bt04118@gsfcuniversity.ac.in';
                $mail->Password = 'Pranav$2004';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('22bt04118@gsfcuniversity.ac.in', 'Hospital Pharmacy Inventory');
                $mail->addAddress($admin['email']);

                $mail->Subject = 'Login Successful';
                $mail->Body = "Hi $decryptedUsername,\n\nYou have successfully logged in to the Hospital Pharmacy Inventory System.\n\nBest Regards,\nTeam";

                $mail->send();
            } catch (Exception $e) {
                // If email cannot be sent, proceed
            }

            header('Location: admin_dashboard.php');
            exit;
        }
    }

    $error = "Invalid credentials or unauthorized access!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom, #007bff, #6c757d);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .container {
            max-width: 400px;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .alert {
            margin-bottom: 20px;
            font-size: 14px;
        }

        .form-label {
            font-weight: bold;
            color: #333;
        }

        .form-control {
            border-radius: 5px;
        }

        .btn-primary {
            width: 100%;
            padding: 10px;
            background: #007bff;
            border: none;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .form-check-label {
            margin-left: 5px;
            font-size: 14px;
            color: #333;
        }

        .toggle-password {
            cursor: pointer;
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: #007bff;
        }

        .input-group-text:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Admin Login</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" required>
                    <span class="input-group-text toggle-password" onclick="togglePasswordVisibility()">
                        <i id="password-icon" class="bi bi-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="rememberMe">
                <label class="form-check-label" for="rememberMe">Remember Me</label>
            </div>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('bi-eye');
                passwordIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('bi-eye-slash');
                passwordIcon.classList.add('bi-eye');
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
</body>
</html>