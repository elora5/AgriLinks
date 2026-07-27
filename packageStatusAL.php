<?php
session_start();
include 'dbAL.php';

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT * FROM shipping_harvest WHERE 1=1";

if (!empty($searchTerm)) {
    $searchTerm = $conn->real_escape_string($searchTerm);
    $sql .= " AND (crop_name LIKE '%$searchTerm%' OR farmer_name LIKE '%$searchTerm%' OR from_location LIKE '%$searchTerm%' OR to_location LIKE '%$searchTerm%')";
}

if (!empty($statusFilter)) {
    $statusFilter = $conn->real_escape_string($statusFilter);
    $sql .= " AND shipping_status = '$statusFilter'";
}

$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Records</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f7f6;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
        }

        .search-bar {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .search-bar input[type="text"],
        .search-bar select {
            padding: 10px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .search-bar button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .search-bar button:hover {
            background-color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            margin-top: 20px;
        }

        table thead {
            background-color: #2c3e50;
            color: #ecf0f1;
        }

        table th,
        table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tbody tr:hover {
            background-color: #f1f1f1;
        }

        @media (max-width: 768px) {
            .search-bar {
                flex-direction: column;
                align-items: center;
            }

            .search-bar input[type="text"],
            .search-bar select {
                width: 90%;
            }
        }
    </style>
</head>

<body>

    <h1>Shipping Records</h1>
    <div style="text-align: center; margin-bottom: 20px;">
        <a href="packer_dashboardAL.php" style="text-decoration: none;">
            <button
                style="padding: 10px 20px; background-color: #2ecc71; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Go to Packer Dashboard
            </button>
        </a>
    </div>
    <form method="get" action="">
        <div class="search-bar">
            <input type="text" name="search" placeholder="Search by Crop, Farmer, Location..."
                value="<?php echo htmlspecialchars($searchTerm); ?>">
            <select name="status">
                <option value="">All Status</option>
                <option value="Pending" <?php if ($statusFilter == 'Pending')
                    echo 'selected'; ?>>Pending</option>
                <option value="Shipped" <?php if ($statusFilter == 'Shipped')
                    echo 'selected'; ?>>Shipped</option>
                <option value="In Transit" <?php if ($statusFilter == 'In Transit')
                    echo 'selected'; ?>>In Transit
                </option>
                <option value="Delivered" <?php if ($statusFilter == 'Delivered')
                    echo 'selected'; ?>>Delivered</option>
            </select>
            <button type="submit">Filter</button>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>Shipping ID</th>
                <th>Batch ID</th>
                <th>Farmer Name</th>
                <th>Crop Name</th>
                <th>Quantity</th>
                <th>Weight (kg)</th>
                <th>From</th>
                <th>To</th>
                <th>Status</th>
                <th>Shipping Time</th>
                <th>Delivery Time</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['shipping_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['batch_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['farmer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['crop_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($row['weight']); ?></td>
                        <td><?php echo htmlspecialchars($row['from_location']); ?></td>
                        <td><?php echo htmlspecialchars($row['to_location']); ?></td>
                        <td><?php echo htmlspecialchars($row['shipping_status']); ?></td>
                        <td><?php echo htmlspecialchars($row['shipping_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['delivery_time']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11" style="text-align: center;">No records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>

</html>