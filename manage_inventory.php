<?php
session_start();
require 'db.php'; // Include database connection

// Protect the page
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php"); // Redirect to login if no session
    exit;
}

// Fetch inventory items with quantities
$stmt = $pdo->query("
    SELECT inventory.*, medicines.medicine_name, pharma_companies.company_name, medicines.dosage_form,
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

// Fetch medicines from the medicines table
$medicines_stmt = $pdo->query("
    SELECT m.id, m.medicine_name, m.generic_name, m.dosage_form, 
           m.strength, pc.company_name, m.expiry_date
    FROM medicines m
    LEFT JOIN pharma_companies pc ON m.manufacturer_id = pc.id
");
$medicines = $medicines_stmt->fetchAll();

// Fetch medicines for forms
$medicines_stmt = $pdo->query("SELECT * FROM medicines");
$medicines = $medicines_stmt->fetchAll();

// Fetch pharma companies for forms and display
$companies_stmt = $pdo->query("SELECT * FROM pharma_companies");
$companies = $companies_stmt->fetchAll();

// Add new pharma company
if (isset($_POST['add_company'])) {
    $company_id = trim($_POST['company_id']);
    $company_name = trim($_POST['company_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    $stmt = $pdo->prepare("INSERT INTO pharma_companies (id, company_name, email, phone, address) 
                           VALUES (:id, :company_name, :email, :phone, :address)");
    $stmt->execute([
        ':id' => $company_id,
        ':company_name' => $company_name,
        ':email' => $email,
        ':phone' => $phone,
        ':address' => $address
    ]);

    header('Location: manage_inventory.php');
    exit;
}

// Add new medicine
if (isset($_POST['add_medicine'])) {
    $medicine_name = trim($_POST['medicine_name']);
    $generic_name = trim($_POST['generic_name']);
    $dosage_form = trim($_POST['dosage_form']);
    $strength = trim($_POST['strength']);
    $manufacturer_id = intval($_POST['manufacturer_id']);
    $expiry_date = $_POST['expiry_date'];

    $stmt = $pdo->prepare("INSERT INTO medicines (medicine_name, generic_name, dosage_form, strength, manufacturer_id, expiry_date) 
                           VALUES (:medicine_name, :generic_name, :dosage_form, :strength, :manufacturer_id, :expiry_date)");
    $stmt->execute([
        ':medicine_name' => $medicine_name,
        ':generic_name' => $generic_name,
        ':dosage_form' => $dosage_form,
        ':strength' => $strength,
        ':manufacturer_id' => $manufacturer_id,
        ':expiry_date' => $expiry_date
    ]);

    header('Location: manage_inventory.php');
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

    header('Location: manage_inventory.php');
    exit;
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
        .form-container {
            display: none;
        }
    </style>
    <script>
        function toggleForm(formId) {
            // Hide all forms
            const forms = document.querySelectorAll('.form-container');
            forms.forEach(form => form.style.display = 'none');

            // Show the selected form
            const selectedForm = document.getElementById(formId);
            if (selectedForm) {
                selectedForm.style.display = 'block';
            }
        }
    </script>
</head>
<body>
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
            <h2>Manage Inventory</h2>
            <div class="d-flex mb-3">
                <button class="btn btn-primary me-2" onclick="toggleForm('addInventoryForm')">Add Inventory</button>
                <button class="btn btn-success me-2" onclick="toggleForm('addMedicineForm')">Add Medicine</button>
                <button class="btn btn-warning me-2" onclick="toggleForm('addCompanyForm')">Add Pharma Company</button>
            </div>

            <!-- Add Inventory Form -->
            <div id="addInventoryForm" class="form-container">
                <h3>Add Inventory Item</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label for="medicine_id" class="form-label">Medicine</label>
                        <select name="medicine_id" class="form-control" required>
                            <option value="">Select a Medicine</option>
                            <?php foreach ($medicines as $medicine): ?>
                                <option value="<?php echo $medicine['id']; ?>">
                                    <?php echo $medicine['medicine_name']; ?> (<?php echo $medicine['dosage_form']; ?>)
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
                <!-- Inventory Table -->
                <h3>Inventory List</h3>
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Medicine</th>
                            <th>Company</th>
                            <th>Dosage Form</th>
                            <th>Opening Stock</th>
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
                                <td><?php echo $item['dosage_form']; ?></td>
                                <td><?php echo $item['quantity_received']; ?></td>
                                <td><?php echo $item['quantity_issued']; ?></td>
                                <td><?php echo $item['closing_stock']; ?></td>
                                <td><?php echo $item['expiry_date']; ?></td>
                                <td>
                                    <a href="manage_inventory.php?delete=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add Medicine Form -->
            <div id="addMedicineForm" class="form-container">
                <h3>Add New Medicine</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label for="medicine_name" class="form-label">Medicine Name</label>
                        <input type="text" name="medicine_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="generic_name" class="form-label">Generic Name</label>
                        <input type="text" name="generic_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="dosage_form" class="form-label">Dosage Form</label>
                        <input type="text" name="dosage_form" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="strength" class="form-label">Strength</label>
                        <input type="text" name="strength" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="manufacturer_id" class="form-label">Manufacturer</label>
                        <select name="manufacturer_id" class="form-control" required>
                            <option value="">Select a Manufacturer</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?php echo $company['id']; ?>"><?php echo $company['company_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control" required>
                    </div>
                    <button type="submit" name="add_medicine" class="btn btn-success">Add Medicine</button>
                </form>
                <!-- Medicines Table -->
                <h3 class="mt-5">Existing Medicines</h3>
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Medicine Name</th>
                            <th>Generic Name</th>
                            <th>Dosage Form</th>
                            <th>Strength</th>
                            <th>Manufacturer</th>
                            <th>Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medicines as $medicine): ?>
                            <tr>
                                <td><?php echo $medicine['id']; ?></td>
                                <td><?php echo $medicine['medicine_name']; ?></td>
                                <td><?php echo $medicine['generic_name']; ?></td>
                                <td><?php echo $medicine['dosage_form']; ?></td>
                                <td><?php echo $medicine['strength']; ?></td>
                                <td><?php echo $medicine['manufacturer_id']; ?></td>
                                <td><?php echo $medicine['expiry_date']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add Pharma Company Form -->
            <div id="addCompanyForm" class="form-container">
                <h3>Add Pharma Company</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label for="company_id" class="form-label">Company ID</label>
                        <input type="text" name="company_id" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" required>
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
                        <label for="address" class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" required>
                    </div>
                    <button type="submit" name="add_company" class="btn btn-warning">Add Company</button>
                </form>
                <h3>Existing Pharma Companies</h3>
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($companies as $company): ?>
                            <tr>
                                <td><?php echo $company['id']; ?></td>
                                <td><?php echo $company['company_name']; ?></td>
                                <td><?php echo $company['email']; ?></td>
                                <td><?php echo $company['phone']; ?></td>
                                <td><?php echo $company['address']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>