
<?php
include('db.php');
session_start();

if (!isset($_SESSION['username'])) {
    echo "error";
    exit();
}

$receiver = $_SESSION['username'];

$sql = "UPDATE notifications SET is_read = 1 WHERE receiver = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $receiver);
$stmt->execute();

echo "success";
?>
