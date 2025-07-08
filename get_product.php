<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productId = mysqli_real_escape_string($conn, $_POST['id']);

    $sql = "SELECT * FROM products WHERE ProductID = $productId";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $product = mysqli_fetch_assoc($result);
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Error fetching product details']);
    }

    mysqli_close($conn);
}
