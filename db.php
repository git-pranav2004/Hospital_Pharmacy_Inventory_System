<?php
// Database connection parameters
$host = 'localhost';       // Hostname (usually localhost)
$db = 'hpis';              // Database name
$user = 'root';            // Database username
$password = '';            // Database password

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);

    // Set PDO error mode to exception for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Connection successful message (optional, for debugging purposes)
    // echo "Connected successfully to the database.";
} catch (PDOException $e) {
    // Handle connection error
    die("Database connection failed: " . $e->getMessage());
}
?>