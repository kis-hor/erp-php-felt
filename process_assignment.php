<?php
session_start();
include "config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header('Location: product_assignments.php');
    exit;
}

$sales_order_product_id = $_POST['sales_order_product_id'] ?? '';
$artisan_name = $_POST['artisan_name'] ?? '';
$quantity = $_POST['quantity'] ?? '';
$wages = $_POST['wages'] ?? '';
$due_date = $_POST['due_date'] ?? '';

if (!$sales_order_product_id || !$artisan_name || !$quantity || !$wages || !$due_date) {
    $_SESSION['error'] = 'All fields are required';
    header('Location: product_assignments.php');
    exit;
}

mysqli_begin_transaction($conn);

try {
    $sql = "INSERT INTO production_assignments 
            (SalesOrderProductID, ArtisanName, AssignedQuantity, 
             WagesPerPiece, ProductionDueDate, Status) 
            VALUES (?, ?, ?, ?, ?, 'In Progress')";

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

    mysqli_commit($conn);
    $_SESSION['success'] = 'Assignment created successfully';
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = 'Error creating assignment: ' . $e->getMessage();
}

header('Location: product_assignments.php');
exit;
