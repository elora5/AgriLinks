<?php
session_start();
include 'dbAL.php';

// Fetch package counts
$shippedCount = $conn->query("SELECT COUNT(*) as total FROM shipping_harvest WHERE shipping_status = 'Shipped'")->fetch_assoc()['total'];
$pendingCount = $conn->query("SELECT COUNT(*) as total FROM shipping_harvest WHERE shipping_status = 'Pending'")->fetch_assoc()['total'];
$deliveredCount = $conn->query("SELECT COUNT(*) as total FROM shipping_harvest WHERE shipping_status = 'Delivered'")->fetch_assoc()['total'];
$inTransitCount = $conn->query("SELECT COUNT(*) as total FROM shipping_harvest WHERE shipping_status = 'In Transit'")->fetch_assoc()['total'];
$totalPackages = $conn->query("SELECT COUNT(*) as total FROM shipping_harvest")->fetch_assoc()['total'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packaging Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7f6;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #2c3e50;
            color: #ecf0f1;
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 20px 0;
        }

        .sidebar ul li a {
            color: #ecf0f1;
            text-decoration: none;
            font-size: 16px;
        }

        .sidebar ul li a:hover {
            text-decoration: underline;
        }

        .content {
            margin-left: 270px;
            padding: 30px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .card {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .card p {
            font-size: 24px;
            font-weight: bold;
            color: #007BFF;
        }

        h1 {
            color: #333;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2>Packaging Dashboard</h2>
        <ul>
            <li><a href="#">Dashboard</a></li>
            <li><a href="add_shipping_harvest.php">Create Package</a></li>
            <li><a href="packageStatusAL.php">Package Status</a></li>
            <li><a href="logoutAL.php">Logout</a></li>
        </ul>
    </div>

    <div class="content">
        <h1>Welcome to the Packaging Dashboard</h1>
        <p>Select an option from the sidebar to get started.</p>

        <div class="cards">
            <div class="card">
                <h3>Shipped Packages</h3>
                <p><?php echo $shippedCount; ?></p>
            </div>
            <div class="card">
                <h3>Pending Packages</h3>
                <p><?php echo $pendingCount; ?></p>
            </div>
            <div class="card">
                <h3>Delivered Packages</h3>
                <p><?php echo $deliveredCount; ?></p>
            </div>
            <div class="card">
                <h3>In-Transit Packages</h3>
                <p><?php echo $inTransitCount; ?></p>
            </div>
            <div class="card">
                <h3>Total Packages</h3>
                <p><?php echo $totalPackages; ?></p>
            </div>
        </div>
    </div>
</body>

</html>