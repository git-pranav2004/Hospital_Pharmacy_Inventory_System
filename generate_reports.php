<?php
session_start();
require 'db.php';

// Protect the page
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Get selected view (daily or patient)
$view_mode = isset($_GET['view_mode']) ? $_GET['view_mode'] : 'daily';

// Get selected date and patient
$selected_date = isset($_GET['report_date']) ? $_GET['report_date'] : date('Y-m-d');
$selected_patient = isset($_GET['patient_id']) ? $_GET['patient_id'] : '';

// Fetch patients for dropdown
$patients_stmt = $pdo->query("SELECT patient_id, patient_name FROM patients");
$patients = $patients_stmt->fetchAll();

if ($view_mode === 'daily') {
    // Fetch daily transactions
    $transactions_query = "
        SELECT t.id, t.medicine_id, m.medicine_name, t.quantity_issued, t.price_per_unit, 
               (t.price_per_unit * t.quantity_issued) AS total_price, t.transaction_date, p.patient_name
        FROM transactions t
        JOIN medicines m ON t.medicine_id = m.id
        JOIN patients p ON t.patient_id = p.patient_id
        WHERE DATE(t.transaction_date) = :selected_date
    ";

    $params = [':selected_date' => $selected_date];

    if (!empty($selected_patient)) {
        $transactions_query .= " AND t.patient_id = :selected_patient";
        $params[':selected_patient'] = $selected_patient;
    }

    $transactions_stmt = $pdo->prepare($transactions_query);
    $transactions_stmt->execute($params);
    $transactions = $transactions_stmt->fetchAll();

    // Calculate total sales
    $total_sales_query = "
        SELECT SUM(t.price_per_unit * t.quantity_issued) AS total_sales
        FROM transactions t
        WHERE DATE(t.transaction_date) = :selected_date
    ";

    if (!empty($selected_patient)) {
        $total_sales_query .= " AND t.patient_id = :selected_patient";
    }

    $total_sales_stmt = $pdo->prepare($total_sales_query);
    $total_sales_stmt->execute($params);
    $total_sales = $total_sales_stmt->fetchColumn();
} else {
    // Fetch all transactions for the selected patient
    $transactions_query = "
        SELECT t.id, m.medicine_name, t.quantity_issued, t.price_per_unit, 
               (t.price_per_unit * t.quantity_issued) AS total_price, t.transaction_date
        FROM transactions t
        JOIN medicines m ON t.medicine_id = m.id
        WHERE t.patient_id = :selected_patient
    ";
    $transactions_stmt = $pdo->prepare($transactions_query);
    $transactions_stmt->execute([':selected_patient' => $selected_patient]);
    $transactions = $transactions_stmt->fetchAll();

    // Calculate total sales for the selected patient
    $total_sales_query = "
        SELECT SUM(t.price_per_unit * t.quantity_issued) AS total_sales
        FROM transactions t
        WHERE t.patient_id = :selected_patient
    ";
    $total_sales_stmt = $pdo->prepare($total_sales_query);
    $total_sales_stmt->execute([':selected_patient' => $selected_patient]);
    $total_sales = $total_sales_stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Transactions Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
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
            top: 0;
            left: 0;
            overflow-y: auto;
            padding-top: 20px;
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
            margin-left: 260px;
            padding: 20px;
            height: 100vh;
            overflow-y: auto;
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

    <!-- Main Content -->
    <div class="main-content">
        <h2>
            <?php echo $view_mode === 'daily' ? 'Daily Transactions Report' : 'Patient Transaction History'; ?>
        </h2>

        <!-- Filters -->
        <form method="GET" class="mb-3">
            <input type="hidden" name="view_mode" value="<?php echo htmlspecialchars($view_mode); ?>">
            <div class="row">
                <?php if ($view_mode === 'daily'): ?>
                    <div class="col-md-4">
                        <input type="date" name="report_date" class="form-control" value="<?php echo htmlspecialchars($selected_date); ?>">
                    </div>
                <?php endif; ?>
                <div class="col-md-4">
                    <select name="patient_id" class="form-control">
                        <option value="">Select Patient</option>
                        <?php foreach ($patients as $patient): ?>
                            <option value="<?= htmlspecialchars($patient['patient_id']) ?>" <?= $selected_patient == $patient['patient_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($patient['patient_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="?view_mode=<?php echo $view_mode === 'daily' ? 'patient' : 'daily'; ?>" 
                       class="btn btn-secondary w-100">
                        Switch to <?php echo $view_mode === 'daily' ? 'Patient History' : 'Daily Transactions'; ?>
                    </a>
                </div>
            </div>
        </form>

        <!-- Transactions Table -->
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <?php if ($view_mode === 'daily'): ?>
                        <th>Patient Name</th>
                        <th>Medicine ID</th>
                    <?php endif; ?>
                    <th>Medicine Name</th>
                    <th>Quantity Issued</th>
                    <th>Price Per Unit</th>
                    <th>Total Price</th>
                    <th>Transaction Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($transactions) > 0): ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                            <?php if ($view_mode === 'daily'): ?>
                                <td><?php echo htmlspecialchars($transaction['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['medicine_id']); ?></td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars($transaction['medicine_name']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['quantity_issued']); ?></td>
                            <td>₹<?php echo number_format($transaction['price_per_unit'], 2); ?></td>
                            <td>₹<?php echo number_format($transaction['total_price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($transaction['transaction_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No transactions found.</td>
                    </tr>
                <?php endif; ?>
                <!-- Display Total Sales -->
                <?php if ($view_mode === 'daily'): ?>
                    <h4>Total Sales for Selected Date: ₹<?php echo number_format($total_sales, 2); ?></h4>
                <?php elseif ($view_mode === 'patient' && $selected_patient): ?>
                    <h4>Total Sales for Selected Patient: ₹<?php echo number_format($total_sales, 2); ?></h4>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($view_mode === 'daily'): ?>
            <h4>Total Sales: ₹<?php echo number_format($total_sales, 2); ?></h4>
        <?php endif; ?>
    </div>
</body>
</html>