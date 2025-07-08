<?php
session_start();
$_SESSION['success'] = '';
$_SESSION['error'] = '';
include "config.php";
// include "library_detact.php";
if (isset($_POST['Add_Order'])) {
    $CustomerName = mysqli_real_escape_string($conn, $_POST['CustomerName']);
    $Product = mysqli_real_escape_string($conn, $_POST['Product']);
    $Quantity = mysqli_real_escape_string($conn, $_POST['Quantity']);
    $WagesPerPiece = mysqli_real_escape_string($conn, $_POST['WagesPerPiece']);
    $ProductionDueDate = mysqli_real_escape_string($conn, $_POST['ProductionDueDate']);
    $ArtisanID = mysqli_real_escape_string($conn, $_POST['ArtisanID']);
    $DepartmentID = mysqli_real_escape_string($conn, $_POST['DepartmentID']);
   

    if (empty($CustomerName) || empty($Product) || empty($Quantity) || empty($WagesPerPiece) || empty($ProductionDueDate) ||  empty($ArtisanID)) {
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
    // user registretion 
    $sql = "INSERT INTO orders (CustomerName,Product,Quantity,WagesPerPiece,ProductionDueDate,ArtisanID,DepartmentID)
        VALUE('{$CustomerName}','{$Product}',{$Quantity},{$WagesPerPiece},'{$ProductionDueDate}',{$ArtisanID},{$DepartmentID})";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = 'Orders Successfully Register.';
        header("Location: orders");
        exit;
    } else {
        $_SESSION['error'] = 'Sorry Registretion Faild.';
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
