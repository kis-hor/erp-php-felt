<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header('Location: inventory_sales_orders.php');
    exit;
}

$fulfillment = $_POST['fulfillment'] ?? [];
$sales_order_id = 0; // We'll get this from the first product

mysqli_begin_transaction($conn);

try {
    foreach ($fulfillment as $sales_order_product_id => $data) {
        // Insert into inventory_fulfillment
        $sql = "INSERT INTO inventory_fulfillment 
                (SalesOrderProductID, AvailableQuantity, PendingQuantity, Status) 
                VALUES (?, ?, ?, 'In Progress')";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            "iii",
            $sales_order_product_id,
            $data['available_quantity'],
            $data['pending_quantity']
        );
        mysqli_stmt_execute($stmt);

        // Get the sales_order_id if we haven't yet
        if (!$sales_order_id) {
            $sql = "SELECT SalesOrderID FROM sales_order_products WHERE SalesOrderProductID = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $sales_order_product_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $sales_order_id = $row['SalesOrderID'];
        }
    }

    mysqli_commit($conn);
    $_SESSION['success'] = 'Inventory fulfillment recorded successfully';
    // Redirect to send_to_production.php with the sales_order_id
    header("Location: send_to_production.php?sales_order_id=" . $sales_order_id);
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = 'Error recording fulfillment: ' . $e->getMessage();
    header('Location: inventory_fulfillment.php?sales_order_id=' . $sales_order_id);
}
exit;
