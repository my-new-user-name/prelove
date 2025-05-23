
<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$loggedInUser = $_SESSION['username'];
$viewedUser = isset($_GET['user']) ? mysqli_real_escape_string($conn, $_GET['user']) : '';

if (!$viewedUser) {
    die("User not found.");
}

// Fetch user details
$sql = "SELECT username, email, first_name, last_name, address, barangay, city, phone, profile_picture FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $viewedUser);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Check follow status
$followCheckSQL = "SELECT * FROM follows WHERE follower = ? AND following = ?";
$followStmt = $conn->prepare($followCheckSQL);
$followStmt->bind_param("ss", $loggedInUser, $viewedUser);
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
    <img src="<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'default-avatar.png'; ?>" class="profile-pic" alt="Profile Picture">
    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
    <p><strong>Barangay:</strong> <?php echo htmlspecialchars($user['barangay']); ?></p>
    <p><strong>City:</strong> <?php echo htmlspecialchars($user['city']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>

    <!-- Follow Button -->
    <?php if ($loggedInUser !== $viewedUser): ?>
        <form id="followForm">
            <input type="hidden" id="followedUser" value="<?php echo $viewedUser; ?>">
            <button type="button" id="followBtn">
                <?php echo $isFollowing ? "Unfollow" : "Follow"; ?>
            </button>
        </form>
    <?php endif; ?>

    <a href="home.php">Back to Home</a>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    $("#followBtn").click(function() {
        var followedUser = $("#followedUser").val();
        $.post("follow_action.php", { followedUser: followedUser }, function(response) {
            $("#followBtn").text(response);
        });
    });
</script>
</body>
</html>
