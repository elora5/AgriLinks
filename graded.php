<?php
session_start();
include "./dbAL.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: loginAL.php");
    exit();
}

$farmer_id = $_SESSION['user_id'];

$search_column = '';
$search_value = '';
$search_sql = '';
$search_param = '';
$search_param_type = '';

if (isset($_GET['search_column']) && isset($_GET['search_value'])) {
    $search_column = $_GET['search_column'];
    $search_value = $_GET['search_value'];

    $valid_columns = [
        "g.batch_id", "hb.crop_name", "hb.crop_type", "g.inspection_date", 
        "g.grade", "g.freshness", "g.taste", "g.color", "g.shelf_life"
    ];

    if (in_array($search_column, $valid_columns)) {
        $search_sql = " AND $search_column LIKE ?";
        $search_param = "%$search_value%";
        $search_param_type = "s";
    }
}

$sql_grading = "SELECT 
                    g.batch_id, 
                    g.inspector_id, 
                    g.inspection_date,
                    g.freshness, 
                    g.weight AS graded_weight, 
                    g.color, 
                    g.taste, 
                    g.shelf_life, 
                    g.grade,
                    hb.crop_name, 
                    hb.crop_type
                FROM graded_batch g 
                JOIN harvested_batch hb ON g.batch_id = hb.batch_id
                WHERE hb.farmer_id = ? $search_sql";

$stmt = $conn->prepare($sql_grading);

if ($search_sql) {
    $stmt->bind_param("is", $farmer_id, $search_param);
} else {
    $stmt->bind_param("i", $farmer_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grading - Farmer Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
        }
        nav {
            background-color: #2c5f2d;
            padding: 10px 20px;
            text-align: center;
        }
        nav a {
            color: white;
            font-size: 16px;
            text-decoration: none;
            margin: 0 15px;
        }
        .content {
            padding: 20px;
            background-color: white;
            margin: 20px auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            width: 90%;
        }
        h1 {
            text-align: center;
            color: #2c5f2d;
        }
        form {
            margin-bottom: 20px;
            text-align: center;
        }
        input[type="text"], select, button {
            padding: 8px;
            margin: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #2c5f2d;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #1e4020;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #97bc62;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .no-results {
            text-align: center;
            color: #777;
        }
    </style>
</head>
<body>
    <nav>
        <a href="farmer_dashboardAL.php">Dashboard</a>
        <a href="harvest.php">Harvest</a>
        <a href="graded.php">Grading</a>
        <a href="toshipping.php">Shipping</a>
    </nav>

    <div class="content">
        <h1>Grading Results</h1>

        <form method="GET">
            <select name="search_column" required>
                <option value="">Search by</option>
                <option value="g.batch_id" <?= $search_column == "g.batch_id" ? 'selected' : '' ?>>Batch ID</option>
                <option value="hb.crop_name" <?= $search_column == "hb.crop_name" ? 'selected' : '' ?>>Crop Name</option>
                <option value="hb.crop_type" <?= $search_column == "hb.crop_type" ? 'selected' : '' ?>>Crop Type</option>
                <option value="g.grade" <?= $search_column == "g.grade" ? 'selected' : '' ?>>Grade</option>
                <option value="g.color" <?= $search_column == "g.color" ? 'selected' : '' ?>>Color</option>
                <option value="g.taste" <?= $search_column == "g.taste" ? 'selected' : '' ?>>Taste</option>
                <option value="g.freshness" <?= $search_column == "g.freshness" ? 'selected' : '' ?>>Freshness</option>
                <option value="g.shelf_life" <?= $search_column == "g.shelf_life" ? 'selected' : '' ?>>Shelf Life</option>
            </select>
            <input type="text" name="search_value" placeholder="Search value" value="<?= htmlspecialchars($search_value) ?>" required>
            <button type="submit">Search</button>
            <a href="grading.php"><button type="button">Clear</button></a>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Batch ID</th>
                    <th>Crop Name</th>
                    <th>Inspection Date</th>
                    <th>Grade</th>
                    <th>Freshness</th>
                    <th>Taste</th>
                    <th>Color</th>
                    <th>Shelf Life</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['batch_id'] ?></td>
                            <td><?= $row['crop_name'] ?> (<?= $row['crop_type'] ?>)</td>
                            <td><?= date('d M Y', strtotime($row['inspection_date'])) ?></td>
                            <td><?= $row['grade'] ?></td>
                            <td><?= $row['freshness'] ?> / 10</td>
                            <td><?= $row['taste'] ?></td>
                            <td><?= $row['color'] ?></td>
                            <td><?= $row['shelf_life'] ?> days</td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="no-results">No grading results available</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
