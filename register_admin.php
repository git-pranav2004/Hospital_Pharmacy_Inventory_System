<?php
session_start();
require 'db.php'; // Include your database connection file
require 'vendor/autoload.php'; // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

// Encryption Function
function str_openssl_enc($str, $iv) {
    $key = "!1@2#3$4%5^6&7*8"; // Replace with a strong, secure key
    $cipher = "aes-256-cbc";
    $options = 0;
    return openssl_encrypt($str, $cipher, $key, $options, $iv);
}

// Decryption Function
function str_openssl_dec($encryptedStr, $iv) {
    $key = "!1@2#3$4%5^6&7*8"; // Ensure the same key is used as in the encryption
    $cipher = "aes-256-cbc";
    $options = 0;
    return openssl_decrypt($encryptedStr, $cipher, $key, $options, $iv);
}

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
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Generate an IV (Initialization Vector) for AES
    $iv = '1234567890123456'; // Must be 16 bytes for AES-256-CBC

    // Encrypt the username
    $encryptedUsername = str_openssl_enc($username, $iv);
    $encryptedEmail = str_openssl_enc($email, $iv);
    $encryptedPhone = str_openssl_enc($phone, $iv);

    // Check if the username or email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
    $stmt->execute([':username' => $encryptedUsername, ':email' => $encryptedEmail]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        $error = "Username or Email already exists. Please choose a different one.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format. Please enter a valid email.";
    } elseif (!preg_match('/^\d{10}$/', $phone)) {
        $error = "Invalid phone number. Please enter a 10-digit phone number.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match. Please confirm your password.";
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert the new admin into the database
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, phone, password_hash, role) 
            VALUES (:username, :email, :phone, :password_hash, 'Admin')
        ");
        $stmt->execute([
            ':username' => $encryptedUsername,
            ':email' => $encryptedEmail,
            ':phone' => $encryptedPhone,
            ':password_hash' => $hashedPassword
        ]);

        // Send registration success email
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
            $mail->addAddress($email);

            $mail->Subject = 'Registration Successful!';
            $mail->Body = "Hi $username,\n\nYou have successfully registered as an Admin in the Hospital Pharmacy Inventory System.\n\nBest Regards,\nTeam";

            $mail->send();
            $success = "Admin registered successfully! A confirmation email has been sent.";
        } catch (Exception $e) {
            $success = "Admin registered successfully, but the email could not be sent.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
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
        <h2>Admin Registration</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="text" name="phone" id="phone" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" required>
                    <span class="input-group-text toggle-password" onclick="togglePasswordVisibility('password')">
                        <i id="password-icon" class="bi bi-eye"></i>
                    </span>
                </div>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    <span class="input-group-text toggle-password" onclick="togglePasswordVisibility('confirm_password')">
                        <i id="confirm-password-icon" class="bi bi-eye"></i>
                    </span>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
    <script>
        function togglePasswordVisibility(id) {
            const passwordInput = document.getElementById(id);
            const passwordIcon = id === 'password' ? 'password-icon' : 'confirm-password-icon';
            const icon = document.getElementById(passwordIcon);
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
</body>
</html>