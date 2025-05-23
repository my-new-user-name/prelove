<?php
session_start();
include('db.php');  // Ensure $conn is initialized properly

error_reporting(E_ALL);
ini_set('display_errors', 1);

$productId = null;

// Allow both GET and POST methods
if (isset($_GET['product_id'])) {
    $productId = intval($_GET['product_id']);
} elseif (isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);
} else {
    echo json_encode(["status" => "error", "message" => "Missing product_id"]);
    exit;
}

// Ensure product ID is valid
if ($productId <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid product_id"]);
    exit;
}

// Fetch top-level comments
$query = "SELECT c.id, c.comment, c.created_at, u.username 
          FROM comments c 
          JOIN users u ON c.user_id = u.id 
          WHERE c.product_id = ? AND c.parent_id IS NULL
          ORDER BY c.created_at ASC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "SQL Error: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
$commentMap = []; // Associative array for quick lookup of comments

while ($row = $result->fetch_assoc()) {
    $commentData = [
        "id" => $row['id'],
        "username" => $row['username'],
        "comment" => $row['comment'],
        "created_at" => $row['created_at'],
        "replies" => []
    ];
    
    $comments[] = $commentData;
    $commentMap[$row['id']] = &$comments[count($comments) - 1];  // Reference for replies
}

// Fetch replies
$query = "SELECT c.id, c.comment, c.created_at, u.username, c.parent_id
          FROM comments c 
          JOIN users u ON c.user_id = u.id 
          WHERE c.product_id = ? AND c.parent_id IS NOT NULL
          ORDER BY c.created_at ASC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "SQL Error: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if (isset($commentMap[$row['parent_id']])) {  
        // Append reply directly to the correct comment
        $commentMap[$row['parent_id']]['replies'][] = [
            "username" => $row['username'],
            "comment" => $row['comment'],
            "created_at" => $row['created_at']
        ];
    }
}

// Return the response
echo json_encode([
    "status" => "success",
    "data" => $comments
]);
?>