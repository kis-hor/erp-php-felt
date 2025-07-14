<?php
session_start();
include "config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Get form data
$sales_order_product_id = $_POST['sales_order_product_id'] ?? '';
$artisan_name = $_POST['artisan_name'] ?? '';
$quantity = $_POST['quantity'] ?? '';
$wages = $_POST['wages'] ?? '';
$due_date = $_POST['due_date'] ?? '';

// Validate inputs
if (!$sales_order_product_id || !$artisan_name || !$quantity || !$wages || !$due_date) {
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit;
}

mysqli_begin_transaction($conn);

try {
    // Insert into production_assignments
    $sql = "INSERT INTO production_assignments 
            (SalesOrderProductID, ArtisanName, AssignedQuantity, 
             WagesPerUnit, ProductionDueDate, Status, Progress, is_delete) 
            VALUES (?, ?, ?, ?, ?, 'In Progress', 0, 0)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "isids",
        $sales_order_product_id,
        $artisan_name,
        $quantity,
        $wages,
        $due_date
    );
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) <= 0) {
        throw new Exception("Failed to insert assignment");
    }

    // Update inventory_fulfillment pending quantity
    $update_inventory = "UPDATE inventory_fulfillment 
                        SET PendingQuantity = PendingQuantity - ? 
                        WHERE SalesOrderProductID = ?";
    $stmt = mysqli_prepare($conn, $update_inventory);
    mysqli_stmt_bind_param($stmt, "ii", $quantity, $sales_order_product_id);
    mysqli_stmt_execute($stmt);

    mysqli_commit($conn);
    echo json_encode(['success' => true, 'message' => 'Assignment created successfully']);
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
