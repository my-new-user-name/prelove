
<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$loggedInUser = $_SESSION['username'];

// Ensure `viewedUser` is set correctly
if (!isset($_GET['user']) || empty(trim($_GET['user']))) {
    die("Error: No user specified.");
}

$viewedUser = trim($_GET['user']);

echo "<pre>Debugging Info:";
echo "\nLogged-in User: " . htmlspecialchars($loggedInUser);
echo "\nViewed User (from URL): " . htmlspecialchars($viewedUser);
echo "</pre>";

// Fetch user details securely (supports email and username)
$sql = "SELECT username, email, first_name, last_name, address, barangay, city, phone, profile_picture, valid_id 
        FROM users WHERE BINARY (username = ? OR email = ?) LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $viewedUser, $viewedUser);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found in database.");
}

$user = $result->fetch_assoc();

// Check follow status
$followCheckSQL = "SELECT * FROM follows WHERE follower = ? AND following = ?";
$followStmt = $conn->prepare($followCheckSQL);
$followStmt->bind_param("ss", $loggedInUser, $user['username']);
$followStmt->execute();
$isFollowing = $followStmt->get_result()->num_rows > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?>'s Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2><?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?>'s Profile</h2>
    <div class="profile-card">
        <img src="<?php echo !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'default-avatar.png'; ?>" class="profile-pic" alt="Profile Picture">
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
        <p><strong>Barangay:</strong> <?php echo htmlspecialchars($user['barangay']); ?></p>
        <p><strong>City:</strong> <?php echo htmlspecialchars($user['city']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>

        <!-- Valid ID -->
        <p><strong>Valid ID:</strong> 
            <?php if (!empty($user['valid_id'])): ?>
                <a href="<?php echo htmlspecialchars($user['valid_id']); ?>" target="_blank">View Valid ID</a>
            <?php else: ?>
                Not uploaded
            <?php endif; ?>
        </p>
    </div>

    <!-- Follow Button -->
    <?php if ($loggedInUser !== $user['username']): ?>
        <form id="followForm">
            <input type="hidden" id="followedUser" value="<?php echo htmlspecialchars($user['username']); ?>">
            <button type="button" id="followBtn">
                <?php echo $isFollowing ? "Unfollow" : "Follow"; ?>
            </button>
        </form>
    <?php endif; ?>

    <p><a href="home.php">Back to Home</a></p>
</div>

<!-- Chat Box -->
<div class="chat-box" id="chat-box" style="display: none;">
    <div class="chat-box-header">
        <h2 id="chat-user-name"><?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></h2>
        <a href="view_profile.php?user=<?php echo urlencode($user['username']); ?>">View Profile</a>
        <button class="close-btn" onclick="closeChat()">✖</button>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    $(document).ready(function () {
        $("#followBtn").click(function () {
            var followedUser = $("#followedUser").val();
            $.post("follow_action.php", { followedUser: followedUser }, function (response) {
                $("#followBtn").text(response);
            });
        });
    });

    function closeChat() {
        document.getElementById("chat-box").style.display = "none";
    }
</script>
</body>
</html>
