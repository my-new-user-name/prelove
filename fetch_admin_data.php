
<?php
include('db.php');

$type = $_GET['type'];

if ($type == 'users') {
    $result = $conn->query("SELECT COUNT(*) AS total FROM users");
    $row = $result->fetch_assoc();
    echo $row['total'];
} elseif ($type == 'products') {
    $result = $conn->query("SELECT username, item, price, created_at AS date FROM products ORDER BY created_at DESC");
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}
?>
