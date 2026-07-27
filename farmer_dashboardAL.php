<?php
session_start();
include "./dbAL.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer' && $_SESSION['role'] !== 'Admin') {
    header("Location: loginAL.php");
    exit();
}

$farmer_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Farmer';

// Fetch harvest data
$sql = "SELECT batch_id, crop_name, crop_type, batch_date AS date_added, weight, quantity
        FROM harvested_batch WHERE farmer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();

$monthlyData = [];
$cropData = [];
while ($row = $result->fetch_assoc()) {
    $month = date('M Y', strtotime($row['date_added']));
    $monthlyData[$month] = ($monthlyData[$month] ?? 0) + $row['weight'];

    $crop = $row['crop_name'];
    $cropData[$crop] = ($cropData[$crop] ?? 0) + 1;
}

// Graded batch
$sql_grading = "SELECT g.batch_id, g.inspector_id, g.inspection_date, g.freshness, 
                       g.weight AS graded_weight, g.color, g.taste, g.shelf_life, g.grade,
                       hb.crop_name, hb.crop_type
                FROM graded_batch g 
                JOIN harvested_batch hb ON g.batch_id = hb.batch_id
                WHERE hb.farmer_id = ?";
$stmt_grading = $conn->prepare($sql_grading);
$stmt_grading->bind_param("i", $farmer_id);
$stmt_grading->execute();
$result_grading = $stmt_grading->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Farmer Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f0fdf4;
            color: #1a3e1b;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(to right, #2c5f2d, #3f9142, #97bc62);
            color: white;
            padding: 10px 20px;
        }
        .top-bar img {
            height: 40px;
        }
        nav {
            width: 200px;
            background: #daf5dc;
            padding: 20px;
            float: left;
            height: 100vh;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        nav a {
            display: block;
            margin: 10px 0;
            color: #2c5f2d;
            text-decoration: none;
            font-weight: bold;
        }
        .profile {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #3f9142;
        }
        .dashboard {
            margin-left: 220px;
            padding: 20px;
        }
        .chart-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            flex: 1 1 45%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #b9e4c9;
            text-align: center;
        }
        th {
            background: #3f9142;
            color: white;
        }
        #gradingSearch {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #97bc62;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="logo"><img src="agrilinkLogo.webp" alt="Logo"></div>
        <div class="username">Welcome, <?= htmlspecialchars($username) ?>!</div>
    </div>

    <nav>
        <div class="profile">
            <img src="<?= file_exists("uploads/$username.png") ? "uploads/$username.png" : "profile_pic.png" ?>" alt="Profile Picture">
            <div><?= htmlspecialchars($username) ?></div>
        </div>
        <a href="#">Dashboard</a>
        <a href="harvest.php">Harvest</a>
        <a href="graded.php">Grading</a>
        <a href="toshipping.php">Shipping</a>
        <a href="logoutAL.php">Logout</a>
    </nav>

    <div class="dashboard">
        <h1>Farmer Dashboard</h1>
        <div class="chart-container">
            <div class="card">
                <h2>Monthly Harvest Data</h2>
                <canvas id="monthlyChart"></canvas>
            </div>
            <div class="card">
                <h2>Crop Harvests</h2>
                <canvas id="cropChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h2>Grading Results</h2>
            <input type="text" id="gradingSearch" placeholder="Search Grading Results">
            <table id="gradingTable">
                <thead>
                    <tr>
                        <th>Batch ID</th>
                        <th>Crop Name</th>
                        <th>Inspection Date</th>
                        <th>Grade</th>
                        <th>Freshness</th>
                        <th>Taste</th>
                        <th>Color</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_grading->num_rows > 0): ?>
                        <?php while ($row = $result_grading->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['batch_id'] ?></td>
                                <td><?= $row['crop_name'] ?> (<?= $row['crop_type'] ?>)</td>
                                <td><?= date('d M Y', strtotime($row['inspection_date'])) ?></td>
                                <td><?= $row['grade'] ?></td>
                                <td><?= $row['freshness'] ?>/10</td>
                                <td><?= $row['taste'] ?></td>
                                <td><?= $row['color'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7">No grading results available</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
    const monthlyData = <?= json_encode($monthlyData); ?>;
    const cropData = <?= json_encode($cropData); ?>;

    // Function to generate a random gradient color
    function generateGradient(ctx, color1, color2) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, color1);
        gradient.addColorStop(1, color2);
        return gradient;
    }

    // Function to generate random hex color
    function getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    const ctx1 = document.getElementById('monthlyChart').getContext('2d');
    const barColors = Object.keys(monthlyData).map(() => 
        generateGradient(ctx1, getRandomColor(), getRandomColor())
    );

    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: Object.keys(monthlyData),
            datasets: [{
                label: 'Weight (kg)',
                data: Object.values(monthlyData),
                backgroundColor: barColors
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const ctx2 = document.getElementById('cropChart').getContext('2d');
    const pieColors = Object.keys(cropData).map(() => 
        generateGradient(ctx2, getRandomColor(), getRandomColor())
    );

    new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: Object.keys(cropData),
            datasets: [{
                data: Object.values(cropData),
                backgroundColor: pieColors
            }]
        },
        options: {
            responsive: true
        }
    });

    // Search functionality
    document.getElementById('gradingSearch').addEventListener('keyup', function () {
        const value = this.value.toLowerCase();
        const rows = document.querySelectorAll('#gradingTable tbody tr');
        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
        });
    });
</script>

</body>
</html>