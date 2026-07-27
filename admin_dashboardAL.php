<?php
session_start();
include "./dbAL.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: loginAL.php");
    exit();
}

$username = $_SESSION['username'] ?? 'Unknown';
$role = $_SESSION['role'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AgriLinks Unified Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            background-color: #fff5f5;
        }

        .sidebar {
            width: 230px;
            background: linear-gradient(to bottom, #d32f2f, #f44336, #fff);
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
            position: fixed;
        }

        .sidebar h2 {
            font-size: 1.6rem;
            margin-bottom: 30px;
            color: #fff;
        }

        .sidebar a {
            width: 90%;
            padding: 12px 15px;
            margin: 5px 0;
            text-decoration: none;
            color: #fff;
            background: #e53935;
            border-radius: 8px;
            text-align: left;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .sidebar a:hover {
            background: #c62828;
        }

        .main-content {
            margin-left: 230px;
            width: calc(100% - 230px);
            background-color: #fff;
            height: 100vh;
            overflow: hidden;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .profile {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 14px;
            text-align: right;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>AgriLinks</h2>

        <!-- Always visible to Admin -->
        <?php if ($role === 'Admin'): ?>
            <a href="#" onclick="loadPage('admin_home.php')">Dashboard</a>
            <a href="#" onclick="loadPage('farmer_dashboardAL.php')">Farmer Dashboard</a>
            <a href="#" onclick="loadPage('driver_dashboardAL.php')">Driver Dashboard</a>
            <a href="#" onclick="loadPage('inspector_dashboardAL.php')">Inspector Dashboard</a>
            <a href="#" onclick="loadPage('packer_dashboardAL.php')">Package Manager Dashboard</a>
            <a href="#" onclick="loadPage('warehouse_manager_dashboardAL.php')">Warehouse Dashboard</a>
        <?php endif; ?>

        <a href="logoutAL.php">Logout</a>
    </div>

    <div class="main-content">
        <iframe id="contentFrame" src="<?php
            echo ($role === 'Admin') ? 'admin_home.php' : strtolower($role) . '_dashboardAL.php';
        ?>"></iframe>
    </div>

    <script>
        function loadPage(page) {
            document.getElementById('contentFrame').src = page;
        }
    </script>
</body>
</html>
