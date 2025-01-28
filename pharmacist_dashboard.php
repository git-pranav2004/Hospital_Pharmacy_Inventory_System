<?php
session_start();

if (!isset($_SESSION['is_logged_in']) || $_SESSION['role'] !== 'Pharmacist') {
    header('Location: login.php');
    exit;
}

require 'db.php'; // Include your database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard</title>
    <!-- Include Bootstrap CSS -->
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
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="pharmacist_dashboard.php"><h3>Pharmacist Panel</h3></a>
        <a href="pmanage_inventory.php">Manage Inventory</a>
        <a href="pview_transactions.php">View Transactions</a>
        <a href="pgenerate_reports.php">Generate Reports</a>
        <a href="plogout.php" class="text-danger">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h1>Welcome, Pharmacist</h1>
            <div class="row">
                <!-- Card 2 -->
                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Medicines in Stock</h5>
                            <p class="card-text">
                                <?php
                                $stmt = $pdo->query("SELECT COUNT(*) AS total FROM inventory");
                                echo $stmt->fetch()['total'];
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="col-md-3">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Transactions Today</h5>
                            <p class="card-text">
                                <?php
                                $stmt = $pdo->query("SELECT COUNT(*) AS total FROM transactions WHERE DATE(created_at) = CURDATE()");
                                echo $stmt->fetch()['total'];
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Card 4 -->
                <div class="col-md-3">
                    <div class="card text-white bg-danger mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Low Stock Items</h5>
                            <p class="card-text">
                                <?php
                                $stmt = $pdo->query("SELECT COUNT(*) AS total FROM inventory WHERE quantity_issued < 10");
                                echo $stmt->fetch()['total'];
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>