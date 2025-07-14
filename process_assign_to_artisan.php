<?php
session_start();
include "config.php";

$sales_order_product_id = intval($_POST['sales_order_product_id']);
$assignments = $_POST['assignments'] ?? [];

foreach ($assignments as $assignment) {
    $artisan_name = $assignment['artisan_name'];
    $assigned_quantity = intval($assignment['assigned_quantity']);
    $wages_per_piece = floatval($assignment['wages_per_piece']);
    $production_due_date = $assignment['production_due_date'];
    $status = 'inprocess';

    $insert_sql = "INSERT INTO production_assignments (SalesOrderProductID, ArtisanName, AssignedQuantity, WagesPerPiece, ProductionDueDate, Status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($stmt, 'isidss', $sales_order_product_id, $artisan_name, $assigned_quantity, $wages_per_piece, $production_due_date, $status);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

$_SESSION['success'] = 'Production assignment(s) created successfully.';
header('Location: pending_production.php');
exit;
