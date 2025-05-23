<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$message = "";

// Fetch user details, including valid_id
$sql = "SELECT username, email, first_name, last_name, address, barangay, city, phone, profile_picture, valid_id, password FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Query Error: " . $conn->error);
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="style_user.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
</head>
<body>

<div class="header">
    <p><a href="home.php"><i class='bx bx-arrow-back'></i></a></p>
</div>

<div class="container">
    <?php if ($user): ?>
        <div class="profile-header">
    <div class="profile-info-top">
        <img src="<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'default-avatar.png'; ?>" class="profile-pic" alt="Profile Picture">
        
        <div class="profile-details-right">
            <div class="profile-name">
                <h3><?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></h3>
            </div>

            <div class="profile-card">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                <p><strong>Barangay:</strong> <?php echo htmlspecialchars($user['barangay']); ?></p>
                <p><strong>City:</strong> <?php echo htmlspecialchars($user['city']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>

                <p><strong>Valid ID:</strong> 
                    <?php if (!empty($user['valid_id'])): ?>
                        <a href="<?php echo htmlspecialchars($user['valid_id']); ?>" target="_blank">View Valid ID</a>
                    <?php else: ?>
                        Not uploaded
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="account-info">
      <div class="welcome">
        <h2><i class='bx bx-user-circle'></i></h2>
      </div>

    <!-- Add Product Button -->
      <div class="add-product">
        <button class="btn" onclick="openModal()">Post Item Here</button>
      </div>
</div>



        <!-- Add Product Modal -->
<div id="addProductModal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="closeModal()">&times;</span>
        <h2>Create a Post</h2>

        <form action="addproduct.php" method="POST" enctype="multipart/form-data">

        <div class="form-group">
             
            <input type="text" id="item" name="item" placeholder="Item" required>
            <input type="number" id="price" name="price" placeholder="Price" required>
            <input type="text" id="address" name="address" placeholder="Address" required>
            <textarea id="details" name="details" rows="3" placeholder="Details" required></textarea>
        </div>

        <label>Upload Photos:</label>
        <div id="photo-container">
            <button type="button" class="file-button" onclick="document.getElementById('file-upload').click();">
            <i class='bx bx-image'></i>
            </button>
            <input type="file" id="file-upload" class="file-input" name="photos[]" accept="image/*">
            <button type="button" class="file-button" onclick="addPhotoInput()"><i class='bx bx-plus'></i></button>  
        </div>

        <button type="submit" class="post-button">Post</button>
        </form>
    </div>
</div>

        <div class="profile-actions">
            <p><a href="edit_profile.php"><i class='bx bxs-edit'></i> Edit your profile</a></p>
            <p><a href="product_history_uploaded.php"><i class='bx bx-history'></i> Product history upload</a></p>
        </div>            

    <?php else: ?>
        <p>Error: User not found.</p>
    <?php endif; ?>
</div>

<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f0f0f0;
    margin: 0;
    padding: 0;
}

.header {
    width: 98%;
    height: 40px;
    max-width: 1475px;
    display: flex;
    align-items: center;
    padding: 10px 20px;
    background-color:rgb(83, 167, 245); /* Light gray background */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.header p {
    margin: 0;
}

.header a {
    text-decoration: none;
    color: #fff; /* Dark gray */
    font-size: 24px;
    transition: color 0.3s ease;
}

.header a:hover {
    color: rgb(10, 74, 134); /* Blue on hover */
}

/* Outer container */
.profile-header {
    border: 2px solid rgb(236, 245, 253);
    border-radius: 55px;
    padding: 20px;
    background-color: rgb(236, 245, 253);
    max-width: 1700px;
    margin-bottom: 20px;
    margin-top: 20px;
}

/* Grid layout: image left, details right */
.profile-info-top {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 20px;
    align-items: flex-start;
}

/* Profile picture */
.profile-pic {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgb(164, 210, 250);
}

/* Right side container (name + card) */
.profile-details-right {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Name */
.profile-name h3 {
    margin: 0;
    font-size: 1.8em;
    color: #333;
}

/* Card inside right column */
.profile-card {
    margin-top: 10px;
}

.profile-card p {
    margin: 6px 0;
    font-size: 1em;
    color: #444;
}

.profile-card strong {
    color: #222;
}

/* Link */
.profile-card a {
    color: #007bff;
    text-decoration: none;
}

.profile-card a:hover {
    text-decoration: underline;
}

/* Account info container */
.account-info {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 1px;
    background-color: #aae4ff; /* Light gray background */
    border-radius: 40px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 99%;
    max-width: 2000px; /* Adjust width as needed */
    margin: 20px auto; /* Center it */
    margin-left: 0;
}

/* Welcome section */
.welcome {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* User circle icon */
.welcome i {
    font-size: 40px; /* Adjust size */
    color: #000; /* Default color */
    transition: color 0.3s ease, transform 0.2s ease;
    margin-left: 10px;
}

.welcome i:hover {
    color: #333; /* Blue on hover */
    transform: scale(1.1); /* Slight zoom effect */
}

/* Modal Background */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
}

/* Modal Content */
.modal-content {
    background: #dbe2ec;
    padding: 20px;
    width: 600px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    position: relative;
    margin-top: 100px;
    margin-left: 400px;
}

/* Close Button */
.close-button {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 24px;
    cursor: pointer;
}

.modal h2 {
    text-align: center;
}

.modal label {
    margin-top: 10px;
}

/* Form Styling */
.form-group {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

input, textarea {
    width: 90%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

textarea {
    resize: none;
}

/* Upload Button */
#photo-container {
    margin-top: 10px;
}

.modal button i {
    font-size: 26px;
}

.file-input {
    display: none;
}

.file-button {
    display: inline-block;
    background:  rgb(142, 180, 216);
    color: #000;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 26px;
    border: none;
    text-align: center;
}

.file-button:hover {
    background: rgb(93, 140, 184);
}

.post-button {
    width: 100%;
    display: inline-block;
    background:  rgb(142, 180, 216);
    color: #000;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 20px;
    border: none;
    text-align: center;
    margin-top: 10px;
}

/* Add Product Button */
.add-product {
    margin-left: 10px; /* Pushes button to the right */
}

.add-product .btn {
    background-color: #729fbd;
    color: #2e2e2e;
    border: none;
    padding: 10px 320px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    border-radius: 20px;
    transition: background 0.3s ease, transform 0.2s ease;
    text-align: left; /* Aligns text to the left */
    width: 1200px; /* Adjust width to maintain alignment */
    display: flex;
    align-items: center;
    justify-content: flex-start; /* Push text to the left */
    padding-left: 20px; /* Adds left padding for spacing */
    margin-top: 4px;
}

.add-product .btn:hover {
    background-color: #91b0c5;
    transform: none;
}

.profile-actions {
    border: 2px solid  rgb(184, 221, 247);
    border-radius: 55px;
    padding: 20px;
    background-color:rgb(184, 221, 247);
    max-width: 1700px;
    margin: 20px auto;
}

.profile-actions p {
    margin: 12px 0;
    font-size: 1.1em;
}

.profile-actions a {
    text-decoration: none;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: color 0.3s ease;
}

.profile-actions a:hover {
    color: rgb(71, 176, 250);
}

.profile-actions i {
    font-size: 1.3em;
}

</style>

<script>
// Function to open the modal
function openModal() {
    document.getElementById('addProductModal').style.display = 'block';
}

// Function to close the modal
function closeModal() {
    document.getElementById('addProductModal').style.display = 'none';
}

// Optional: Close modal when clicking outside the modal content
window.onclick = function(event) {
    const modal = document.getElementById('addProductModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

// Add new file input dynamically
function addPhotoInput() {
    const container = document.getElementById('photo-container');
    const input = document.createElement('input');
    input.type = 'file';
    input.name = 'photos[]';
    input.accept = 'image/*';
    input.classList.add('file-input');
    container.insertBefore(input, container.lastElementChild); // Insert before the + button
}
</script>


</body>
</html>