<?php
include "./dbAL.php";
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo "Access Denied";
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "Invalid user ID.";
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM user_dataal WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $role = $_POST['role'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("UPDATE user_dataal SET username=?, role=?, name=?, email=?, dob=?, address=? WHERE id=?");
    $stmt->bind_param("ssssssi", $username, $role, $name, $email, $dob, $address, $id);
    $stmt->execute();

    header("Location: admin_home.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 60px auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
            font-size: 16px;
        }

        button {
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 10px;
        }

        button[type="submit"] {
            background-color: #28a745;
            color: white;
        }

        button[type="submit"]:hover {
            background-color: #218838;
        }

        .cancel-btn {
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            padding: 12px 20px;
            border-radius: 8px;
        }

        .cancel-btn:hover {
            background-color: #c82333;
        }

        .btn-group {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit User</h2>
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

        <label>Role:</label>
        <select name="role" required>
            <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
            <option value="User" <?= $user['role'] === 'User' ? 'selected' : '' ?>>User</option>
        </select>

        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>DOB:</label>
        <input type="date" name="dob" value="<?= htmlspecialchars($user['dob']) ?>" required>

        <label>Address:</label>
        <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>" required>

        <div class="btn-group">
            <button type="submit">Update User</button>
            <a href="admin_home.php" class="cancel-btn">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>
