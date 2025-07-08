<?php
session_start();
$_SESSION['success'] = '';
$_SESSION['error'] = '';
include "config.php";

// Handle Single Approval (For a single order)
if (isset($_POST['Approve_Order'])) {
    $ApprovedQuantity = mysqli_real_escape_string($conn, $_POST['ApprovedQuantity']);
    // $RejectedQuantity = mysqli_real_escape_string($conn, $_POST['RejectedQuantity']);
    $ApprovalDate = mysqli_real_escape_string($conn, $_POST['ApprovalDate']);
    $OrderID = mysqli_real_escape_string($conn, $_POST['OrderID']);

    if (empty($ApprovedQuantity) || empty($ApprovalDate) || empty($OrderID)) {
        $_SESSION['error'] = 'All fields are required.';
        header("Location: orders");
        exit;
    }

    // Check the order quantity
    $sql_order = "SELECT Quantity FROM orders WHERE OrderID = {$OrderID}";
    $result_order = mysqli_query($conn, $sql_order);
    $row_order = mysqli_fetch_assoc($result_order);

    // Check if ApprovedQuantity is valid
    if ($ApprovedQuantity > $row_order['Quantity']) {
        $_SESSION['error'] = 'Approved Quantity is not valid';
        header("Location: orders");
        exit;
    }

    $RejectedQuantity = $row_order['Quantity'] - $ApprovedQuantity;
    $ApprovalBy = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : 0;

    // Check if ApprovalDate is valid
    if ($ApprovalDate < date('Y-m-d')) {
        $_SESSION['error'] = 'Approval Date must be greater than today';
        header("Location: orders");
        exit;
    }

    // Insert into quality_control table for single approval
    $sql = "INSERT INTO quality_control (ApprovedQuantity, RejectedQuantity, ApprovalDate, OrderID, ApprovalBy)
            VALUES ({$ApprovedQuantity}, {$RejectedQuantity}, '{$ApprovalDate}', {$OrderID}, {$ApprovalBy})";

    if (mysqli_query($conn, $sql)) {
        $sql_update_o = "UPDATE orders SET Status = 'Approved' WHERE OrderID = {$OrderID}";
        mysqli_query($conn, $sql_update_o);
        $_SESSION['success'] = 'Order Successfully Approved.';
        header("Location: dispatch");
        exit;
    } else {
        $_SESSION['error'] = 'Sorry, approval failed.';
        header("Location: dispatch");
        exit;
    }
}

// Handle Bulk Approval (For multiple orders)
if (isset($_POST['approve_all'])) {
    if (empty($_POST['OrderID']) || empty($_POST['ApprovedQuantity']) || empty($_POST['ApprovalDate'])) {
        $_SESSION['error'] = 'All fields are required for bulk approval.';
        header("Location: orders");
        exit;
    }

    $orderIDs = $_POST['OrderID']; // Array of OrderIDs
    $approvedQuantities = $_POST['ApprovedQuantity']; // Array of Approved Quantities
    $approvalDates = $_POST['ApprovalDate']; // Array of Approval Dates

    $ApprovalBy = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : 0;

    // Loop through all the selected orders
    for ($i = 0; $i < count($orderIDs); $i++) {
        $OrderID = mysqli_real_escape_string($conn, $orderIDs[$i]);
        $ApprovedQuantity = mysqli_real_escape_string($conn, $approvedQuantities[$i]);
        $ApprovalDate = mysqli_real_escape_string($conn, $approvalDates[$i]);

        // Check if all fields are valid
        $sql_order = "SELECT Quantity FROM orders WHERE OrderID = {$OrderID}";
        $result_order = mysqli_query($conn, $sql_order);
        $row_order = mysqli_fetch_assoc($result_order);

        if ($ApprovedQuantity > $row_order['Quantity']) {
            $_SESSION['error'] = "Approved Quantity for Order {$OrderID} is not valid.";
            header("Location: orders");
            exit;
        }

        $RejectedQuantity = $row_order['Quantity'] - $ApprovedQuantity;

        // Check if ApprovalDate is valid
        if ($ApprovalDate < date('Y-m-d')) {
            $_SESSION['error'] = 'Approval Date must be greater than today.';
            header("Location: orders");
            exit;
        }

        // Insert into quality_control table for bulk approval
        $sql = "INSERT INTO quality_control (ApprovedQuantity, RejectedQuantity, ApprovalDate, OrderID, ApprovalBy)
                VALUES ({$ApprovedQuantity}, {$RejectedQuantity}, '{$ApprovalDate}', {$OrderID}, {$ApprovalBy})";
        if (!mysqli_query($conn, $sql)) {
            $_SESSION['error'] = 'Failed to approve one or more orders.';
            header("Location: dispatch");
            exit;
        }

        // Update order status for bulk approval
        $sql_update_o = "UPDATE orders SET Status = 'Approved' WHERE OrderID = {$OrderID}";
        if (!mysqli_query($conn, $sql_update_o)) {
            $_SESSION['error'] = 'Failed to update order status for bulk approval.';
            header("Location: dispatch");
            exit;
        }
    }

    $_SESSION['success'] = 'Orders Successfully Approved.';
    header("Location: dispatch");
    exit;
}
