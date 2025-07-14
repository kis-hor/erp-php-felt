<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "config.php"; // Assumes MySQL connection in config.php

$upload_dir = __DIR__ . '/uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$po_number = $_POST['po_number'];
$customer_name = $_POST['customer_name'];
$salesperson_name = $_POST['salesperson_name'];
$order_date = $_POST['order_date'];
$remarks = $_POST['remarks'] ?? '';
$products = $_POST['products'] ?? [];
$product_photos = $_FILES['product_photos'] ?? [];

mysqli_begin_transaction($conn);
try {
    // Insert into sales_orders
    $insert_order_sql = "INSERT INTO sales_orders (PONumber, CustomerName, SalespersonName, OrderDate, Remarks, CreatedAt) 
                         VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $insert_order_sql);
    mysqli_stmt_bind_param($stmt, 'sssss', $po_number, $customer_name, $salesperson_name, $order_date, $remarks);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Insert into sales_orders failed: ' . mysqli_error($conn));
    }
    $sales_order_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // Insert products
    foreach ($products as $index => $product) {
        $product_name = $product['product_name'];
        $product_description = $product['product_description'] ?? '';
        $ordered_quantity = (int)$product['ordered_quantity'];
        $product_image = '';

        // Handle file upload
        if (isset($product_photos['name'][$index]) && $product_photos['error'][$index] == UPLOAD_ERR_OK) {
            $file_name = time() . '_' . basename($product_photos['name'][$index]);
            $target_file = $upload_dir . $file_name;
            if (move_uploaded_file($product_photos['tmp_name'][$index], $target_file)) {
                $product_image = '/Uploads/' . $file_name;
            }
        }

        $insert_product_sql = "INSERT INTO sales_order_products (SalesOrderID, ProductName, ProductDescription, ProductImage, OrderedQuantity) 
                              VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_product_sql);
        mysqli_stmt_bind_param($stmt, 'isssi', $sales_order_id, $product_name, $product_description, $product_image, $ordered_quantity);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Insert into sales_order_products failed: ' . mysqli_error($conn));
        }
        mysqli_stmt_close($stmt);
    }

    mysqli_commit($conn);
    header("Location: view_sales_orders.php");
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = 'Failed to add sales order: ' . $e->getMessage();
    header("Location: add_sales_order.php");
    exit;
}
