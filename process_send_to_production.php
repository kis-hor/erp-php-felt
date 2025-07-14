<?php
session_start();
include "config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header('Location: send_to_production.php');
    exit;
}

$remarks = $_POST['remarks'] ?? [];

mysqli_begin_transaction($conn);

try {
    foreach ($remarks as $inventory_fulfillment_id => $remark) {
        $sql = "UPDATE inventory_fulfillment 
                SET Remarks = ?, 
                    SentToProduction = 1,
                    Status = 'In Progress',
                    Progress = 0
                WHERE InventoryFulfillmentID = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $remark, $inventory_fulfillment_id);
        mysqli_stmt_execute($stmt);
    }

    mysqli_commit($conn);
    $_SESSION['success'] = 'Products sent to production successfully';
    header('Location: production_dashboard.php');
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = 'Error sending to production: ' . $e->getMessage();
    header('Location: send_to_production.php');
    exit;
}
