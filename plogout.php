<?php
session_start();

// Destroy the session to log the user out
session_unset(); // Remove all session variables
session_destroy(); // Destroy the session

// Redirect to admin login page with a thank you message
header("Location: login.php?message=Thank you for logging out!");
exit;
?>