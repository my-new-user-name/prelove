<?php
include('db.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $productId = $_POST['product_id'];
    $username = $_POST['username'];

    // Update reports count
    $sql = "UPDATE products SET reports = reports + 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();

    // Check if the product has reached 3 reports
    $checkSql = "SELECT reports FROM products WHERE id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['reports'] >= 3) {
        // Delete the user
        $deleteUser = "DELETE FROM users WHERE username = ?";
        $stmt = $conn->prepare($deleteUser);
        $stmt->bind_param("s", $username);
        $stmt->execute();

        // Delete all products of the user
        $deleteProducts = "DELETE FROM products WHERE username = ?";
        $stmt = $conn->prepare($deleteProducts);
        $stmt->bind_param("s", $username);
        $stmt->execute();

        echo "User and their products have been removed due to excessive reports.";
    } else {
        echo "Product reported. If this product reaches 3 reports, the user will be deleted.";
    }
}
?>