<?php
session_start();
require 'db.php'; // Include your database connection

// Protect the page
if (!isset($_SESSION['is_logged_in']) || $_SESSION['role'] !== 'Receptionist') {
    header("Location: login.php");
    exit;
}

// Fetch summary counts
$total_appointments = $pdo->query("SELECT COUNT(*) AS total FROM appointments")->fetch()['total'];
$total_patients = $pdo->query("SELECT COUNT(*) AS total FROM patients")->fetch()['total'];

// Fetch recent activities (logs) with meaningful data
$logs_query = "
    SELECT logs.id, logs.action, users.username, logs.created_at 
    FROM logs 
    JOIN users ON logs.user_id = users.id 
    ORDER BY logs.created_at DESC 
    LIMIT 5
";
$logs_stmt = $pdo->query($logs_query);
$logs = $logs_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receptionist Dashboard</title>
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
        <h1>Welcome, Receptionist</h1>
        <div class="row">
            <!-- Appointments Card -->
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Appointments</h5>
                        <p class="card-text"><?php echo $total_appointments; ?></p>
                    </div>
                </div>
            </div>

            <!-- Patients Card -->
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Patients</h5>
                        <p class="card-text"><?php echo $total_patients; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>