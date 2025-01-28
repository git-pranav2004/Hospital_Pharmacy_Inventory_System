<?php
session_start();

// Protect the admin dashboard
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php"); // Redirect to login if no session
    exit;
}

require 'db.php'; // Include your database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
    <div class="sidebar">
        <a href="admin_dashboard.php" class="text-center py-3"><h3>Admin Dashboard</h3></a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_inventory.php">Manage Inventory</a>
        <a href="view_transactions.php">View Transactions</a>
        <a href="generate_reports.php">Generate Reports</a>
        <a href="logout.php" class="text-danger">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h1>Welcome, Admin</h1>
            <div class="row">
                <!-- Card 1 -->
                <div class="col-md-3">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Users</h5>
                            <p class="card-text">
                                <?php
                                $stmt = $pdo->query("SELECT COUNT(*) AS total FROM users");
                                echo $stmt->fetch()['total'];
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
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

            <!-- Example Table -->
            <div class="mt-4">
                <h2>Recent Activities</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Activity</th>
                            <th>User</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM logs ORDER BY created_at DESC LIMIT 5");
                        while ($log = $stmt->fetch()) {
                            echo "<tr>
                                    <td>{$log['id']}</td>
                                    <td>{$log['action']}</td>
                                    <td>{$log['user_id']}</td>
                                    <td>{$log['created_at']}</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>