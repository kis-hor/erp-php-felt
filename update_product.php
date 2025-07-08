<?php
include 'config.php';

// Check if it's a GET request to fetch product details
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['productID'])) {
    $productId = mysqli_real_escape_string($conn, $_GET['productID']);

    $sql = "SELECT * FROM products WHERE ProductID = $productId";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        // Output the product details in a format suitable for the front-end
        echo '<input type="hidden" id="productID" value="' . $product['ProductID'] . '">';
        echo '<input type="text" id="productName" value="' . $product['ProductName'] . '">';
        echo '<input type="text" id="productSize" value="' . $product['ProductSize'] . '">';
        echo '<input type="text" id="productColor" value="' . $product['ProductColor'] . '">';
        echo '<input type="text" id="productWeight" value="' . $product['ProductWeight'] . '">';
        echo '<img id="productImagePreview" src="' . $product['ProductImage'] . '" alt="Product Image" />';
    } else {
        echo 'Product not found';
    }
}

// Check if it's a POST request to update product details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productId = mysqli_real_escape_string($conn, $_POST['productID']);
    $productName = mysqli_real_escape_string($conn, $_POST['productName']);
    $productSize = mysqli_real_escape_string($conn, $_POST['productSize']);
    $productColor = mysqli_real_escape_string($conn, $_POST['productColor']);
    $productWeight = mysqli_real_escape_string($conn, $_POST['productWeight']);

    // Check if a new image is uploaded
    if (!empty($_FILES['productImage']['name'])) {
        $target_dir = "";
        $target_file = $target_dir . basename($_FILES["productImage"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (move_uploaded_file($_FILES["productImage"]["tmp_name"], $target_file)) {
            // Update product details with the new image
            $sql = "UPDATE products SET ProductName='$productName', ProductSize='$productSize', 
                    ProductColor='$productColor', ProductWeight='$productWeight', 
                    ProductImage='$target_file' WHERE ProductID=$productId";
        } else {
            echo "Error uploading image";
            exit();
        }
    } else {
        // Update product details without changing the image
        $sql = "UPDATE products SET ProductName='$productName', ProductSize='$productSize', 
                ProductColor='$productColor', ProductWeight='$productWeight' WHERE ProductID=$productId";
    }

    if (mysqli_query($conn, $sql)) {
        echo "Product updated successfully!";
    } else {
        echo "Error updating product: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
