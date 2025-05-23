
<?php
include('db.php');

$commentId = $_GET['comment_id'];
$sql = "UPDATE comments SET likes = likes + 1 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $commentId);
$stmt->execute();

$sql = "SELECT likes FROM comments WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $commentId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['likes' => $row['likes']]);
?>
