<?php
session_start();
require 'db.php'; // Include database connection

// Protect the page
if (!isset($_SESSION['is_logged_in']) || $_SESSION['role'] !== 'Pharmacist') {
    header('Location: login.php');
    exit;
}

// Fetch patient list for the dropdown
$patients_stmt = $pdo->query("SELECT patient_id, patient_name FROM patients"); // Adjust the query as per your patients table
$patients = $patients_stmt->fetchAll();

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

// Delete inventory item
if (isset($_GET['delete'])) {
    $item_id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = :id");
    $stmt->execute([':id' => $item_id]);
    // Log the activity
    $action = "Added $quantity_received units of Medicine ID: $medicine_id with expiry date $expiry_date.";
    $pdo->prepare("INSERT INTO logs (action) VALUES (:action)")
        ->execute([':action' => $action]);
    header('Location: pmanage_inventory.php');
    exit;
}

// Add inventory item
if (isset($_POST['add_inventory'])) {
    $medicine_id = intval($_POST['medicine_id']);
    $quantity_received = intval($_POST['quantity_received']);
    $expiry_date = $_POST['expiry_date'];

    $stmt = $pdo->prepare("INSERT INTO inventory (medicine_id, quantity_received, expiry_date) 
                           VALUES (:medicine_id, :quantity_received, :expiry_date)");
    $stmt->execute([
        ':medicine_id' => $medicine_id,
        ':quantity_received' => $quantity_received,
        ':expiry_date' => $expiry_date
    ]);    

    // Log the activity
    $action = "Added $quantity_received units of Medicine ID: $medicine_id with expiry date $expiry_date.";
    $pdo->prepare("INSERT INTO logs (action) VALUES (:action)")
        ->execute([':action' => $action]);

    header('Location: pmanage_inventory.php');
    exit;
}

// Issue medicine
if (isset($_POST['issue_medicine'])) {
    $medicine_id = intval($_POST['medicine_id']);
    $patient_id = intval($_POST['patient_id']);
    $quantity_issued = intval($_POST['quantity_issued']);
    $price_per_unit = floatval($_POST['price_per_unit']);
    
    $stmt = $pdo->prepare("SELECT quantity_received FROM inventory WHERE medicine_id = :medicine_id");
    $stmt->execute([':medicine_id' => $medicine_id]);
    $inventory_item = $stmt->fetch();

    if ($inventory_item['quantity_received'] >= $quantity_issued) {
        $stmt = $pdo->prepare("INSERT INTO transactions (medicine_id, patient_id, quantity_issued, price_per_unit, created_at) 
                               VALUES (:medicine_id, :patient_id, :quantity_issued, :price_per_unit, NOW())");
        $stmt->execute([
            ':medicine_id' => $medicine_id,
            ':patient_id' => $patient_id,
            ':quantity_issued' => $quantity_issued,
            ':price_per_unit' => $price_per_unit
        ]);

        $new_stock = $inventory_item['quantity_received'] - $quantity_issued;
        $stmt = $pdo->prepare("UPDATE inventory SET quantity_received = :new_stock WHERE medicine_id = :medicine_id");
        $stmt->execute([
            ':new_stock' => $new_stock,
            ':medicine_id' => $medicine_id
        ]);
        // Log the activity
        $action = "Added $quantity_received units of Medicine ID: $medicine_id with expiry date $expiry_date.";
        $pdo->prepare("INSERT INTO logs (action) VALUES (:action)")
            ->execute([':action' => $action]);

        header('Location: pmanage_inventory.php');
        exit;
    } else {
        $error_message = "Not enough stock!";
    }
}
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
        <a href="pharmacist_dashboard.php"><h3>Pharmacist Panel</h3></a>
        <a href="pmanage_inventory.php">Manage Inventory</a>
        <a href="pview_transactions.php">View Transactions</a>
        <a href="pgenerate_reports.php">Generate Reports</a>
        <a href="plogout.php" class="text-danger">Logout</a>
    </div>

    <div class="main-content">
        <div class="container mt-5">
            <h2>Manage Inventory</h2>
            <div class="d-flex mb-3">
                <button class="btn btn-primary me-2" onclick="toggleForm('addInventoryForm')">Add Inventory</button>
                <button class="btn btn-success" onclick="toggleForm('issueMedicineForm')">Issue Medicine</button>
            </div>

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
                        <th>Actions</th>
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
                            <td>
                                <a href="pmanage_inventory.php?delete=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Add Inventory Form -->
            <div id="addInventoryForm" style="display: none;">
                <h3>Add Inventory Item</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label for="medicine_id" class="form-label">Medicine</label>
                        <select name="medicine_id" class="form-control" required>
                            <option value="">Select a Medicine</option>
                            <?php foreach ($medicines as $medicine): ?>
                                <option value="<?php echo $medicine['medicine_id']; ?>">
                                    <?php echo $medicine['medicine_name']; ?> (<?php echo $medicine['company_name']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity_received" class="form-label">Quantity</label>
                        <input type="number" name="quantity_received" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control" required>
                    </div>
                    <button type="submit" name="add_inventory" class="btn btn-primary">Add Item</button>
                </form>
            </div>

            <!-- Issue Medicine Form -->
            <div id="issueMedicineForm" style="display: none;">
                <h3>Issue Medicine</h3>
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="medicine_id" class="form-label">Medicine</label>
                        <select name="medicine_id" class="form-control" required>
                            <option value="">Select a Medicine</option>
                            <?php foreach ($medicines as $medicine): ?>
                                <option value="<?php echo $medicine['medicine_id']; ?>">
                                    <?php echo $medicine['medicine_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="patient_id" class="form-label">Patient</label>
                        <select name="patient_id" class="form-control" required>
                            <option value="">Select a Patient</option>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?php echo $patient['patient_id']; ?>">
                                    <?php echo $patient['patient_name']; ?> (ID: <?php echo $patient['patient_id']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity_issued" class="form-label">Quantity</label>
                        <input type="number" name="quantity_issued" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="price_per_unit" class="form-label">Price per Unit</label>
                        <input type="number" step="0.01" name="price_per_unit" class="form-control" required>
                    </div>
                    <button type="submit" name="issue_medicine" class="btn btn-success">Issue Medicine</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>