
<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender = mysqli_real_escape_string($conn, $_POST['sender']);
    $receiver = mysqli_real_escape_string($conn, $_POST['receiver']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    if (!empty($sender) && !empty($receiver) && !empty($message)) {
        $sql = "INSERT INTO messages (sender, receiver, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $sender, $receiver, $message);
        $stmt->execute();
        echo "Message sent";
    }
}
?>
