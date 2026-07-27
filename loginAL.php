<?php
include "dbAL.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $username = trim($_POST['username']);
    $role = trim($_POST['role']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($role) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: loginAL.php");
        exit();
    }

    // Use prepared statement to query `User_data`
    $sql = "SELECT id, username, password, role FROM User_dataAL WHERE username = ? AND role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Plain-text password comparison
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            switch ($user['role']) {
                case 'Admin':
                    header("Location: admin_dashboardAL.php");
                    break;
                case 'Farmer':
                    header("Location: farmer_dashboardAL.php");
                    break;
                case 'Driver':
                    header("Location: driver_dashboardAL.php");
                    break;
                case 'Inspector':
                    header("Location: inspector_dashboardAL.php");
                    break;
                case 'Packer':
                    header("Location: packer_dashboardAL.php");
                    break;
                case 'Warehouse Manager':
                    header("Location: warehouse_manager_dashboardAL.php");
                    break;
                default:
                    $_SESSION['error'] = "Unauthorized role.";
                    header("Location: loginAL.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid password.";
            header("Location: loginAL.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "User not found.";
        header("Location: loginAL.php");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>
