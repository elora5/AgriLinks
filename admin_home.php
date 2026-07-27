<?php
include "./dbAL.php";
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo "Access Denied";
    exit();
}

$columns = ["username", "role", "name", "email", "dob", "address"];
$searchCol = $_GET['column'] ?? '';
$searchTerm = $_GET['term'] ?? '';

$sql = "SELECT * FROM user_dataal";
if ($searchCol && $searchTerm && in_array($searchCol, $columns)) {
    $sql .= " WHERE $searchCol LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $searchVal);
    $searchVal = "%$searchTerm%";
} else {
    $stmt = $conn->prepare($sql);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Users</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 20px;
        }

        h2 {
            margin-bottom: 20px;
        }

        .search-bar {
            margin-bottom: 15px;
        }

        select, input[type="text"], button {
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-right: 5px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f14646;
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .actions a {
            margin-right: 10px;
            text-decoration: none;
            color: #f14646;
            font-weight: bold;
        }

        .actions a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h2>User Management</h2>

<div class="search-bar">
    <form method="GET">
        <select name="column">
            <option value="">Select column</option>
            <?php foreach ($columns as $col): ?>
                <option value="<?= $col ?>" <?= $col === $searchCol ? 'selected' : '' ?>><?= ucfirst($col) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="term" placeholder="Search..." value="<?= htmlspecialchars($searchTerm) ?>">
        <button type="submit">Search</button>
        <a href="admin_home.php"><button type="button">Clear</button></a>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Role</th>
            <th>Name</th>
            <th>Email</th>
            <th>DOB</th>
            <th>Address</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['role']) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['dob']) ?></td>
            <td><?= htmlspecialchars($row['address']) ?></td>
            <td class="actions">
                <a href="edit_user.php?id=<?= $row['id'] ?>">Edit</a>
                <a href="delete_user.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this user?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
