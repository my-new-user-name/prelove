<?php
include('db.php');
session_start();

if (!isset($_SESSION['username'])) {
    echo "error";
    exit();
}

$sender = $_SESSION['username'];
$product_id = $_POST['product_id'];

// Fetch product owner
$sql = "SELECT user_id FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row) {
    $receiver_id = $row['user_id']; // Ensure this field exists in the `products` table
    $message = "$sender liked your product.";

    // Check if receiver exists in the users table
    $user_query = "SELECT id FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("i", $receiver_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows > 0) {
        // Insert notification with user_id
        $notif_sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $notif_stmt = $conn->prepare($notif_sql);
        $notif_stmt->bind_param("is", $receiver_id, $message);
        $notif_stmt->execute();
    }
}

// Update likes count
$update_sql = "UPDATE products SET likes = likes + 1 WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("i", $product_id);
$update_stmt->execute();

echo "success";
?>