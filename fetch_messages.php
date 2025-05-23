
<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender = mysqli_real_escape_string($conn, $_POST['sender']);
    $receiver = mysqli_real_escape_string($conn, $_POST['receiver']);

    $sql = "SELECT * FROM messages 
            WHERE (sender = ? AND receiver = ?) 
               OR (sender = ? AND receiver = ?)
            ORDER BY timestamp ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $sender, $receiver, $receiver, $sender);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $isSender = $row['sender'] === $sender;
        echo "<div style='text-align:" . ($isSender ? "right" : "left") . "; margin-bottom:15px;'>";

        // Message bubble
        echo "<div style='display:inline-block; background:" . ($isSender ? "#DCF8C6" : "#eee") . "; padding:10px; border-radius:10px; max-width:60%;'>";
        echo htmlspecialchars($row['message']);
        echo "</div>";

        // Centered timestamp
        echo "<div style='text-align:center; font-size:12px; color:gray; margin-top:2px;'>" . date('h:i A', strtotime($row['timestamp'])) . "</div>";

        echo "</div>";
    }
}
?>
