
<?php
session_start();
include('db.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validate input
if (!isset($_POST['product_id']) || !isset($_POST['comment'])) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

$productId = intval($_POST['product_id']);
$comment = trim($_POST['comment']);

if ($productId <= 0 || empty($comment)) {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit;
}

// Get user ID from session (assuming user is logged in)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

$userId = $_SESSION['user_id'];

// Insert comment into the database
$query = "INSERT INTO comments (product_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "SQL Error: " . $conn->error]);
    exit;
}

$stmt->bind_param("iis", $productId, $userId, $comment);
if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => "Failed to post comment"]);
    exit;
}

// Fetch the newly posted comment
$newCommentId = $stmt->insert_id;
$query = "SELECT c.id, c.comment, c.created_at, u.username 
          FROM comments c 
          JOIN users u ON c.user_id = u.id 
          WHERE c.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $newCommentId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Prepare HTML for the new comment
$html = "<div class='comment'>
            <strong>" . htmlspecialchars($row['username']) . "</strong>: 
            " . htmlspecialchars($row['comment']) . "<br>
            <small>" . $row['created_at'] . "</small>
         </div>";

// Return success response with HTML
echo json_encode([
    "status" => "success",
    "html" => $html
]);
?>
