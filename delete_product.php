<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productId = $_POST['id'];

    // Delete product from the database
    $sql = "DELETE FROM products WHERE ProductID = $productId";

    if (mysqli_query($conn, $sql)) {
        echo "Product deleted successfully!";
    } else {
        echo "Error deleting product: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
