<?php
include 'dbAL.php';
session_start();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (
    isset($_POST['batch_id']) && isset($_POST['grading_criteria']) && isset($_POST['weight']) && isset($_POST['quantity'])
    && isset($_POST['expected_freshness']) && isset($_POST['expected_color']) && isset($_POST['expected_taste']) &&
    isset($_POST['expected_shelf_life'])
) {
    $batch_id = intval($_POST['batch_id']);
    $grade = trim($_POST['grading_criteria']);
    $weight = floatval($_POST['weight']);
    $quantity = intval($_POST['quantity']);
    $inspector_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $inspection_date = date('Y-m-d');
    $freshness = intval($_POST['expected_freshness']);
    $color = trim($_POST['expected_color']);
    $taste = trim($_POST['expected_taste']);
    $shelf_life = intval($_POST['expected_shelf_life']);

    $stmt = $conn->prepare("
    INSERT INTO graded_batch (batch_id, inspector_id, inspection_date, freshness, color, taste, shelf_life, grade, weight)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "iisissisd",
        $batch_id,
        $inspector_id,
        $inspection_date,
        $freshness,
        $color,
        $taste,
        $shelf_life,
        $grade,
        $weight
    );

    if ($stmt->execute()) {
        echo "<div class='success-message'>Batch ID " . htmlspecialchars($batch_id) . " graded successfully as " . htmlspecialchars($grade) . ".</div>";
    } else {
        echo "<div class='error-message'>Error grading batch: " . $stmt->error . "</div>";
    }

    $stmt->close();
} else {
    echo "<div class='error-message'>Invalid submission. Batch ID or Grade missing.</div>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Grading</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
        }

        .success-message {
            color: green;
            font-size: 18px;
        }

        .error-message {
            color: red;
            font-size: 18px;
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            margin: 20px 0;
            background-color: #007bff;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
            color: #333;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Grading Result</h1>
        <div class="message">

            <?php if (isset($grade)): ?>
                <div class="success-message">Batch ID <?= htmlspecialchars($batch_id) ?> graded successfully as
                    <?= htmlspecialchars($grade) ?>.</div>
            <?php else: ?>
                <div class="error-message">Error grading batch.</div>
            <?php endif; ?>
        </div>
        <a href="inspectionAL.php" class="back-button">Back to Inspection</a>
    </div>

</body>

</html>