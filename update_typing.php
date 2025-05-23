
<?php
include('db.php');

$sender = $_POST['sender'];
$receiver = $_POST['receiver'];
$is_typing = $_POST['is_typing'] === 'true' ? 1 : 0;

$stmt = $conn->prepare("SELECT id FROM typing_status WHERE sender = ? AND receiver = ?");
$stmt->bind_param("ss", $sender, $receiver);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE typing_status SET is_typing = ? WHERE sender = ? AND receiver = ?");
    $stmt->bind_param("iss", $is_typing, $sender, $receiver);
} else {
    $stmt = $conn->prepare("INSERT INTO typing_status (sender, receiver, is_typing) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $sender, $receiver, $is_typing);
}
$stmt->execute();
?>
