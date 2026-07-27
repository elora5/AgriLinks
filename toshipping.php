<?php
session_start();
include "./dbAL.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer' && $_SESSION['role'] !== 'Admin') {
    header("Location: loginAL.php");
    exit();
}

$farmer_id = $_SESSION['user_id'];

// Handle Ship action
if (isset($_POST['ship_batch'])) {
    $batch_id = $_POST['batch_id'];
    $to_location = $_POST['to_location'];

    // Fetch batch info
    $stmt = $conn->prepare("SELECT crop_name, weight, quantity FROM harvested_batch WHERE batch_id = ? AND farmer_id = ?");
    $stmt->bind_param("ii", $batch_id, $farmer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $batch = $result->fetch_assoc();

    // Fetch farmer info
    $stmt2 = $conn->prepare("SELECT name, address FROM user_dataal WHERE id = ?");
    $stmt2->bind_param("i", $farmer_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $user = $result2->fetch_assoc();

    if ($batch && $user) {
        $farmer_name = $user['name'];
        $from_location = $user['address'];

        // Insert into shipping_harvest
        $insert = $conn->prepare("INSERT INTO shipping_harvest 
            (batch_id, farmer_id, crop_name, quantity, weight, farmer_name, from_location, to_location, shipping_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $insert->bind_param("iisissss", $batch_id, $farmer_id, $batch['crop_name'], $batch['quantity'], $batch['weight'], $farmer_name, $from_location, $to_location);
        $insert->execute();
    }

    header("Location: toshipping.php?message=Batch marked for shipping!");
    exit();
}

// Fetch batches
$query = "SELECT hb.batch_id, hb.crop_name, hb.quantity, hb.weight, 
                 sh.shipping_status, sh.shipping_time, sh.to_location 
          FROM harvested_batch hb 
          LEFT JOIN shipping_harvest sh ON hb.batch_id = sh.batch_id 
          WHERE hb.farmer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$batches = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Farmer - To Ship</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; margin: 0; padding: 0; }
        .container { width: 90%; margin: 30px auto; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
        th, td { padding: 12px; text-align: center; border-bottom: 1px solid #ddd; }
        th { background: #97bc62; color: #fff; }
        tr:hover { background: #f1f1f1; }
        .message { background: #28a745; color: white; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .search-container { margin-bottom: 20px; display: flex; gap: 10px; }
        .navbar { background: #97bc62; overflow: hidden; }
        .navbar a { float: left; padding: 14px; color: white; text-decoration: none; }
        .navbar a:hover { background: #2c5f2d; }
    </style>
</head>
<body>

<div class="navbar">
    <a href="farmer_dashboardAL.php">Dashboard</a>
    <a href="harvest.php">Harvest</a>
    <a href="graded.php">Grading</a>
</div>

<div class="container">
    <h1>To Ship Table</h1>

    <?php if (isset($_GET['message'])): ?>
        <div class="message"><?= htmlspecialchars($_GET['message']) ?></div>
    <?php endif; ?>

    <div class="search-container">
        <select id="columnSelect">
            <option value="batch_id">Batch ID</option>
            <option value="crop_name">Crop Name</option>
            <option value="quantity">Quantity</option>
            <option value="weight">Weight</option>
            <option value="shipping_status">Shipping Status</option>
        </select>
        <input type="text" id="searchInput" placeholder="Search...">
        <button onclick="clearSearch()">Clear</button>
    </div>

    <table id="shippingTable">
        <thead>
            <tr>
                <th>Batch ID</th>
                <th>Crop Name</th>
                <th>Quantity</th>
                <th>Weight</th>
                <th>Shipping Status</th>
                <th>Shipping Time</th>
                <th>Shipping Location</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $batches->fetch_assoc()): ?>
            <tr>
                <td><?= $row['batch_id'] ?></td>
                <td><?= $row['crop_name'] ?></td>
                <td><?= $row['quantity'] ?></td>
                <td><?= $row['weight'] ?></td>
                <td><?= $row['shipping_status'] ?? 'Not Shipped' ?></td>
                <td><?= $row['shipping_time'] ?? 'N/A' ?></td>
                <td><?= $row['to_location'] ?? 'Not Set' ?></td>
                <td>
                    <?php if (!$row['shipping_status']): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="batch_id" value="<?= $row['batch_id'] ?>">
                            <input type="text" name="to_location" placeholder="Enter destination" required>
                            <button type="submit" name="ship_batch">Ship</button>
                        </form>
                    <?php else: ?>
                        Done
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
const input = document.getElementById("searchInput");
const columnSelect = document.getElementById("columnSelect");

input.addEventListener("keyup", function () {
    const filter = input.value.toLowerCase();
    const column = columnSelect.selectedIndex;
    const rows = document.querySelectorAll("#shippingTable tbody tr");

    rows.forEach(row => {
        const cell = row.cells[column];
        const text = cell.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});

function clearSearch() {
    input.value = '';
    columnSelect.selectedIndex = 0;
    document.querySelectorAll("#shippingTable tbody tr").forEach(row => row.style.display = "");
}
</script>

</body>
</html>
