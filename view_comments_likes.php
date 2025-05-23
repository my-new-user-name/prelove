<?php
session_start();
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php"); // Redirect non-admin users to home
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Comments and Likes</title>
    <link href="style_comments_likes.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
</head>
<body>

<div class="header">
   <a href="admin_dashboard.php" class="btn"><i class='bx bx-arrow-back'></i></a>
</div>



<div class="containers">
        <h1>Comments and Likes</h1>
        <table border="1">
            <tr>
                <th>Photo</th>
                <th>Item</th>
                <th>Price</th>
                <th>Address</th>
                <th>Details</th>
                <th>Posted By</th>
                <th>Likes</th>
                <th>Comments</th>
                <th>Created At</th>
            </tr>
            
            <?php
            $sql = "SELECT p.id, p.item, p.price, p.address, p.details, p.photo, p.likes, p.created_at, u.username 
                    FROM products p 
                    JOIN users u ON p.username = u.username 
                    ORDER BY p.created_at DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $photoPath = htmlspecialchars($row['photo']);
                    $productId = $row['id'];
                    echo "<tr>";
                    echo "<td><img src='$photoPath' alt='Product Image' width='50' height='50'></td>";
                    echo "<td>" . htmlspecialchars($row['item']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['price']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['details']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                    echo "<td>" . $row['likes'] . "</td>";
                    echo "<td><a href='view_product_comments.php?product_id=" . $productId . "' class='view-comments-btn'>View Comments</a></td>";

                    echo "<td>" . $row['created_at'] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9'>No products found.</td></tr>";
            }
            ?>
        </table>
</div>

</body>
</html>