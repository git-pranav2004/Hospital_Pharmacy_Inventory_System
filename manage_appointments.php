<?php
session_start();
require 'db.php'; // Include database connection

// Protect the page
if (!isset($_SESSION['is_logged_in']) || $_SESSION['role'] !== 'Receptionist') {
    header('Location: login.php');
    exit;
}

// Decryption function
function decryptData($encryptedData, $key)
{
    $cipher = "AES-256-CBC"; // Ensure this matches your encryption method
    $iv = substr($encryptedData, 0, 16); // Extract the IV
    $encryptedData = substr($encryptedData, 16); // Extract the encrypted data
    return openssl_decrypt($encryptedData, $cipher, $key, 0, $iv);
}
$key="!1@2#3$4%5^6&7*8";

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a CSRF token
}

// Fetch all appointments with doctor names and doctor IDs
$stmt = $pdo->query("SELECT appointments.*, patients.patient_name, users.username AS doctor_username FROM appointments LEFT JOIN patients ON appointments.patient_id = patients.patient_id LEFT JOIN users ON appointments.doctor_username = users.username ORDER BY date ASC");
$appointments = $stmt->fetchAll();

// Fetch patients and doctors for the dropdowns
$patients_stmt = $pdo->query("SELECT * FROM patients");
$patients = $patients_stmt->fetchAll();

$doctors_stmt = $pdo->query("SELECT * FROM users WHERE role = 'Doctor'");
$doctors = $doctors_stmt->fetchAll();

// Add appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_appointment'])) {
    // CSRF validation
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $date = $_POST['date'];
    $status = 'Scheduled'; // Default status

    $encryptionKey = "!1@2#3$4%5^6&7*8"; // Replace this with your actual key
    $doctors_stmt = $pdo->query("SELECT * FROM users WHERE role = 'Doctor'");
    $doctors = $doctors_stmt->fetchAll();

    foreach ($doctors as &$doctor) {
        $doctor['username'] = decryptData($doctor['username'], $encryptionKey);
    }
    unset($doctor); // Avoid unintended references

    // Insert new appointment
    $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_username, date, status) VALUES (:patient_id, :doctor_username, :date, :status)");
    $stmt->execute([
        ':patient_id' => $patient_id,
        ':doctor_username' => $doctor_username,
        ':date' => $date,
        ':status' => $status
    ]);

    header('Location: manage_appointments.php');
    exit;
}

// Update appointment
if (isset($_GET['edit'])) {
    $appointment_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE appointment_id = :appointment_id");
    $stmt->execute([':appointment_id' => $appointment_id]);
    $appointment = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_appointment'])) {
        // CSRF validation
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Invalid CSRF token');
        }

        $patient_id = $_POST['patient_id'];
        $doctor_id = $_POST['doctor_id'];
        $date = $_POST['date'];
        $status = $_POST['status'];

        // Fetch the doctor's username using doctor_id
        $encryptionKey = "!1@2#3$4%5^6&7*8"; // Replace this with your actual key
        $doctors_stmt = $pdo->query("SELECT * FROM users WHERE role = 'Doctor'");
        $doctors = $doctors_stmt->fetchAll();
    
        foreach ($doctors as &$doctor) {
            $doctor['username'] = decryptData($doctor['username'], $encryptionKey);
        }
        unset($doctor); // Avoid unintended references
    
        // Update the appointment
        $stmt = $pdo->prepare("UPDATE appointments SET patient_id = :patient_id, doctor_username = :doctor_username, date = :date, status = :status WHERE appointment_id = :appointment_id");
        $stmt->execute([
            ':patient_id' => $patient_id,
            ':doctor_username' => $doctor_username,
            ':date' => $date,
            ':status' => $status,
            ':appointment_id' => $appointment_id
        ]);

        header('Location: manage_appointments.php');
        exit;
    }
}

// Delete appointment
if (isset($_GET['delete'])) {
    $appointment_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM appointments WHERE appointment_id = :appointment_id");
    $stmt->execute([':appointment_id' => $appointment_id]);
    header('Location: manage_appointments.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
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
            <div class="main-content">
                <div class="col-md-4">
                    <h2>Manage Appointments</h2>

                    <!-- Add Appointment Form -->
                    <h4 class="mt-4">Add Appointment</h4>
                    <form method="POST">
                        <!-- CSRF Token Field -->
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <div class="mb-4">
                            <label for="patient_id" class="form-label">Select Patient</label>
                            <select name="patient_id" class="form-control" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?php echo $patient['patient_id']; ?>"><?php echo $patient['patient_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="doctor_id" class="form-label">Assign Doctor</label>
                            <select name="doctor_id" class="form-control" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?php echo $doctor['id']; ?>">Dr.  (ID: <?php echo $doctor['id']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="date" class="form-label">Appointment Date & Time</label>
                            <input type="datetime-local" name="date" class="form-control" required>
                        </div>
                        <button type="submit" name="add_appointment" class="btn btn-primary">Add Appointment</button>
                    </form>

                    <!-- Edit Appointment Form -->
                    <?php if (isset($appointment)): ?>
                        <h4 class="mt-4">Edit Appointment</h4>
                        <form method="POST">
                            <!-- CSRF Token Field -->
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <div class="mb-4">
                                <label for="patient_id" class="form-label">Select Patient</label>
                                <select name="patient_id" class="form-control" required>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?php echo $patient['patient_id']; ?>" <?php echo $appointment['patient_id'] == $patient['patient_id'] ? 'selected' : ''; ?>>
                                            <?php echo $patient['patient_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-4">
                            <label for="doctor_id" class="form-label">Assign Doctor</label>
                            <select name="doctor_id" class="form-control" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?php echo $doctor['id']; ?>">Dr. <?php echo $doctor['username']; ?> (ID: <?php echo $doctor['id']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            </div>

                            <div class="mb-4">
                                <label for="date" class="form-label">Appointment Date & Time</label>
                                <input type="datetime-local" name="date" class="form-control" value="<?php echo $appointment['date']; ?>" required>
                            </div>
                            <div class="mb-4">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="Scheduled" <?php echo $appointment['status'] == 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                    <option value="Completed" <?php echo $appointment['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="Cancelled" <?php echo $appointment['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <button type="submit" name="update_appointment" class="btn btn-warning">Update Appointment</button>
                        </form>
                    <?php endif; ?>

                    <!-- Appointments Table -->
                    <h4 class="mt-4">Appointments List</h4>
                    <table class="table table-bordered mt-4">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Patient Name</th>
                                <th>Assigned Doctor</th>
                                <th>Patient ID</th> <!-- Display Doctor ID -->
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr>
                                    <td><?php echo $appointment['appointment_id']; ?></td>
                                    <td><?php echo $appointment['patient_name']; ?></td>
                                    <td>Dr. <?php echo $appointment['doctor_username']; ?></td>
                                    <td><?php echo $appointment['patient_id']; ?></td> <!-- Show Doctor ID -->
                                    <td><?php echo $appointment['date']; ?></td>
                                    <td><?php echo $appointment['status']; ?></td>
                                    <td>
                                        <a href="manage_appointments.php?edit=<?php echo $appointment['appointment_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="manage_appointments.php?delete=<?php echo $appointment['appointment_id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>