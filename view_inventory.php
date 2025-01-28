<?php
session_start();
require 'db.php'; // Include database connection

// Check if the user is logged in and is a doctor
if (!isset($_SESSION['is_logged_in']) || $_SESSION['role'] !== 'Doctor') {
    header('Location: login.php');
    exit;
}

// Fetch inventory items with quantities
$stmt = $pdo->query("
    SELECT inventory.*, medicines.medicine_name, pharma_companies.company_name, 
           (SELECT COALESCE(SUM(transactions.quantity_issued), 0) 
            FROM transactions 
            WHERE transactions.medicine_id = inventory.medicine_id) AS quantity_issued,
           (inventory.quantity_received - 
           (SELECT COALESCE(SUM(transactions.quantity_issued), 0) 
            FROM transactions 
            WHERE transactions.medicine_id = inventory.medicine_id)) AS closing_stock
    FROM inventory
    LEFT JOIN medicines ON inventory.medicine_id = medicines.id
    LEFT JOIN pharma_companies ON medicines.manufacturer_id = pharma_companies.id
");
$inventory = $stmt->fetchAll();

// Fetch medicines for forms
$medicines_stmt = $pdo->query("SELECT medicines.id AS medicine_id, medicines.medicine_name, pharma_companies.company_name 
                               FROM medicines
                               LEFT JOIN pharma_companies ON medicines.manufacturer_id = pharma_companies.id");
$medicines = $medicines_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .sidebar h2 {
            color:rgb(13, 144, 232);
            margin-bottom: 20px;
        }
        h3 {
            color: rgb(13, 144, 232);
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            margin-bottom: 10px;
            border-radius: 4px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #000000;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
    <script>
        function toggleForm(formId) {
            document.getElementById('addInventoryForm').style.display = 'none';
            document.getElementById('issueMedicineForm').style.display = 'none';
            document.getElementById(formId).style.display = 'block';
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <h2>Welcome, Dr.</h2>
        <a href="doctor_dashboard.php?view=appointments">Appointments</a>
        <a href="doctor_dashboard.php?view=patients">Patients</a>
        <a href="doctor_dashboard.php?view=prescriptions">Prescriptions</a>
        <a href="doctor_dashboard.php?view=inventory">View Inventory</a>
        <a href="doctor_reports.php">View Reports</a>
        <a href="plogout.php" class="text-danger">Logout</a>
    </div>

    <div class="main-content">
        <div class="container mt-5">
            <!-- Inventory Table -->
            <h3>Inventory List</h3>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Medicine</th>
                        <th>Company</th>
                        <th>Quantity Received</th>
                        <th>Quantity Issued</th>
                        <th>Closing Stock</th>
                        <th>Expiry Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventory as $item): ?>
                        <tr>
                            <td><?php echo $item['id']; ?></td>
                            <td><?php echo $item['medicine_name']; ?></td>
                            <td><?php echo $item['company_name']; ?></td>
                            <td><?php echo $item['quantity_received']; ?></td>
                            <td><?php echo $item['quantity_issued']; ?></td>
                            <td><?php echo $item['closing_stock']; ?></td>
                            <td><?php echo $item['expiry_date']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>