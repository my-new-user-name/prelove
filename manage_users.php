<?php
session_start();
include('db.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all users with report counts
$sql = "SELECT u.id, u.username, u.email, u.status, 
               COUNT(p.reports) AS report_count
        FROM users u
        LEFT JOIN products p ON u.username = p.username
        GROUP BY u.id";

$result = $conn->query($sql); // Execute the query

if (!$result) {
    die("Query failed: " . $conn->error); // Error handling
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="style_users.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_products.php">Manage Products</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="admin-content">
            <h1>Manage Users</h1>
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Reports</th> <!-- New Column -->
                    <th>Actions</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo ucfirst($row['status']); ?></td>
                    <td><?php echo $row['report_count']; ?></td> <!-- Display Report Count -->
                    <td>
                        <?php if ($row['status'] == 'active') { ?>
                            <a href="toggle_user_status.php?id=<?php echo $row['id']; ?>&action=deactivate" style="color: red;">Deactivate</a>
                        <?php } else { ?>
                            <a href="toggle_user_status.php?id=<?php echo $row['id']; ?>&action=activate" style="color: green;">Activate</a>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>


</body>
</html>