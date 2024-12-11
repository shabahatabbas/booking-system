<?php
// Database Configuration
$host = 'localhost';   // Database Host
$dbname = 'bookingcalander'; // Database Name
$user = 'root';        // Database Username
$pass = '';            // Database Password

// Create a connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check the connection
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>
