<?php 
session_start();
include('db.php'); // Database connection file

// If already logged in, redirect to home page
if (isset($_SESSION['username'])) {
    header("Location: home.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $birthday = $_POST['birthday'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];
    $status = "pending"; // Default user status

    // Handle profile picture upload
    $profile_picture = null;
    if (!empty($_FILES["profile_picture"]["name"])) {
        $target_dir = "uploads/profile_pictures/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create directory if it doesn't exist
        }
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Allowed file types
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (in_array($imageFileType, $allowed_types)) {
            move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
            $profile_picture = $target_file;
        }
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "Username already exists";
    } else {
        // Insert new user into database
        $stmt = $conn->prepare("INSERT INTO users 
            (username, password, first_name, last_name, email, birthday, phone, address, province, city, barangay, profile_picture, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("sssssssssssss", $username, $hashedPassword, $first_name, $last_name, $email, $birthday, $phone, $address, $province, $city, $barangay, $profile_picture, $status);
        
        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            header("Location: home.php");
            exit();
        } else {
            $error = "Registration failed";
        }
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="style_log.css" rel="stylesheet">
    <script>
        function toggleRegisterButton() {
            const checkbox = document.getElementById('agree_terms');
            const registerButton = document.getElementById('register_button');
            registerButton.disabled = !checkbox.checked;
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>

            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="birthday">Birthday:</label>
            <input type="date" id="birthday" name="birthday" required>

            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" required>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required>

            <label for="province">Province:</label>
            <input type="text" id="province" name="province" required>

            <label for="city">City:</label>
            <input type="text" id="city" name="city" required>

            <label for="barangay">Barangay:</label>
            <input type="text" id="barangay" name="barangay" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="profile_picture">Profile Picture:</label>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/*">

            <label for="valid_id">Upload Valid ID:</label>
            <input type="file" id="valid_id" name="valid_id" accept="image/*,application/pdf" required>
    </div>

    <div class="container1">
        <h1>Register</h1>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <label>
                <input type="checkbox" id="agree_terms" onchange="toggleRegisterButton()">
                I agree to the <a href="terms.php" target="_blank">Privacy Policy & Terms of Service</a>
            </label><br>

            <!-- Dropdown message -->
            <div id="terms_message">
            

                <p><strong>1. Introduction</strong></p>
                <p>Welcome to Preloved items, an online platform designed for buying and selling preloved items.</p>

                <p><strong>2. Information Collection and Use</strong></p>
                <ul>
                    <li>Username</li>
                    <li>Full Name</li>
                    <li>Email Address</li>
                    <li>Phone Number</li>
                    <li>Address (Province, City, Barangay)</li>
                    <li>Birthday</li>
                    <li>Uploaded Valid ID</li>
                </ul>
                <p>This information is used to verify identities and facilitate transactions.</p>

                <p><strong>3. Data Protection</strong></p>
                <ul>
                    <li>Data is stored securely and is only accessible by authorized personnel.</li>
                    <li>We do not sell, trade, or rent user data.</li>
                    <li>Users can request data deletion or modification at any time.</li>
                </ul>

                <p><strong>4. User Responsibilities</strong></p>
                <ul>
                    <li>Users must provide accurate and truthful information.</li>
                    <li>Selling illegal, counterfeit, or prohibited items is forbidden.</li>
                    <li>Harassment, scams, and fraudulent activity will result in account suspension.</li>
                </ul>

                <p><strong>5. Transactions and Communication</strong></p>
                <p>All transactions are facilitated through our chat-based system.</p>

                <p><strong>6. Account Security</strong></p>
                <p>Users are responsible for maintaining the confidentiality of their login credentials.</p>

                <p><strong>7. Changes to Policy</strong></p>
                <p>We reserve the right to modify this policy at any time.</p>

                <p><strong>8. Contact Information</strong></p>
                <p>For questions, contact us at [Your Contact Information].</p>
            </div>

            <button type="submit" id="register_button" disabled>Register</button>
        </form>

        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </div>


<style>
   body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    background-image: url('uploads/background1.png'); /* Replace with your image path */
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

.container {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    max-width: 400px;
    width: 100%;
    position: relative;
}

.container1 {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    max-width: 400px;
    width: 100%;
    max-height: 595px;
    margin-left: 23px;
    margin-bottom: 0;
}

#terms_message {
    background: #f9f9f9;
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    max-height: 400px;
    overflow-y: auto;
}

h1 {
    text-align: center;
    color: #333;
}

form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3px;
}

label {
    font-weight: bold;
    color: #555;
}

input[type="text"],
input[type="email"],
input[type="date"],
input[type="tel"],
input[type="password"],
input[type="file"] {
    width: 80%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

input[type="file"] {
    padding: 5px;
}

input[type="checkbox" i] {
    background-color: initial;
    cursor: default;
    appearance: auto;
    box-sizing: border-box;
    margin: 3px 3px 3px 4px;
    padding: initial;
    border: initial;
}

button {
    background-color: #007bff;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-align: center;
    margin-top: 3px;
}

button:hover {
    background-color: #0056b3;
}

.error {
    color: red;
    text-align: center;
}

p {
    text-align: left;
    color: #000;
}

</style>
</body>
</html>

    <script>
        function toggleRegisterButton() {
            const checkbox = document.getElementById('agree_terms');
            const registerButton = document.getElementById('register_button');
            const messageBox = document.getElementById('terms_message');

            if (checkbox.checked) {
                registerButton.disabled = false;
                messageBox.classList.add("show"); // Show dropdown message
            } else {
                registerButton.disabled = true;
                messageBox.classList.remove("show"); // Hide dropdown message
            }
        }
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        toggleRegisterButton(); // Ensure button is disabled on page load

        document.getElementById("agree_terms").addEventListener("change", toggleRegisterButton);
    });

    function toggleRegisterButton() {
        const checkbox = document.getElementById('agree_terms');
        const registerButton = document.getElementById('register_button');
        const messageBox = document.getElementById('terms_message');

        registerButton.disabled = !checkbox.checked; // Enable if checked, disable otherwise
        
        if (checkbox.checked) {
            messageBox.style.display = "block"; // Show dropdown message
        } else {
            messageBox.style.display = "none"; // Hide dropdown message
        }
    }
</script>