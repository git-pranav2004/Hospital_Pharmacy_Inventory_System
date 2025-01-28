<?php
session_start();
require 'db.php'; // Include your database connection
require 'vendor/autoload.php'; // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = ''; // Initialize error variable
$success = '';

// Decryption function
function str_openssl_dec($encryptedStr, $iv) {
    $key = "!1@2#3$4%5^6&7*8"; // Replace with your consistent encryption key
    $cipher = "aes-256-cbc";
    $options = 0;
    return openssl_decrypt($encryptedStr, $cipher, $key, $options, $iv);
}

// Generate CSRF token if not already set in session
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check CSRF token
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
        // Get the form data
        $inputUsername = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
        $inputPassword = $_POST['password'];

        // Generate an IV (Initialization Vector)
        $iv = '1234567890123456'; // Must match the one used during encryption

        // Fetch user from the database
        $stmt = $pdo->prepare("SELECT * FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll();

        foreach ($users as $user) {
            // Decrypt the stored username
            $decryptedUsername = str_openssl_dec($user['username'], $iv);

            // Check if the decrypted username matches the input username
            if ($decryptedUsername === $inputUsername) {
                // Verify password
                if (password_verify($inputPassword, $user['password_hash'])) {
                    // Set session variables
                    $_SESSION['is_logged_in'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $decryptedUsername;
                    $_SESSION['role'] = $user['role'];

                    // Send login notification email
                    try {
                        $mail = new PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com'; // Your SMTP server
                        $mail->SMTPAuth = true;
                        $mail->Username = '22bt04118@gsfcuniversity.ac.in'; // Your email address
                        $mail->Password = 'Pranav$2004'; // Your email password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        // Set email sender and recipient
                        $mail->setFrom('22bt04118@gsfcuniversity.ac.in', 'Hospital Pharmacy Inventory');
                        $mail->addAddress($user['email']); // Send to the user's registered email

                        $mail->Subject = 'Login Notification';
                        $mail->Body = "Hi $decryptedUsername,\n\nYou have successfully logged into the Hospital Pharmacy Inventory System.\n\nRegards,\nTeam";

                        $mail->send();
                    } catch (Exception $e) {
                        $error = "Login successful, but email could not be sent. Error: " . $mail->ErrorInfo;
                    }

                    // Redirect based on user role
                    if ($user['role'] === 'Pharmacist') {
                        header('Location: pharmacist_dashboard.php');
                    } elseif ($user['role'] === 'Doctor') {
                        header('Location: doctor_dashboard.php');
                    } elseif ($user['role'] === 'Receptionist') {
                        header('Location: receptionist_dashboard.php');
                    } else {
                        $error = "Invalid role assigned to this user.";
                    }
                    exit;
                } else {
                    $error = "Invalid username or password!";
                }
            }
        }
        $error = "Invalid username or password!";
    } else {
        $error = "Invalid CSRF token!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"> <!-- CSRF Token -->
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