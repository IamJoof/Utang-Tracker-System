<?php
// db.php - Database connection
$conn = new mysqli("localhost", "root", "", "utang_tracker");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// admin credentials (fixed)
$adminUser = "admin";
$adminPass = "admin123";
?>
