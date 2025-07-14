<?php
session_start();
$_SESSION['success'] = '';
$_SESSION['error'] = '';
include "config.php";
// include "library_detact.php";
if (isset($_POST['Assign_Order'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['OrderID']);
    $quantity = (int)$_POST['Quantity'];
    $wages_per_piece = (float)$_POST['WagesPerPiece'];
    $production_due_date = mysqli_real_escape_string($conn, $_POST['ProductionDueDate']);
    $artisan_id = (int)$_POST['ArtisanID'];
    $department_id = (int)$_POST['DepartmentID'];

    // Validate Quantity against PendingQuantity
    $sql = "SELECT ic.PendingQuantity 
            FROM orders o 
            JOIN inventory_checks ic ON o.InventoryCheckID = ic.InventoryCheckID 
            WHERE o.OrderID = $order_id AND o.is_delete = 0";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($quantity > $row['PendingQuantity']) {
            $_SESSION['error'] = 'Quantity cannot exceed pending quantity (' . $row['PendingQuantity'] . ').';
            header("Location: orders");
            exit;
        }
    } else {
        $_SESSION['error'] = 'Invalid order selected.';
        header("Location: orders");
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        $sql = "UPDATE orders 
                SET Quantity = $quantity, 
                    WagesPerPiece = $wages_per_piece, 
                    ProductionDueDate = '$production_due_date', 
                    ArtisanID = $artisan_id, 
                    DepartmentID = $department_id, 
                    Status = 'inprocess' 
                WHERE OrderID = $order_id AND is_delete = 0";
        if (!mysqli_query($conn, $sql)) {
            throw new Exception('Failed to assign order: ' . mysqli_error($conn));
        }
        mysqli_commit($conn);
        $_SESSION['success'] = 'Order assigned successfully.';
        header("Location: orders");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
        header("Location: orders");
        exit;
    }
}

// user edit section
if (isset($_POST['Edit_Order'])) {

    $OrderID = mysqli_real_escape_string($conn, $_POST['OrderID']);
    $CustomerName = mysqli_real_escape_string($conn, $_POST['CustomerName']);
    $Product = mysqli_real_escape_string($conn, $_POST['Product']);
    $Quantity = mysqli_real_escape_string($conn, $_POST['Quantity']);
    $WagesPerPiece = mysqli_real_escape_string($conn, $_POST['WagesPerPiece']);
    $ProductionDueDate = mysqli_real_escape_string($conn, $_POST['ProductionDueDate']);
    $ArtisanID = mysqli_real_escape_string($conn, $_POST['ArtisanID']);
    $DepartmentID = mysqli_real_escape_string($conn, $_POST['DepartmentID']);

    if (empty($CustomerName) || empty($Product) || empty($Quantity) || empty($WagesPerPiece) || empty($ProductionDueDate) ||  empty($ArtisanID) || empty($DepartmentID)) {
        $_SESSION['error'] = 'All Failed Requird.';
        header("Location: orders");
        exit;
    }
    // chaking the vailed date
    if ($ProductionDueDate < date('Y-m-d H:i:s')) {
        $_SESSION['error'] = 'Due Date must be greater than today';
        header("Location: orders");
        exit;
    }
    $sql = "UPDATE orders SET CustomerName = '{$CustomerName}',Product = '{$Product}',Quantity ={$Quantity}, WagesPerPiece = {$WagesPerPiece}, ProductionDueDate = '{$ProductionDueDate}', ArtisanID = {$ArtisanID}, DepartmentID = {$DepartmentID}  WHERE OrderID= {$OrderID}";

    // user registretion 

    if (mysqli_query($conn, $sql)) {

        $_SESSION['success'] = 'Orders Successfully Updated.';
        header("Location: orders");
        exit;
    } else {
        $_SESSION['error'] = 'Sorry Update Faild.';
        header("Location: orders");
        exit;
    }
}
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    $sql = "UPDATE orders SET is_delete = 1 WHERE OrderID = $id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = 'Orders Successfully Deleted.';
        header("Location: orders");
        exit;
    } else {
        $_SESSION['error'] = 'Sorry Delete Faild.';
        header("Location: orders");
        exit;
    }
}
