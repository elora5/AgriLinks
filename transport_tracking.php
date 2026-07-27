<?php
// Connect to your actual database
$conn = new mysqli("localhost", "root", "", "agrilinks");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the latest vehicle location
$sql = "SELECT latitude, longitude FROM transport_tracking ORDER BY timestamp DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $lat = $row['latitude'];
    $lng = $row['longitude'];
} else {
    // Default fallback location (e.g., Dhaka)
    $lat = 23.8103;
    $lng = 90.4125;
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Transport Tracking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        #map {
            height: 90vh;
            width: 100%;
        }
    </style>
</head>
<body>
    <h2>Live Transport Vehicle Location</h2>
    <div id="map"></div>

    <script>
        function initMap() {
            var vehicleLocation = { lat: <?= $lat ?>, lng: <?= $lng ?> };
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 14,
                center: vehicleLocation
            });
            new google.maps.Marker({
                position: vehicleLocation,
                map: map,
                title: "Current Location"
            });
        }
    </script>
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap">
    </script>
</body>
</html>
