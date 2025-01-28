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
    $role = $_POST['role']; // Get selected role (Pharmacist or Doctor)

    // Generate an IV (Initialization Vector) for AES
    $iv = '1234567890123456'; // Must be 16 bytes for AES-256-CBC

    // Encrypt the username
    $encryptedUsername = str_openssl_enc($username, $iv);
    $encryptedEmail = str_openssl_enc($email, $iv);
    $encryptedPhone = str_openssl_enc($phone, $iv);

    // Check if the username or email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
    $stmt->execute([':username' => $encryptedUsername, ':email' => $encryptedEmail]);
    $existingUser  = $stmt->fetch();

    if ($existingUser ) {
        $error = "Username or Email already exists. Please choose a different one.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format. Please enter a valid email.";
    } elseif (!preg_match('/^\d{10}$/', $phone)) {
        $error = "Invalid phone number. Please enter a 10-digit phone number.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match. Please confirm your password.";
    } elseif ($role !== 'Pharmacist' && $role !== 'Doctor' && $role !== 'Receptionist') {
        $error = "Invalid role selected. Please choose either Pharmacist or Doctor or Receptionist.";
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert the new admin into the database
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, phone, password_hash, role) 
            VALUES (:username, :email, :phone, :password_hash, :role)
        ");
        $stmt->execute([
            ':username' => $encryptedUsername,
            ':email' => $encryptedEmail,
            ':phone' => $encryptedPhone,
            ':password_hash' => $hashedPassword,
            ':role' => $role
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
            $mail->Body = "Hi $username,\n\nYou have successfully registered as an $role in the Hospital Pharmacy Inventory System.\n\nBest Regards,\nTeam";

            $mail->send();
            $success = "$role registered successfully! A confirmation email has been sent.";
        } catch (Exception $e) {
            $success = "$role registered successfully, but the email could not be sent.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #007bff, #6c757d);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        form {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        input, button, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #007bff;
            color: white;
            font-size: 16px;
            border: none;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .toggle-password {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 10px;
        }
        .form-group {
            position: relative;
        }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Register</h2>
        
        <?php if (isset($error) && $error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if (isset($success) && $success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>

        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="text" name="phone" id="phone" class="form-control" placeholder="10-digit phone number" required>
        </div>
        
        <div class="form-group mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
            <span class="toggle-password" onclick="togglePasswordVisibility('password')">👁️</span>
        </div>
        
        <div class="form-group mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm password" required>
            <span class="toggle-password" onclick="togglePasswordVisibility('confirm_password')">👁️</span>
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="form-select" required>
                <option value="" disabled selected>Select your role</option>
                <option value="Pharmacist">Pharmacist</option>
                <option value="Doctor">Doctor</option>
                <option value="Receptionist">Receptionist</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Register</button>
    </form>

    <script>
        function togglePasswordVisibility(id) {
            const passwordField = document.getElementById(id);
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
        }
    </script>
</body>
</html>