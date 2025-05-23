
<?php
include('db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in to post a comment.");
}

if (isset($_POST['product_id'], $_POST['comment'])) {
    $productId = intval($_POST['product_id']);
    $userId = $_SESSION['user_id'];
    $comment = trim($_POST['comment']);
    $parentId = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

    if (!empty($comment)) {
        $query = "INSERT INTO comments (product_id, user_id, comment, parent_id, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            die("SQL Prepare Error: " . $conn->error);
        }

        $stmt->bind_param("iisi", $productId, $userId, $comment, $parentId);

        if ($stmt->execute()) {
            echo "success";
        } else {
            die("Error executing query: " . $stmt->error);
        }
    } else {
        die("Error: Comment cannot be empty.");
    }
} else {
    die("Error: Missing parameters.");
}
?>
