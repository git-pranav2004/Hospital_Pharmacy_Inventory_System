<?php
session_start();
require 'db.php'; // Database connection

// Protect the page
if (!isset($_SESSION['is_logged_in']) || $_SESSION['role'] !== 'Pharmacist') {
    header('Location: login.php');
    exit;
}

// Filter by date (if requested)
$filterDate = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['filter_date'])) {
    $filterDate = $_POST['filter_date'];
    $stmt = $pdo->prepare("
        SELECT transactions.*, 
               medicines.medicine_name, 
               patients.patient_name, 
               pharma_companies.company_name 
        FROM transactions
        LEFT JOIN medicines ON transactions.medicine_id = medicines.id
        LEFT JOIN patients ON transactions.patient_id = patients.patient_id
        LEFT JOIN pharma_companies ON medicines.manufacturer_id = pharma_companies.id
        WHERE DATE(transactions.created_at) = :filter_date
        ORDER BY transactions.created_at DESC
    ");
    $stmt->execute([':filter_date' => $filterDate]);
} else {
    // Default query to fetch all transactions
    $stmt = $pdo->query("
        SELECT transactions.*, 
               medicines.medicine_name, 
               patients.patient_name, 
               pharma_companies.company_name 
        FROM transactions
        LEFT JOIN medicines ON transactions.medicine_id = medicines.id
        LEFT JOIN patients ON transactions.patient_id = patients.patient_id
        LEFT JOIN pharma_companies ON medicines.manufacturer_id = pharma_companies.id
        ORDER BY transactions.created_at DESC
    ");
}

$transactions = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Transactions</title>
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
        <a href="pharmacist_dashboard.php"><h3>Pharmacist Panel</h3></a>
        <a href="pmanage_inventory.php">Manage Inventory</a>
        <a href="pview_transactions.php">View Transactions</a>
        <a href="pgenerate_reports.php">Generate Reports</a>
        <a href="plogout.php" class="text-danger">Logout</a>
    </div>

    <div class="main-content">
    <div class="container mt-5">
        <h2>View Transactions</h2>

        <!-- Date Filter Form -->
        <h4>Filter by Date</h4>
        <form method="POST">
            <div class="row">
                <div class="col-md-3">
                    <input type="date" name="filter_date" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>

        <!-- Transactions Table -->
        <h4 class="mt-5">Transactions on <?php echo $filterDate ? $filterDate : 'All Dates'; ?></h4>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Medicine</th>
                    <th>Pharma Company</th>
                    <th>Patient</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($transactions)) {
                    echo "<tr><td colspan='7'>No transactions found for this date.</td></tr>";
                } else {
                    foreach ($transactions as $transaction) {
                        $totalPrice = $transaction['price_per_unit'] * $transaction['quantity_issued']; // Assuming price_per_unit is in the transaction table
                        echo "
                        <tr>
                            <td>{$transaction['id']}</td>
                            <td>" . htmlspecialchars($transaction['medicine_name'], ENT_QUOTES, 'UTF-8') . "</td>
                            <td>" . htmlspecialchars($transaction['company_name'], ENT_QUOTES, 'UTF-8') . "</td>
                            <td>" . htmlspecialchars($transaction['patient_name'], ENT_QUOTES, 'UTF-8') . "</td>
                            <td>{$transaction['quantity_issued']}</td>
                            <td>{$totalPrice}</td>
                            <td>{$transaction['created_at']}</td>
                        </tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>