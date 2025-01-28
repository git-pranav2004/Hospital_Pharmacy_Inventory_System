<?php
session_start();
require 'db.php'; // Include database connection

// Protect the page
if (!isset($_SESSION['is_logged_in']) || $_SESSION['role'] !== 'Receptionist') {
    header('Location: login.php');
    exit;
}

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a CSRF token
}

// Fetch all patients with error handling
try {
    $stmt = $pdo->query("SELECT * FROM patients ORDER BY patient_id");
    $patients = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Error fetching patients: " . $e->getMessage());
}

// Add patient with CSRF validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_patient'])) {
    // CSRF validation
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    $patient_name = $_POST['patient_name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $contact_info = $_POST['contact_info'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];

    // Insert new patient
    try {
        $insert_query = "INSERT INTO patients (patient_name, age, gender, contact_info, email, phone_number) 
                         VALUES (:patient_name, :age, :gender, :contact_info, :email, :phone_number)";
        $stmt = $pdo->prepare($insert_query);
        $stmt->execute([
            ':patient_name' => $patient_name,
            ':age' => $age,
            ':gender' => $gender,
            ':contact_info' => $contact_info,
            ':email' => $email,
            ':phone_number' => $phone_number
        ]);

        header('Location: view_patients.php');
        exit;
    } catch (Exception $e) {
        echo 'Error adding patient: ' . $e->getMessage();
    }
}

// Edit patient with CSRF validation
if (isset($_GET['edit'])) {
    $patient_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = :patient_id");
    $stmt->execute([':patient_id' => $patient_id]);
    $patient = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_patient'])) {
        // CSRF validation
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Invalid CSRF token');
        }

        $patient_name = $_POST['patient_name'];
        $age = $_POST['age'];
        $gender = $_POST['gender'];
        $contact_info = $_POST['contact_info'];
        $email = $_POST['email'];
        $phone_number = $_POST['phone_number'];

        // Update patient
        try {
            $stmt = $pdo->prepare("
                UPDATE patients 
                SET patient_name = :patient_name, age = :age, gender = :gender, 
                    contact_info = :contact_info, email = :email, phone_number = :phone_number 
                WHERE patient_id = :patient_id
            ");
            $stmt->execute([
                ':patient_name' => $patient_name,
                ':age' => $age,
                ':gender' => $gender,
                ':contact_info' => $contact_info,
                ':email' => $email,
                ':phone_number' => $phone_number,
                ':patient_id' => $patient_id
            ]);

            header('Location: view_patients.php');
            exit;
        } catch (Exception $e) {
            echo 'Error updating patient: ' . $e->getMessage();
        }
    }
}

// Delete patient
if (isset($_GET['delete'])) {
    $patient_id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM patients WHERE patient_id = :patient_id");
        $stmt->execute([':patient_id' => $patient_id]);
        header('Location: view_patients.php');
        exit;
    } catch (Exception $e) {
        echo 'Error deleting patient: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        h2, h3, h4 {
            color: rgb(13, 144, 232);
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            background: #343a40;
            color: white;
            position: fixed;
            padding: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px;
            display: block;
            margin: 10px 0;
        }
        .sidebar a:hover {
            background: #495057;
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="sidebar">
                <h3>Receptionist Panel</h3>
                <a href="receptionist_dashboard.php">Dashboard</a>
                <a href="manage_appointments.php">Manage Appointments</a>
                <a href="view_patients.php">View Patients</a>
                <a href="plogout.php" class="text-danger">Logout</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="container mt-5">
                    <h2>View Patients</h2>

                <!-- Add Patient Form -->
                <h4 class="mt-4">Add Patient</h4>
                <form method="POST">
                    <!-- CSRF Token Field -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="mb-3">
                        <label for="patient_name" class="form-label">Patient Name</label>
                        <input type="text" name="patient_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="age" class="form-label">Age</label>
                        <input type="number" name="age" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" required>
                    </div>
                    <button type="submit" name="add_patient" class="btn btn-primary">Add Patient</button>
                </form>

                <!-- Edit Patient Form -->
                <?php if (isset($patient)): ?>
                    <h4 class="mt-4">Edit Patient</h4>
                    <form method="POST">
                        <!-- CSRF Token Field -->
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <div class="mb-3">
                            <label for="patient_name" class="form-label">Patient Name</label>
                            <input type="text" name="patient_name" class="form-control" value="<?php echo $patient['patient_name']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="age" class="form-label">Age</label>
                            <input type="number" name="age" class="form-control" value="<?php echo $patient['age']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select name="gender" class="form-control" required>
                                <option value="Male" <?php echo $patient['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo $patient['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo $patient['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $patient['email']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" name="phone_number" class="form-control" value="<?php echo $patient['phone_number']; ?>" required>
                        </div>
                        <button type="submit" name="update_patient" class="btn btn-warning">Update Patient</button>
                    </form>
                <?php endif; ?>

                <!-- Patients Table -->
                <h4 class="mt-4">Patients List</h4>
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td><?php echo $patient['patient_id']; ?></td>
                                <td><?php echo $patient['patient_name']; ?></td>
                                <td><?php echo $patient['age']; ?></td>
                                <td><?php echo $patient['gender']; ?></td>
                                <td><?php echo $patient['email']; ?></td>
                                <td><?php echo $patient['phone_number']; ?></td>
                                <td>
                                    <a href="?edit=<?php echo $patient['patient_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="?delete=<?php echo $patient['patient_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this patient?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
