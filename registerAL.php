<?php
include "dbAL.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm-password']);

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: registerAL.html");
        exit();
    }

    // Check if username already exists
    $sql_check = "SELECT * FROM User_dataAL WHERE username = '$username'";
    $result = mysqli_query($conn, $sql_check);
    
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Username already taken. Please choose another.";
        header("Location: registerAL.html");
        exit();
    }

    // Insert new user data into the database without password hashing
    $sql = "INSERT INTO User_dataAL (username, password, role, name, email, dob, address, created_at) 
            VALUES ('$username', '$password', '$role', '$name', '$email', '$dob', '$address', NOW())";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Registration successful! You can now log in.";
        header("Location: loginAL.html");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
        header("Location: registerAL.html");
        exit();
    }
}

$conn->close();
?>
