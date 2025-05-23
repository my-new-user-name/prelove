<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch user details including password
$stmt = $conn->prepare("SELECT first_name, last_name, email, birthday, phone, address, province, city, barangay, profile_picture, password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        // Handle password change
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (!password_verify($current_password, $user['password'])) {
            $message = "Current password is incorrect.";
        } elseif ($new_password !== $confirm_password) {
            $message = "New passwords do not match.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->bind_param("ss", $hashed_password, $username);

            if ($stmt->execute()) {
                $message = "Password updated successfully!";
            } else {
                $message = "Error updating password.";
            }
        }
    } else {
        // Handle profile update
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $birthday = $_POST['birthday'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $province = $_POST['province'];
        $city = $_POST['city'];
        $barangay = $_POST['barangay'];

        if (!empty($_FILES["profile_picture"]["name"])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $allowed_types = ["jpg", "jpeg", "png", "gif"];
            if (in_array($imageFileType, $allowed_types)) {
                move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
                $profile_picture = $target_file;

                $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE username = ?");
                $stmt->bind_param("ss", $profile_picture, $username);
                $stmt->execute();
            }
        }

        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, birthday = ?, phone = ?, address = ?, province = ?, city = ?, barangay = ? WHERE username = ?");
        $stmt->bind_param("ssssssssss", $first_name, $last_name, $email, $birthday, $phone, $address, $province, $city, $barangay, $username);

        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            $stmt = $conn->prepare("SELECT first_name, last_name, email, birthday, phone, address, province, city, barangay, profile_picture, password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error = "Profile update failed!";
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="style_main.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
</head>
<body>

<div class="header">
    <p><a href="userprofile.php"><i class='bx bx-arrow-back'></i></a></p>
</div>

<div class="container">
    <h1>Edit Profile</h1>
    <?php if (isset($success)): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? 'default.png'); ?>" width="100" height="100" alt="Profile Picture">

        <label for="profile_picture">Change Profile Picture:</label>
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">

        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" disabled>

        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" placeholder="First Name:" required>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" placeholder="Last Name:" required>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Email:" required>
        <input type="date" name="birthday" value="<?php echo htmlspecialchars($user['birthday']); ?>" required>
        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="Phone Number:" required>
        <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" placeholder="Address:" required>
        <input type="text" name="province" value="<?php echo htmlspecialchars($user['province']); ?>" placeholder="Province:" required>
        <input type="text" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" placeholder="City:" required>
        <input type="text" name="barangay" value="<?php echo htmlspecialchars($user['barangay']); ?>" placeholder="Barangay:" required>

        <button type="submit">Update Profile</button>
        <button type="button" onclick="openChangePasswordModal()">Change Password</button>
    </form>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="modal">
    <div class="form-container">
        <span class="close-button" onclick="closeChangePasswordModal()">&times;</span>
        <h2>Change Password</h2>
        <?php if (!empty($message)): ?>
            <p class="message" style="color: red;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="action" value="change_password">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required><br>

            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Update Password</button>
        </form>
    </div>
</div>

<!-- Styling -->
<style>
.container {
    background: rgb(219, 224, 236);
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    width: 100%;
    margin: 60px auto;
}

form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

h1 {
    text-align: center;
    color: #333;
    font-size: 28px;
    margin-bottom: 20px;
    grid-column: span 2; /* Ensures it spans both columns in the form */
}


img {
    grid-column: span 2;
    margin: 0 auto 20px auto;
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid rgb(152, 194, 221);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

label {
    grid-column: span 2;
    font-weight: bold;
}

input[type="text"],
input[type="email"],
input[type="date"],
input[type="tel"],
input[type="password"] {
    background-color: rgb(165, 189, 216);
    padding: 10px;
    border: 1px solid rgb(165, 189, 216);
    border-radius: 5px;
    width: 90%;
}

input[type="file"] {
    width: 95%;
    padding: 10px;
    border: 1px solid rgb(165, 189, 216);
    border-radius: 5px;
    background-color: rgb(165, 189, 216);
    color: #000;
    font-family: inherit;
    font-size: 14px;
    cursor: pointer;
    grid-column: span 2;
}


input[disabled] {
    background-color: #ccc;
}

button {
    background-color: rgb(97, 156, 224);
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-align: center;
    width: 48%;
    margin-top: 10px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: rgb(181, 199, 219);
}


.success, .error {
    text-align: center;
    font-weight: bold;
}

.success { color: green; }
.error { color: red; }

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
}

.form-container {
    background: rgb(219, 224, 236);
    padding: 20px;
    border-radius: 10px;
    width: 100%;
    max-width: 500px;
    position: relative;
}

.modal form {
    display: block; /* or remove display property */
    width: 100%;
}

.modal input[type="password"] {
    background-color: rgb(165, 189, 216);
    width: 90%;
    padding: 10px;
    border: 1px solid rgb(165, 189, 216);
    border-radius: 5px;
    margin-bottom: 10px;
    box-sizing: border-box;
}

.close-button {
    position: absolute;
    top: 10px; right: 15px;
    font-size: 30px;
    cursor: pointer;
    color: black;
}

.modal button {
    background-color: rgb(97, 156, 224);
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-align: center;
    width: 48%;
    margin-top: 10px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: rgb(181, 199, 219);
}
</style>

<script>
function openChangePasswordModal() {
    document.getElementById('changePasswordModal').style.display = 'flex';
}
function closeChangePasswordModal() {
    document.getElementById('changePasswordModal').style.display = 'none';
}
window.addEventListener('click', function(event) {
    const modal = document.getElementById('changePasswordModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});
</script>

</body>
</html>
