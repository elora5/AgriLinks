<?php
session_start();
include "./dbAL.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Driver' && $_SESSION['role'] !== 'Admin') {
    header("Location: loginAL.php");
    exit();
}

// Fetch Username
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM user_dataal WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Handle Accept Shipment
if (isset($_POST['accept_shipment'])) {
    $batch_id = $_POST['batch_id'];
    try {
        $update = $conn->prepare("UPDATE shipping_harvest 
                                SET shipping_status = 'In Transit', 
                                    shipping_time = NOW() 
                                WHERE batch_id = ?");
        $update->bind_param("i", $batch_id);
        $update->execute();
        if ($update->affected_rows > 0) {
            $_SESSION['success'] = "Shipment accepted successfully!";
        } else {
            $_SESSION['error'] = "No shipment found or already accepted!";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    header("Location: driver_dashboardAL.php");
    exit();
}

// Handle Mark Shipped
if (isset($_POST['mark_shipped'])) {
  $batch_id = $_POST['batch_id'];

  try {
      // Start transaction
      $conn->begin_transaction();

      // First, update shipping_harvest
      $update = $conn->prepare("UPDATE shipping_harvest 
                                SET shipping_status = 'Shipped',
                                    delivery_time = NOW()
                                WHERE batch_id = ?");
      $update->bind_param("i", $batch_id);
      $update->execute();

      if ($update->affected_rows > 0) {
          // Now insert into shipping_history
          $select = $conn->prepare("SELECT shipping_id, from_location, to_location, shipping_time 
                                    FROM shipping_harvest 
                                    WHERE batch_id = ?");
          $select->bind_param("i", $batch_id);
          $select->execute();
          $result = $select->get_result();
          $shipment = $result->fetch_assoc();

          if ($shipment) {
              $shipping_id = $shipment['shipping_id'];
              $from_location = $shipment['from_location'];
              $to_location = $shipment['to_location'];
              $shipping_time = $shipment['shipping_time'];

              $driver_id = $_SESSION['user_id'];
              
              // Fetch driver's name
              $driver_name_stmt = $conn->prepare("SELECT username FROM user_dataal WHERE id = ?");
              $driver_name_stmt->bind_param("i", $driver_id);
              $driver_name_stmt->execute();
              $driver_name_stmt->bind_result($driver_name);
              $driver_name_stmt->fetch();
              $driver_name_stmt->close();

              // Insert into shipping_history
              $insert = $conn->prepare("INSERT INTO shipping_history 
                                        (shipping_id, driver_id, driver_name, from_location, to_location, shipping_time, delivery_time) 
                                        VALUES (?, ?, ?, ?, ?, ?, NOW())");
              $insert->bind_param("iissss", $shipping_id, $driver_id, $driver_name, $from_location, $to_location, $shipping_time);
              $insert->execute();
          }
          $conn->commit();
          $_SESSION['success'] = "Shipment marked as shipped and recorded in history!";
      } else {
          $conn->rollback();
          $_SESSION['error'] = "Failed to mark shipment as shipped!";
      }
  } catch (Exception $e) {
      $conn->rollback();
      $_SESSION['error'] = "Error: " . $e->getMessage();
  }
  header("Location: driver_dashboardAL.php");
  exit();
}



// Fetch Pending Shipments
try {
    $pending_query = "SELECT * FROM shipping_harvest WHERE shipping_status = 'Pending' ORDER BY shipping_time DESC";
    $pending_result = $conn->query($pending_query);
    if (!$pending_result) {
        throw new Exception("Pending Query failed: " . $conn->error);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Fetch Accepted Shipments
try {
    $accepted_query = "SELECT * FROM shipping_harvest WHERE shipping_status = 'In Transit' ORDER BY shipping_time DESC";
    $accepted_result = $conn->query($accepted_query);
    if (!$accepted_result) {
        throw new Exception("Accepted Query failed: " . $conn->error);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver Dashboard</title>
    <style>
        /* Your CSS (same as before) */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            background: linear-gradient(to right, #e0f7fa, #ffffff);
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            background: linear-gradient(180deg, #1DA1F2, #0d8ddb);
            padding: 20px;
            color: white;
            position: fixed;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .logo {
            width: 80px;
            height: 80px;
            background-image: url('agrilinkLogo.webp');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-color: white;
            border-radius: 50%;
            margin-bottom: 20px;
        }
        .profile-pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-image: url('profile_pic.png'); 
            background-size: cover;
            background-position: center;
            margin-bottom: 10px;
        }
        .welcome {
            text-align: center;
            margin-bottom: 30px;
            font-size: 18px;
        }
        .sidebar a, .sidebar form button {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            text-decoration: none;
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 8px;
            text-align: center;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .sidebar a:hover, .sidebar form button:hover {
            background-color: rgba(255, 255, 255, 0.4);
        }
        .main-content {
            margin-left: 270px;
            padding: 30px;
            flex: 1;
            background-color: #f4f9fd;
            min-height: 100vh;
        }
        h1 {
            color: #1DA1F2;
        }
        .search-container {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        input[type="text"], select {
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        input[type="text"] {
            width: 250px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 50px;
        }
        th, td {
            padding: 14px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        th {
            background: linear-gradient(90deg, #1DA1F2, #0d8ddb);
            color: white;
            font-size: 16px;
        }
        tr:hover {
            background-color: #e6f7ff;
        }
        button {
            padding: 8px 14px;
            background-color: #1DA1F2;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0d8ddb;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo"></div>
        <div class="profile-pic"></div>
        <div class="welcome">Welcome, <br><?= htmlspecialchars($username) ?></div>
        <a href="driver_dashboardAL.php">Dashboard</a>
        <a href="driver_performance.php">Performance</a>
        <form method="POST" action="logoutAL.php">
            <button type="submit">Logout</button>
        </form>
    </div>

    <div class="main-content">
        <h1>Pending Shipments</h1>

        <div class="search-container">
            <input type="text" id="pendingSearchInput" placeholder="Search Pending...">
            <select id="pendingColumnSelect">
                <option value="0">Batch ID</option>
                <option value="1">Crop Name</option>
                <option value="2">Quantity</option>
                <option value="3">Weight</option>
                <option value="4">Farmer</option>
                <option value="5">Status</option>
                <option value="6">Shipping Time</option>
            </select>
        </div>

        <table id="pendingTable">
            <thead>
                <tr>
                    <th>Batch ID</th>
                    <th>Crop Name</th>
                    <th>Quantity</th>
                    <th>Weight</th>
                    <th>Farmer</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Status</th>
                    <th>Shipping Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $pending_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['batch_id']) ?></td>
                    <td><?= htmlspecialchars($row['crop_name']) ?></td>
                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                    <td><?= htmlspecialchars($row['weight']) ?></td>
                    <td><?= htmlspecialchars($row['farmer_name']) ?></td>
                    <td><?= htmlspecialchars($row['from_location']) ?></td>
                    <td><?= htmlspecialchars($row['to_location']) ?></td>
                    <td><?= htmlspecialchars($row['shipping_status']) ?></td>
                    <td><?= $row['shipping_time'] ? htmlspecialchars($row['shipping_time']) : 'Not Shipped' ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="batch_id" value="<?= htmlspecialchars($row['batch_id']) ?>">
                            <button type="submit" name="accept_shipment" onclick="return confirm('Accept this shipment?')">Accept</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h1>Accepted Shipments</h1>

        <div class="search-container">
            <input type="text" id="acceptedSearchInput" placeholder="Search Accepted...">
            <select id="acceptedColumnSelect">
                <option value="0">Batch ID</option>
                <option value="1">Crop Name</option>
                <option value="2">Quantity</option>
                <option value="3">Weight</option>
                <option value="4">Farmer</option>
                <option value="5">Status</option>
                <option value="6">Shipping Time</option>
            </select>
        </div>

        <table id="acceptedTable">
            <thead>
                <tr>
                    <th>Batch ID</th>
                    <th>Crop Name</th>
                    <th>Quantity</th>
                    <th>Weight</th>
                    <th>Farmer</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Status</th>
                    <th>Shipping Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $accepted_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['batch_id']) ?></td>
                    <td><?= htmlspecialchars($row['crop_name']) ?></td>
                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                    <td><?= htmlspecialchars($row['weight']) ?></td>
                    <td><?= htmlspecialchars($row['farmer_name']) ?></td>
                    <td><?= htmlspecialchars($row['from_location']) ?></td>
                    <td><?= htmlspecialchars($row['to_location']) ?></td>
                    <td><?= htmlspecialchars($row['shipping_status']) ?></td>
                    <td><?= $row['shipping_time'] ? htmlspecialchars($row['shipping_time']) : 'Not Shipped' ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="batch_id" value="<?= htmlspecialchars($row['batch_id']) ?>">
                            <button type="submit" name="mark_shipped" onclick="return confirm('Mark this shipment as shipped?')">Shipped!</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>

    <script>
        function setupSearch(inputId, columnSelectId, tableId) {
            const input = document.getElementById(inputId);
            const columnSelect = document.getElementById(columnSelectId);
            const table = document.getElementById(tableId);

            input.addEventListener("keyup", function () {
                const filter = input.value.toLowerCase();
                const columnIndex = parseInt(columnSelect.value);
                const rows = table.querySelectorAll("tbody tr");

                rows.forEach(row => {
                    const cells = row.getElementsByTagName("td");
                    const text = cells[columnIndex]?.innerText.toLowerCase() || "";
                    row.style.display = text.includes(filter) ? "" : "none";
                });
            });
        }

        setupSearch("pendingSearchInput", "pendingColumnSelect", "pendingTable");
        setupSearch("acceptedSearchInput", "acceptedColumnSelect", "acceptedTable");
    </script>

</body>
</html>
