<?php
session_start();
include "./dbAL.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: loginAL.php");
    exit();
}

// Fetch summary counts
$graded_count = $conn->query("SELECT COUNT(*) as total FROM graded_batch")->fetch_assoc()['total'];
$packaged_count = $conn->query("SELECT COUNT(*) as total FROM packaged_batch")->fetch_assoc()['total'];
$in_transit_count = $conn->query("SELECT COUNT(*) as total FROM transport WHERE current_status='in_transit'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Warehouse Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f4f0f8;
        }
        .top-bar {
            background-color: #b39ddb; /* Lavender Purple */
            color: white;
            padding: 20px;
            text-align: center;
        }
        nav {
            width: 220px;
            height: 100vh;
            background-color: #9575cd; /* Medium Lavender */
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
            background-color: #d1c4e9; /* Light Lavender */
            color: #4a148c;
        }
        .content {
            margin-left: 240px;
            padding: 30px;
        }
        .summary {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-box {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            flex: 1;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 5px solid #ce93d8;
        }
        h2 {
            color: #6a1b9a; /* Deep Purple */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-bottom: 40px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ccc;
        }
        th {
            background-color: #e1bee7; /* Lavender Table Head */
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
    <div class="summary">
        <div class="summary-box">
            <h2><?= $graded_count ?></h2>
            <p>Total Graded Batches</p>
        </div>
        <div class="summary-box">
            <h2><?= $packaged_count ?></h2>
            <p>Total Packaged Batches</p>
        </div>
        <div class="summary-box">
            <h2><?= $in_transit_count ?></h2>
            <p>In Transit</p>
        </div>
    </div>

    <h2>Latest Graded Produce</h2>
    <table>
        <tr>
            <th>Batch ID</th>
            <th>Crop</th>
            <th>Grade</th>
            <th>Weight</th>
            <th>Date</th>
        </tr>
        <?php
        $result = $conn->query("SELECT g.batch_id, hb.crop_name, g.grade, g.weight, g.inspection_date
                                FROM graded_batch g
                                JOIN harvested_batch hb ON g.batch_id = hb.batch_id
                                ORDER BY g.inspection_date DESC LIMIT 5");
        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?= $row['batch_id'] ?></td>
            <td><?= $row['crop_name'] ?></td>
            <td><?= $row['grade'] ?></td>
            <td><?= $row['weight'] ?> kg</td>
            <td><?= date('d M Y', strtotime($row['inspection_date'])) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h2>Ongoing Transport</h2>
    <table>
        <tr>
            <th>Batch ID</th>
            <th>From</th>
            <th>To</th>
            <th>Status</th>
        </tr>
        <?php
        $transit = $conn->query("SELECT * FROM transport WHERE current_status='in_transit' LIMIT 5");
        while ($row = $transit->fetch_assoc()):
        ?>
        <tr>
            <td><?= $row['batch_id'] ?></td>
            <td><?= $row['origin'] ?></td>
            <td><?= $row['destination'] ?></td>
            <td><?= ucfirst($row['current_status']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>


