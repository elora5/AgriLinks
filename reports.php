<?php
$conn = new mysqli("localhost", "root", "", "agrilinks");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Total packaged batches
$packaged_result = $conn->query("SELECT COUNT(*) as total FROM packaged_batch");
$packaged_total = $packaged_result->fetch_assoc()['total'];

// Packaged batches by status
$status_result = $conn->query("SELECT status, COUNT(*) as count FROM packaged_batch GROUP BY status");

// Grading summary
$grade_result = $conn->query("SELECT grade, COUNT(*) as count FROM graded_batches GROUP BY grade");

// Recent transport logs (last 5)
$transport_result = $conn->query("SELECT * FROM transport_tracking ORDER BY timestamp DESC LIMIT 5");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Warehouse Reports</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f8ff; }
        h2 { color: #2e4a62; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; background: white; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background-color: #e0eaff; }
    </style>
</head>
<body>
    <h2>📊 Warehouse Summary Reports</h2>

    <h3>1. Total Packaged Batches: <?= $packaged_total ?></h3>

    <h3>2. Packaging Status</h3>
    <table>
        <tr><th>Status</th><th>Count</th></tr>
        <?php while ($row = $status_result->fetch_assoc()): ?>
        <tr>
            <td><?= ucfirst($row['status']) ?></td>
            <td><?= $row['count'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h3>3. Grading Summary</h3>
    <table>
        <tr><th>Grade</th><th>Count</th></tr>
        <?php while ($row = $grade_result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['grade'] ?></td>
            <td><?= $row['count'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h3>4. Recent Transport Logs</h3>
    <table>
        <tr><th>ID</th><th>Latitude</th><th>Longitude</th><th>Time</th></tr>
        <?php while ($row = $transport_result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['latitude'] ?></td>
            <td><?= $row['longitude'] ?></td>
            <td><?= $row['timestamp'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
