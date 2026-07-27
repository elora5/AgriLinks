<?php
$host = 'localhost';
$username = 'root';
$password = ''; // default is empty for root in XAMPP
$database = 'agrilinks';
$port = 3306; // default MySQL port

$conn = new mysqli($host, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
