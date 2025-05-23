<?php
session_start();
include('db.php');

// Ensure user is logged in
if (!isset($_SESSION['username'])) {  
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$message = "";

// Handle product deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Delete product from database
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND username = ?");
    $stmt->bind_param("is", $delete_id, $username);

    if ($stmt->execute()) {
        $message = "Product deleted successfully!";
    } else {
        $message = "Failed to delete product.";
    }
    $stmt->close();
}

// Fetch user-uploaded products
$sql = "SELECT id, item, price, address, details, photo, created_at FROM products WHERE username = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload History</title>
    <link href="style_main.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
</head>
<body>

<div class="header">
    <a href="home.php"><i class='bx bx-arrow-back'></i></a>
</div>

<div class="container">
    <h1>Upload History</h1>

    <?php if (!empty($message)): ?>
        <p class="success"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <table border="1">
        <tr>
            <th>Photo</th>
            <th>Item</th>
            <th>Price</th>
            <th>Address</th>
            <th>Details</th>
            <th>Uploaded On</th>
            <th>Actions</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><img src="<?php echo htmlspecialchars($row['photo']); ?>" width="80" height="80" alt="Product Image"></td>
                    <td><?php echo htmlspecialchars($row['item']); ?></td>
                    <td>₱<?php echo number_format($row['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td><?php echo htmlspecialchars($row['details']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td>
                        <!-- View Comments Link (Client can only view comments for their own products) -->
                        <a href="view_product_comments.php?product_id=<?php echo $row['id']; ?>"><button class="view-btn">view comments</button></a><br>
                        <br>
                        <!-- Product Deletion Form -->
                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this product?');" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No products uploaded yet.</td></tr>
        <?php endif; ?>
    </table>
</div>

<style>
.container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    text-align: center;
}

h1 {
    color: #333;
    margin-bottom: 20px;
}

.success {
    color: #28a745;
    font-weight: bold;
    margin-bottom: 15px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table, th, td {
    border: 1px solid #ddd;
}

th, td {
    padding: 10px;
    text-align: center;
}

th {
    background-color:rgb(113, 200, 235);
    color: black;
}

td img {
    border-radius: 5px;
    object-fit: cover;
}

.view-btn {
    background-color:rgb(91, 158, 245);
    color: white;
    border: none;
    padding: 5px 7px;
    cursor: pointer;
    border-radius: 5px;
    transition: background 0.3s;
}

.view-btn:hover {
    background-color: rgb(122, 163, 216);
}

.delete-btn {
    background-color: #dc3545;
    height: 40px;
    color: white;
    border: none;
    padding: 5px 20px;
    cursor: pointer;
    border-radius: 5px;
    transition: background 0.3s;
}

.delete-btn:hover {
    background-color: #c82333;
}
</style>

</body>
</html>