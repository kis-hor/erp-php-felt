<?php
session_start();
include "config.php";

$sales_order_id = isset($_GET['sales_order_id']) ? intval($_GET['sales_order_id']) : 0;
if ($sales_order_id <= 0) {
    $_SESSION['error'] = "Invalid sales order ID.";
    header("Location: view_sales_orders.php");
    exit;
}

// Delete order
$sql = "DELETE FROM sales_orders WHERE SalesOrderID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $sales_order_id);
if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = "Sales order deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete sales order.";
}
mysqli_stmt_close($stmt);

header("Location: view_sales_orders.php");
exit;
