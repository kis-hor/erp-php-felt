<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "config.php";

$fulfillment_data = $_POST['fulfillment'] ?? [];

mysqli_begin_transaction($conn);
try {
    foreach ($fulfillment_data as $sales_order_product_id => $data) {
        $sales_order_product_id = (int)$sales_order_product_id;
        $available_quantity = (int)$data['available_quantity'];
        $pending_quantity = (int)$data['pending_quantity'];

        // Insert into inventory_fulfillment
        $insert_sql = "INSERT INTO inventory_fulfillment (SalesOrderProductID, AvailableQuantity, PendingQuantity, CheckedAt) 
                       VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, 'iii', $sales_order_product_id, $available_quantity, $pending_quantity);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Insert into inventory_fulfillment failed: ' . mysqli_error($conn));
        }
        mysqli_stmt_close($stmt);

        // After inserting into inventory_fulfillment
    }

    mysqli_commit($conn);
    $_SESSION['success'] = 'Inventory fulfillment recorded successfully.';
    header("Location: inventory_fulfillment.php");
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = 'Failed to record fulfillment: ' . $e->getMessage();
    header("Location: inventory_fulfillment.php");
    exit;
}
