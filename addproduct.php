
<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$response = array('success' => false, 'message' => '');

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $item = mysqli_real_escape_string($conn, $_POST['item']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $details = mysqli_real_escape_string($conn, $_POST['details']);
    
    // Check if the uploads directory exists, if not create it
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Handle photo upload
    $photoPath = "";
    if (isset($_FILES['photos']) && $_FILES['photos']['error'][0] == 0) {
        $fileName = $_FILES['photos']['name'][0];
        $fileType = $_FILES['photos']['type'][0];
        $fileSize = $_FILES['photos']['size'][0];
        $fileTmpName = $_FILES['photos']['tmp_name'][0];
        
        // Generate a unique filename to avoid overwriting
        $uniqueName = time() . '_' . $username . '_' . $fileName;
        $uploadPath = $uploadDir . $uniqueName;
        
        // Check if file is an image
        $validExtensions = array('jpg', 'jpeg', 'png', 'gif');
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (in_array($fileExtension, $validExtensions)) {
            // Move the uploaded file
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                $photoPath = $uploadPath;
            } else {
                $response['message'] = "Failed to upload image. Error: " . error_get_last()['message'];
                echo json_encode($response);
                exit();
            }
        } else {
            $response['message'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
            echo json_encode($response);
            exit();
        }
    } else {
        // No image uploaded or error occurred
        $photoPath = "placeholder.jpg";
    }
    
    // Insert product into database
    $sql = "INSERT INTO products (username, item, price, address, details, photo, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsss", $username, $item, $price, $address, $details, $photoPath);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Product added successfully!";
        
        // Redirect back to main page
        header("Location: home.php");
        exit();
    } else {
        $response['message'] = "Error: " . $stmt->error;
        echo json_encode($response);
        exit();
    }
    
    $stmt->close();
} else {
    // Display the form
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function addPhotoInput() {
            let container = document.getElementById("photo-container");
            let newInput = document.createElement("input");
            newInput.type = "file";
            newInput.name = "photos[]"; 
            newInput.accept = "image/*";
            container.appendChild(newInput);
        }
    </script>
</head>
<body>

<div class="container">
    <h2>Add Product</h2>
    <form action="addproduct.php" method="POST" enctype="multipart/form-data">
        <label for="item">Item Name:</label>
        <input type="text" id="item" name="item" required>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" required>

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" required>

        <label for="details">More Details:</label>
        <textarea id="details" name="details" rows="4" required></textarea>

        <label>Upload Photos:</label>
        <div id="photo-container">
            <input type="file" name="photos[]" accept="image/*" required>
        </div>
        <button type="button" onclick="addPhotoInput()">+ Add More Photos</button>

        <button type="submit">Post Product</button>
    </form>
</div>

</body>
</html>
