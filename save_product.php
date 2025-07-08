<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Database connection
    include "config.php";

    // Get form data
    $productName = $_POST['productName'];
    $productSize = $_POST['productSize'];
    $productColor = $_POST['productColor'];
    $productWeight = $_POST['productWeight'];

    // Handle image upload
    $target_dir = "uploads/";
    $imageFileName = basename($_FILES["productImage"]["name"]);
    $target_file = $target_dir . $imageFileName;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $uploadOk = 1;

    // Check if image file is a valid image
    $check = getimagesize($_FILES["productImage"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size (limit: 2MB)
    if ($_FILES["productImage"]["size"] > 2000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Try to upload file and insert data
    try {
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["productImage"]["tmp_name"], $target_file)) {
                // Prepare SQL statement
                $sql = "INSERT INTO products (ProductName, ProductSize, ProductColor, ProductWeight, ProductImage) 
                        VALUES (?, ?, ?, ?, ?)";

                // Prepare statement
                $stmt = $conn->prepare($sql);

                if ($stmt === false) {
                    throw new Exception("Prepare statement failed: " . $conn->error);
                }

                // Bind parameters
                $stmt->bind_param("sssss", $productName, $productSize, $productColor, $productWeight, $imageFileName);

                // Execute statement
                if ($stmt->execute()) {
                    echo "Product has been successfully added.";
                } else {
                    throw new Exception("Execute statement failed: " . $stmt->error);
                }
            } else {
                throw new Exception("Sorry, there was an error uploading your file.");
            }
        } else {
            throw new Exception("File upload failed due to validation errors.");
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
