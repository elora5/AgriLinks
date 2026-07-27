<?php
include 'dbAL.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT * FROM graded_batch WHERE 
        grade LIKE '%$search%' OR 
        batch_id LIKE '%$search%' OR 
        freshness LIKE '%$search%' OR 
        weight LIKE '%$search%' OR
        color LIKE '%$search%' OR
        taste LIKE '%$search%' OR
        shelf_life LIKE '%$search%'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graded Batch Results</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .search-container input {
            padding: 10px;
            width: 300px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .search-container button {
            padding: 10px;
            margin-left: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-container button:hover {
            background-color: #0056b3;
        }

        .back-button {
            display: inline-block;
            margin: 20px 0;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .back-button:hover {
            background-color: #218838;
        }

        .no-results {
            text-align: center;
            color: #999;
            font-size: 16px;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="inspector_dashboardAL.php" class="back-button">Back to Dashboard</a>
        <h1>Graded Batch Results</h1>

        <div class="search-container">
            <form method="get" action="">
                <input type="text" name="search" placeholder="Search by Grade, Batch ID, Freshness, etc."
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Graded Batch ID</th>
                    <th>Batch ID</th>
                    <th>Inspector ID</th>
                    <th>Inspection Date</th>
                    <th>Freshness</th>
                    <th>Weight</th>
                    <th>Color</th>
                    <th>Taste</th>
                    <th>Shelf Life</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['graded_batch_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['batch_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['inspector_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['inspection_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['freshness']); ?></td>
                            <td><?php echo htmlspecialchars($row['weight']); ?></td>
                            <td><?php echo htmlspecialchars($row['color']); ?></td>
                            <td><?php echo htmlspecialchars($row['taste']); ?></td>
                            <td><?php echo htmlspecialchars($row['shelf_life']); ?></td>
                            <td><?php echo htmlspecialchars($row['grade']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="no-results">No results found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>

<?php
$conn->close();
?>