<?php
include "./dbAL.php";
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo "Access Denied";
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "Invalid user ID.";
    exit();
}

$stmt = $conn->prepare("DELETE FROM user_dataal WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: admin_home.php");
exit();
?>
