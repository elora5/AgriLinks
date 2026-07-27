<?php
session_start();
include "./dbAL.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Driver' && $_SESSION['role'] !== 'Admin')) {
    header("Location: loginAL.php");
    exit();
}

$driver_id = $_SESSION['user_id'];

// Get shipment counts by delivery date
$shipmentQuery = "SELECT DATE(delivery_time) AS delivery_date, COUNT(*) AS total 
                  FROM shipping_harvest 
                  WHERE shipping_status = 'Shipped' 
                  GROUP BY DATE(delivery_time)
                  ORDER BY delivery_date ASC";
$result = $conn->query($shipmentQuery);

$dataPoints = [];
while ($row = $result->fetch_assoc()) {
    $dataPoints[] = ['date' => $row['delivery_date'], 'total' => $row['total']];
}

// Get locations
$locationQuery = "SELECT from_location, to_location FROM shipping_harvest WHERE shipping_status = 'Shipped'";
$locationResult = $conn->query($locationQuery);

$locations = [];
while ($row = $locationResult->fetch_assoc()) {
    $locations[] = $row;
}

// Get table data
$shipmentTableQuery = "SELECT shipping_id, from_location, to_location, delivery_time 
                       FROM shipping_harvest 
                       WHERE shipping_status = 'Shipped' 
                       ORDER BY delivery_time DESC 
                       LIMIT 10";
$shipmentTableResult = $conn->query($shipmentTableQuery);

$shipmentTableRows = [];
while ($row = $shipmentTableResult->fetch_assoc()) {
    $shipmentTableRows[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Driver Performance</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <link href="https://unpkg.com/leaflet/dist/leaflet.css" rel="stylesheet" />
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(#2c5f2d, rgb(25, 147, 204));
            color: white;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
        }

        .sidebar img.logo {
            width: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .sidebar .profile-pic {
            width: 80px;
            height: 80px;
            background-color: white;
            border-radius: 50%;
            margin: 10px auto;
        }

        .sidebar a {
            text-decoration: none;
            color: white;
            padding: 12px 20px;
            display: block;
            width: 100%;
            text-align: center;
        }

        .sidebar a:hover {
            background-color: rgb(63, 142, 232);
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        .performance-content {
            display: flex;
            gap: 20px;
        }

        .left-panel, .right-panel {
            flex: 1;
        }

        .center-title {
            text-align: center;
            font-size: 24px;
            color: #003f5c;
            margin-top: 0;
        }

        #chartContainer {
            width: 100%;
            background-color: #ffffff;
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        #map {
            height: 400px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .shipment-table-container {
            margin-top: 20px;
            overflow-x: auto;
        }

        .shipment-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .shipment-table th, .shipment-table td {
            padding: 12px 16px;
            text-align: left;
        }

        .shipment-table thead {
            background: linear-gradient(to right, #003f5c, #2f95dc); /* deep blue to sky blue */
            color: white;
        }

        .shipment-table tbody tr:nth-child(even) {
            background-color: #e3f2fd;
        }

        .shipment-table tbody tr:hover {
            background-color: #bbdefb;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <img src="agrilinkLogo.webp" alt="Logo" class="logo">
    <div class="profile-pic">
    <img src="profile_pic.png" alt="Logo" class="logo">
    </div>
    <a href="driver_dashboardAL.php">Dashboard</a>
    <a href="driver_performance.php">Performance</a>
</div>

<div class="main-content">
    <div class="performance-content">

        <div class="left-panel">
            <h2 class="center-title">Daily Shipments</h2>
            <div id="chartContainer">
                <canvas id="shipmentChart"></canvas>
            </div>

            <h2 class="center-title">Delivery Routes</h2>
            <div id="map"></div>
        </div>

        <div class="right-panel">
            <h2 class="center-title">Successful Shipments</h2>
            <div class="shipment-table-container">
                <table class="shipment-table">
                    <thead>
                        <tr>
                            <th>Shipping ID</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Completion Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shipmentTableRows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['shipping_id']) ?></td>
                                <td><?= htmlspecialchars($row['from_location']) ?></td>
                                <td><?= htmlspecialchars($row['to_location']) ?></td>
                                <td><?= date("d M Y", strtotime($row['delivery_time'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
    const ctx = document.getElementById('shipmentChart').getContext('2d');
    const chartData = {
        labels: <?= json_encode(array_column($dataPoints, 'date')) ?>,
        datasets: [{
            label: 'Shipments',
            data: <?= json_encode(array_column($dataPoints, 'total')) ?>,
            backgroundColor: '#2f95dc'
        }]
    };

    new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: {
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Delivery Date'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Shipments'
                    }
                }
            }
        }
    });

    const map = L.map('map').setView([22.9734, 78.6569], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    <?php foreach ($locations as $loc): ?>
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=<?= urlencode($loc['from_location']) ?>`)
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                const fromLatLng = [data[0].lat, data[0].lon];
                L.marker(fromLatLng).addTo(map).bindPopup("From: <?= $loc['from_location'] ?>");

                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=<?= urlencode($loc['to_location']) ?>`)
                    .then(res => res.json())
                    .then(data2 => {
                        if (data2.length > 0) {
                            const toLatLng = [data2[0].lat, data2[0].lon];
                            L.marker(toLatLng).addTo(map).bindPopup("To: <?= $loc['to_location'] ?>");
                            L.polyline([fromLatLng, toLatLng], {color: 'green'}).addTo(map);
                        }
                    });
            }
        });
    <?php endforeach; ?>
</script>

</body>
</html>
