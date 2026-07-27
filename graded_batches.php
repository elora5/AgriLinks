<?php
session_start();
include "./dbAL.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: loginAL.php");
    exit();
}

$result = $conn->query("SELECT g.batch_id, hb.crop_name, g.grade, g.weight, g.inspection_date, g.inspector
                        FROM graded_batch g
                        JOIN harvested_batch hb ON g.batch_id = hb.batch_id
                        ORDER BY g.inspection_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Graded Produce</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f4f0f8;
        }
        .top-bar {
            background-color: #b39ddb;
            color: white;
            padding: 20px;
            text-align: center;
        }
        nav {
            width: 220px;
            height: 100vh;
            background-color: #9575cd;
            position: fixed;
            padding-top: 30px;
            color: white;
        }
        nav a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
        }
        nav a:hover {
            background-color: #d1c4e9;
            color: #4a148c;
        }
        .content {
            margin-left: 240px;
            padding: 30px;
        }
        h2 {
            color: #6a1b9a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ccc;
        }
        th {
            background-color: #e1bee7;
        }
        tr:nth-child(even) {
            background-color: #f3e5f5;
        }
    </style>
</head>
<body>

<div class="top-bar">
    <h1>Warehouse Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></p>
</div>

<nav>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="graded_batches.php">📦 Graded Produce</a>
    <a href="packaging.php">🎁 Packaging Status</a>
    <a href="transport_tracking.php">🚚 Transport Tracking</a>
    <a href="reports.php">📊 Reports</a>
    <a href="logout.php">🚪 Logout</a>
</nav>

<div class="content">
    <h2>Graded Produce Records</h2>
    <table>
        <tr>
            <th>Batch ID</th>
            <th>Crop</th>
            <th>Grade</th>
            <th>Weight (kg)</th>
            <th>Inspector</th>
            <th>Inspection Date</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['batch_id'] ?></td>
            <td><?= $row['crop_name'] ?></td>
            <td><?= $row['grade'] ?></td>
            <td><?= $row['weight'] ?></td>
            <td><?= $row['inspector'] ?></td>
            <td><?= date('d M Y', strtotime($row['inspection_date'])) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
