<?php
include 'dbAL.php';
 
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
 
$message = "";
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $graded_batch_id = intval($_POST['graded_batch_id']);
    $batch_id = intval($_POST['batch_id']);
    $inspector_id = intval($_POST['inspector_id']);
    $inspection_date = $_POST['inspection_date'];
    $freshness = intval($_POST['freshness']);
    $weight = floatval($_POST['weight']);
    $color = $_POST['color'];
    $taste = $_POST['taste'];
    $shelf_life = intval($_POST['shelf_life']);
    $grade = $_POST['grade'];
 
    $sql = "INSERT INTO graded_batch 
        (graded_batch_id, batch_id, inspector_id, inspection_date, freshness, weight, color, taste, shelf_life, grade)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
 
    $stmt = $conn->prepare($sql);
 
    if ($stmt) {
        $stmt->bind_param("iiisidssis", $graded_batch_id, $batch_id, $inspector_id, $inspection_date, $freshness, $weight, $color, $taste, $shelf_life, $grade);
        if ($stmt->execute()) {
            $message = "<p class='success'>Graded Batch record added successfully!</p>";
        } else {
            $message = "<p class='error'>Execute failed: " . htmlspecialchars($stmt->error) . "</p>";
        }
    } else {
        $message = "<p class='error'>Prepare failed: " . htmlspecialchars($conn->error) . "</p>";
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
 
<head>
    <meta charset="UTF-8">
    <title>Add Graded Batch</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
 
        .container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
            max-width: 700px;
            width: 100%;
        }
 
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
 
        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px 20px;
        }
 
        form label {
            display: block;
            font-weight: 500;
            color: #555;
            margin-bottom: 5px;
        }
 
        form input,
        form select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
 
        .full-width {
            grid-column: 1 / -1;
        }
 
        button[type="submit"] {
            grid-column: 1 / -1;
            padding: 12px;
            background: #4CAF50;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
 
        button[type="submit"]:hover {
            background: #45a049;
        }
 
        .success,
        .error {
            grid-column: 1 / -1;
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
        }
 
        .success {
            color: green;
        }
 
        .error {
            color: red;
        }
 
        .back-link {
            margin-bottom: 15px;
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 13px;
        }
 
        @media (max-width: 600px) {
            form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
 
<body>
    <div class="container">
        <a href="inspector_dashboardAL.php" class="back-link">← Back</a>
        <h2>Add Graded Batch</h2>
        <?php echo $message; ?>
        <form method="post" action="">
            <div>
                <label>Graded Batch ID:</label>
                <input type="number" name="graded_batch_id" required>
            </div>
            <div>
                <label>Batch ID:</label>
                <input type="number" name="batch_id" required>
            </div>
 
            <div>
                <label>Inspector ID:</label>
                <input type="number" name="inspector_id" required>
            </div>
 
            <div>
                <label>Inspection Date:</label>
                <input type="date" name="inspection_date" required>
            </div>
 
            <div>
                <label>Freshness:</label>
                <input type="number" name="freshness" required>
            </div>
 
            <div>
                <label>Weight (kg):</label>
                <input type="text" name="weight" required>
            </div>
 
            <div>
                <label>Color:</label>
                <input type="text" name="color" required>
            </div>
 
            <div>
                <label>Taste:</label>
                <input type="text" name="taste" required>
            </div>
 
            <div>
                <label>Shelf Life (days):</label>
                <input type="number" name="shelf_life" required>
            </div>
 
            <div class="full-width">
                <label>Grade:</label>
                <select name="grade" required>
                    <option value="">Select</option>
                    <option value="S">S</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            </div>
 
            <button type="submit">Add Graded Batch</button>
        </form>
    </div>
</body>
 
 
</html>