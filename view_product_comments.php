
<?php
session_start();
include('db.php');

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Check if the product_id is set in the URL
if (!isset($_GET['product_id'])) {
    header("Location: upload_history.php");
    exit();
}

$product_id = intval($_GET['product_id']);

// Fetch product details (Photo, Item, Price, Address, Details, Uploaded On)
// Modified to ensure we're viewing the correct product
$product_sql = "SELECT item, price, address, details, photo, created_at, username FROM products WHERE id = ?";
$stmt = $conn->prepare($product_sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_result = $stmt->get_result();

// Check if product exists
if ($product_result->num_rows === 0) {
    // Product not found, redirect to history page
    header("Location: upload_history.php");
    exit();
}

$product = $product_result->fetch_assoc();
$stmt->close();

// Fetch comments for the product
$comments_sql = "SELECT u.username, c.comment, c.created_at 
                 FROM comments c 
                 JOIN users u ON c.user_id = u.id 
                 WHERE c.product_id = ? 
                 ORDER BY c.created_at DESC";
$stmt = $conn->prepare($comments_sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$comments_result = $stmt->get_result();
$stmt->close();

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $comment = htmlspecialchars($_POST['comment']);
    $user_id = $_SESSION['user_id']; // Assuming the user_id is stored in session

    if (!empty($comment)) {
        $insert_comment_sql = "INSERT INTO comments (product_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($insert_comment_sql);
        $stmt->bind_param("iis", $product_id, $user_id, $comment);
        $stmt->execute();
        $stmt->close();
        
        // Redirect back to the same product page after comment is posted
        header("Location: view_product_comments.php?product_id=" . $product_id);
        exit();
    }
}

// Determine if this product belongs to the current user
$is_owner = ($product['username'] === $username);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments for <?php echo htmlspecialchars($product['item']); ?></title>
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
</head>
<body>
    <div class="container">
        <div class="heads">
            <?php if ($is_owner): ?>
            <!-- If it's the user's own product, direct back to their uploads -->
            <a href="product_history_uploaded.php" class="btn"><i class='bx bx-arrow-back'></i></a>
            <?php else: ?>
            <!-- If it's someone else's product, go back to main listing -->
            <a href="index.php" class="btn"><i class='bx bx-arrow-back'></i></a>
            <?php endif; ?>
            <h1><?php echo htmlspecialchars($product['item']); ?>'s Post</h1>
        </div>

        <!-- Product Details Section -->
        <div class="product-details">
            <p><strong>Item:</strong> <?php echo htmlspecialchars($product['item']); ?></p>
            <p><strong>Price:</strong> ₱<?php echo number_format($product['price'], 2); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($product['address']); ?></p>
            <p><strong>Details:</strong> <?php echo htmlspecialchars($product['details']); ?></p>
            <?php if (!empty($product['photo'])): ?>
                <img src="<?php echo htmlspecialchars($product['photo']); ?>" width="80" height="80" alt="Product Image">
            <?php else: ?>
                <img src="placeholder.jpg" width="80" height="80" alt="No image available">
            <?php endif; ?>
            <p><strong>Posted by:</strong> <?php echo htmlspecialchars($product['username']); ?></p>
        </div>

        <!-- Comments Section for this Product -->
        <div class="comments-section">
            <h2>Comments</h2>
            <?php
            if ($comments_result->num_rows > 0) {
                while ($row = $comments_result->fetch_assoc()) {
                    // Format the comment date
                    $comment_date = date("F j, Y, g:i a", strtotime($row['created_at']));
                    echo "<div class='comment-wrapper'>";
                        echo "<div class='comment-box'>";
                            echo "<p><strong>" . htmlspecialchars($row['username']) . "</strong></p>";
                            echo "<div class='comment-text'>" . htmlspecialchars($row['comment']) . "</div>";
                        echo "</div>"; // end comment-box
                        echo "<div class='comment-time'>Posted at: " . $comment_date . "</div>";
                    echo "</div><br>";
                }
            } else {
                echo "<p>No comments found for this product.</p>";
            }
            ?>

            <!-- Comment Form -->
            <form method="POST" action="" id="chat-form">
                <textarea name="comment" placeholder="Write your comment..." required></textarea><br>
                <button type="submit"><i class='bx bxs-send'></i></button>
            </form>
        </div>

    </div>

<style>
    .container {
        max-width: 600px;
        margin: 30px auto;
        padding: 20px;
        font-family: Arial, sans-serif;
        background-color: #fdfdfd;
        border-radius: 12px;
        box-shadow: 0 0 12px rgba(0,0,0,0.1);
    }

    h2 {
        text-align: center;
        color: #333;
    }

    .heads {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }

    .heads .btn {
        position: absolute;
        left: 0;
        padding: 10px 18px;
        background-color: #fff;
        color: black;
        text-decoration: none;
        border-radius: 6px;
        font-size: 19px;
        transition: background-color 0.3s;
    }

    .heads .btn:hover {
        background-color: #fff;
    }

    .heads h1 {
        margin: 0;
        font-size: 24px;
        color: #333;
    }

    .product-details {
        background-color: #f0f8ff;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 30px;
    }

    .product-details img {
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .product-details p {
        margin: 6px 0;
    }

    .comments-section {
        background-color: #fff;
        padding: 15px;
        border-radius: 10px;
        border: 1px solid #ddd;
    }

    .comment-box {
        background-color: #f0f2f5;
        padding: 10px 15px;
        border-radius: 18px;
        margin-bottom: 5px;
        width: fit-content;
        max-width: 80%;
    }

    .comment-text {
        font-size: 14px;
        color: #050505;
    }

    .comment-time {
        font-size: 12px;
        color: #65676b;
        margin-left: 15px;
        margin-top: 5px;
        text-decoration: none;
    }

/* Chat Form */
#chat-form {
    display: flex;
    padding: 12px 16px;
    background-color: #f9f9f9;
    border-top: 1px solid #ddd;
}

#chat-form input[type="text"] {
    flex: 1;
    padding: 10px 22px;
    border-radius: 40px;
    border: 1px solid #ccc;
    background-color: #fff;
    color: #050505;
    font-size: 14px;
    outline: none;
    margin-left: 0;
}

#chat-form button {
    padding: 5px 20px;
    background-color: #0084ff;
    color: #fff;
    border: none;
    border-radius: 20px;
    font-weight: bold;
    font-size: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: right;
    margin-left: 12px;
}

#chat-form button:hover {
    background-color: #006fe0;
}

#chat-form button i {
    font-size: 20px;
}

</style>

</body>
</html>
