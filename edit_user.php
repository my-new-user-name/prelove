<?php
session_start();
include('db.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $sql = "UPDATE users SET first_name=?, last_name=?, phone=?, address=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $first_name, $last_name, $phone, $address, $id);

    if ($stmt->execute()) {
        header("Location: manage_users.php");
        exit();
    } else {
        echo "Error updating user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-content">
            <h1>Edit User</h1>
            <form method="POST">
                <label>First Name:</label>
                <input type="text" name="first_name" value="<?php echo $user['first_name']; ?>" required>

                <label>Last Name:</label>
                <input type="text" name="last_name" value="<?php echo $user['last_name']; ?>" required>

                <label>Phone:</label>
                <input type="text" name="phone" value="<?php echo $user['phone']; ?>" required>

                <label>Address:</label>
                <input type="text" name="address" value="<?php echo $user['address']; ?>" required>

                <button type="submit">Update User</button>
            </form>
        </div>
    </div>

    <style>
        form {
            max-width: 400px;
            margin: auto;
            display: flex;
            flex-direction: column;
        }

        input {
            padding: 8px;
            margin: 10px 0;
        }

        button {
            padding: 10px;
            background: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</body>
</html>