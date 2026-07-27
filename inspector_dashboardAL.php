<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspector Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
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
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 15px 0;
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
            padding: 20px;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2>Inspector Dashboard</h2>
        <ul>
            <li><a href="#">Dashboard</a></li>
            <li><a href="inspectionsAL.php">Grading Inspections</a></li>
            <li><a href="show_graded_batch_resultAL.php">Graded Batch Reports</a></li>
            <li><a href="logoutAL.php">Logout</a></li>
        </ul>
    </div>
    <div class="content">
        <h1>Welcome to the Inspector Dashboard</h1>
        <p>Select an option from the sidebar to get started.</p>
    </div>
</body>

</html>
</ul>