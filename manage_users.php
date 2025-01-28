<?php
session_start();
require 'db.php'; // Include database connection

// Protect the page
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php"); // Redirect to login if no session
    exit;

}
define('FIXED_IV', '1234567890123456'); 
$iv = '1234567890123456';

// Function for encryption
function str_openssl_enc($str) {
    $key = '!1@2#3$4%5^6&7*8';
    $cipher = "aes-256-cbc";
    $options = 0;
    return openssl_encrypt($str, $cipher, $key, $options, FIXED_IV);
}

// Function for decryption
function str_openssl_dec($str) {
    $key = '!1@2#3$4%5^6&7*8';
    $cipher = "aes-256-cbc";
    $options = 0;
    return openssl_decrypt($str, $cipher, $key, $options, FIXED_IV);
}

// Fetch users
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();

// Add user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    // Generate IV for encryption
    $iv = '1234567890123456';

    // Encrypt sensitive data
    $encryptedUsername = str_openssl_enc($username, $iv);
    $encryptedEmail = str_openssl_enc($email, $iv);
    $encryptedPhone = str_openssl_enc($phone, $iv);

    // Check for duplicate email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $encryptedEmail]);
    if ($stmt->rowCount() > 0) {
        die("Error: Email already exists. Please use a different email.");
    }

    // Insert user data with encrypted details
    $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, password_hash, role) VALUES (:username, :email, :phone, :password_hash, :role)");
    $stmt->execute([
        ':username' => $encryptedUsername,
        ':email' => $encryptedEmail,
        ':phone' => $encryptedPhone,
        ':password_hash' => $password,
        ':role' => $role,
        //':iv' => '1234567890123456'
    ]);

    header('Location: manage_users.php');
    exit;
}

// Handle edit action
$editing_user = null;
if (isset($_GET['edit'])) {
    $user_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $editing_user = $stmt->fetch();

    // Update user details
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
        $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');
        $role = $_POST['role'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : $editing_user['password_hash'];

        //$iv = '1234567890123456';

        // Encrypt the updated details
        $encryptedUsername = str_openssl_enc($username, $iv);
        $encryptedPhone = str_openssl_enc($phone, $iv);

        // Update user data with encrypted details
        $stmt = $pdo->prepare("UPDATE users SET username = :username, password_hash = :password, role = :role, phone = :phone WHERE id = :id");
        $stmt->execute([
            ':username' => $encryptedUsername,
            ':password' => $password,
            ':role' => $role,
            ':phone' => $encryptedPhone,
            ':id' => $user_id,
        ]);


        header('Location: manage_users.php');
        exit;
    }
}

// Delete user
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);

    header('Location: manage_users.php');
    exit;
}
?>

<!-- HTML for user management -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        h3 {
            color: rgb(13, 144, 232);
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            background: #343a40;
            color: white;
            position: fixed;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 15px;
            display: block;
        }
        .sidebar a:hover {
            background: #495057;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h3 class="text-center py-3">Admin Panel</h3>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_inventory.php">Manage Inventory</a>
        <a href="view_transactions.php">View Transactions</a>
        <a href="generate_reports.php">Generate Reports</a>
        <a href="logout.php" class="text-danger">Logout</a>
    </div>

    <div class="main-content">
        <div class="container mt-5">
            <h2>Manage Users</h2>

            <!-- Add or Edit User Form -->
            <?php if ($editing_user): ?>
                <h4>Edit User</h4>
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars(str_openssl_dec($editing_user['username']), ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars(str_openssl_dec($editing_user['phone']), ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password (Leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select name="role" class="form-control" required>
                            <option value="Pharmacist" <?php echo $editing_user['role'] === 'Pharmacist' ? 'selected' : ''; ?>>Pharmacist</option>
                            <option value="Doctor" <?php echo $editing_user['role'] === 'Doctor' ? 'selected' : ''; ?>>Doctor</option>
                            <option value="Receptionist" <?php echo $editing_user['role'] === 'Receptionist' ? 'selected' : ''; ?>>Receptionist</option>
                        </select>
                    </div>
                    <button type="submit" name="update_user" class="btn btn-warning">Update User</button>
                    <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
                </form>
            <?php else: ?>
                <h4>Add User</h4>
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select name="role" class="form-control" required>
                            <option value="Pharmacist">Pharmacist</option>
                            <option value="Doctor">Doctor</option>
                            <option value="Receptionist">Receptionist</option>
                        </select>
                    </div>
                    <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                </form>
            <?php endif; ?>

            <!-- Users Table -->
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(str_openssl_dec($user['username']), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(str_openssl_dec($user['email']), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(str_openssl_dec($user['phone']), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td> 
                                <a href="manage_users.php?edit=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="manage_users.php?delete=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>