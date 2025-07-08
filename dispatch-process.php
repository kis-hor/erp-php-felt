<?php
session_start();
include "config.php";

$response = array('status' => 'error', 'message' => 'Unknown error occurred.');

if (isset($_POST['bulk_dispatch'])) {
    $orderIDs = json_decode($_POST['selectedOrderIDs']); // Decode the JSON array
    $DispatchDate = mysqli_real_escape_string($conn, $_POST['DispatchDate']);
    $DispatchMathod = mysqli_real_escape_string($conn, $_POST['DispatchMathod']);

    if (empty($DispatchDate) || empty($DispatchMathod)) {
        $response['message'] = 'All fields are required.';
    } elseif ($DispatchDate < date('Y-m-d')) {
        $response['message'] = 'Dispatch Date must be greater than today.';
    } else {
        $successCount = 0;
        $failureCount = 0;

        foreach ($orderIDs as $OrderID) {
            $OrderID = mysqli_real_escape_string($conn, $OrderID);

            $sql_update_o = "UPDATE orders 
                             SET DispatchDate = '{$DispatchDate}', 
                                 DispatchMethod = '{$DispatchMathod}', 
                                 Status = 'Dispatch' 
                             WHERE OrderID = '{$OrderID}'";

            if (mysqli_query($conn, $sql_update_o)) {
                $successCount++;
            } else {
                $failureCount++;
            }
        }

        if ($failureCount === 0) {
            $response['status'] = 'success';
            $response['message'] = 'All selected orders were successfully dispatched.';
        } else {
            $response['message'] = "$successCount orders were dispatched successfully, but $failureCount failed.";
        }
    }
} else {
    $response['message'] = 'Invalid request.';
}

echo json_encode($response);
