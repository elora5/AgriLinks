<?php
// Start session and include database connection
session_start();
include "./dbAL.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer' && $_SESSION['role'] !== 'Admin') {
    header("Location: loginAL.php");
    exit();
}

// Get logged-in farmer ID from session
$farmer_id = $_SESSION['user_id'];

// Add harvest entry logic
if (isset($_POST['add_harvest'])) {
    $crop_name = $_POST['crop_name'];
    $crop_type = $_POST['crop_type'];
    $batch_date = $_POST['batch_date'];
    $weight = $_POST['weight'];
    $quantity = $_POST['quantity'];

    $sql = "INSERT INTO harvested_batch (farmer_id, crop_name, crop_type, batch_date, weight, quantity) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssdi", $farmer_id, $crop_name, $crop_type, $batch_date, $weight, $quantity);
    if (!$stmt->execute()) {
        echo "Insert Error: " . $stmt->error;
    }
}

// Delete harvest entry logic
if (isset($_GET['delete_batch_id'])) {
    $batch_id = $_GET['delete_batch_id'];
    $sql = "DELETE FROM harvested_batch WHERE batch_id = ? AND farmer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $batch_id, $farmer_id);
    if (!$stmt->execute()) {
        echo "Delete Error: " . $stmt->error;
    }
    header("Location: harvest.php");
    exit();
}

// Fetch harvest data with optional filtering
$sql = "SELECT batch_id, crop_name, crop_type, batch_date AS date_added, weight, quantity 
        FROM harvested_batch WHERE farmer_id = ?";
$params = [$farmer_id];
$types = "i";

if (!empty($_GET['column']) && !empty($_GET['search'])) {
    $allowed_columns = ['crop_name', 'crop_type', 'batch_date', 'weight', 'quantity'];
    $column = $_GET['column'];

    if (in_array($column, $allowed_columns)) {
        $sql .= " AND $column LIKE ?";
        $params[] = "%" . $_GET['search'] . "%";
        $types .= "s";
    }
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Harvest - Farmer Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }
        .navbar {
            background-color: #4CAF50;
            padding: 15px 0;
            color: white;
            text-align: center;
            font-size: 20px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 20px;
            padding: 10px 15px;
        }
        .navbar a:hover {
            background-color: #45a049;
            border-radius: 5px;
        }
        .content {
            padding: 25px;
        }
        .header {
            background-color: #2E7D32;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5rem;
        }
        .header .farmer-name {
            font-size: 1.2rem;
            margin-top: 10px;
        }
        .add-harvest-form input {
            margin-bottom: 10px;
            padding: 10px;
            width: 250px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 1rem;
        }
        .add-harvest-form button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 1rem;
        }
        .add-harvest-form button:hover {
            background-color: #45a049;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .summary-table th, .summary-table td {
            padding: 15px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .summary-table th {
            background-color: #2E7D32;
            color: white;
        }
        .summary-table tr:hover {
            background-color: #f1f1f1;
        }
        .action-btn {
            color: red;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }
        .filter-form {
            margin-top: 20px;
        }
        .filter-form select, .filter-form input {
            padding: 10px;
            margin-right: 10px;
            border-radius: 5px;
            font-size: 1rem;
            border: 1px solid #ccc;
        }
        .filter-form button {
            padding: 10px 15px;
            background-color: #388e3c;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
        }
        .filter-form button:hover {
            background-color: #2e7d32;
        }
        .footer {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
    </style>
</head>
<body>

<div class="navbar">
    <a href="farmer_dashboardAL.php">Dashboard</a>
    <a href="harvest.php">Harvest</a>
    <a href="graded.php">Grading</a>
    <a href="toshipping.php">Shipping</a>
</div>

<div class="content">
    <div class="header">
        <h1>Manage Your Harvest</h1>
        <p class="farmer-name">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
    </div>

    <h3>Add Harvest Entry</h3>
    <form class="add-harvest-form" method="POST">
        <input type="text" name="crop_name" placeholder="Crop Name" required><br>
        <input type="text" name="crop_type" placeholder="Crop Type" required><br>
        <input type="date" name="batch_date" required><br>
        <input type="number" name="weight" placeholder="Weight (kg)" step="0.01" required><br>
        <input type="number" name="quantity" placeholder="Quantity" required><br>
        <button type="submit" name="add_harvest">Add Harvest</button>
    </form>

    <!-- Filter Section -->
    <form method="GET" class="filter-form">
        <label for="column">Filter by:</label>
        <select name="column" required>
            <option value="">--Select Column--</option>
            <option value="crop_name" <?= isset($_GET['column']) && $_GET['column'] == 'crop_name' ? 'selected' : '' ?>>Crop Name</option>
            <option value="crop_type" <?= isset($_GET['column']) && $_GET['column'] == 'crop_type' ? 'selected' : '' ?>>Crop Type</option>
            <option value="batch_date" <?= isset($_GET['column']) && $_GET['column'] == 'batch_date' ? 'selected' : '' ?>>Date</option>
            <option value="weight" <?= isset($_GET['column']) && $_GET['column'] == 'weight' ? 'selected' : '' ?>>Weight</option>
            <option value="quantity" <?= isset($_GET['column']) && $_GET['column'] == 'quantity' ? 'selected' : '' ?>>Quantity</option>
        </select>
        <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" required>
        <button type="submit">Search</button>
        <a href="harvest.php"><button type="button">Clear</button></a>
    </form>

    <!-- Harvest Table -->
    <table class="summary-table">
        <thead>
            <tr>
                <th>Batch ID</th>
                <th>Crop Name</th>
                <th>Crop Type</th>
                <th>Date Added</th>
                <th>Weight (kg)</th>
                <th>Quantity</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['batch_id'] ?></td>
                        <td><?= $row['crop_name'] ?></td>
                        <td><?= $row['crop_type'] ?></td>
                        <td><?= date('d M Y', strtotime($row['date_added'])) ?></td>
                        <td><?= $row['weight'] ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td>
                            <a href="harvest.php?delete_batch_id=<?= $row['batch_id'] ?>" class="action-btn" onclick="return confirm('Delete this entry?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No harvest data available</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="footer">
    <p>AgriLinks &copy; 2025</p>
</div>

</body>
</html>
