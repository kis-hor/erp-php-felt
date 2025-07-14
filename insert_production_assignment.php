<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "config.php";

$products = $_POST['products'] ?? [];
$single_sales_order_product_id = isset($_POST['sales_order_product_id']) ? (int)$_POST['sales_order_product_id'] : 0;

mysqli_begin_transaction($conn);
try {
    // Handle single product assignment
    if ($single_sales_order_product_id) {
        $artisans = $_POST['artisans'] ?? [];
        $wages_per_piece = isset($_POST['wages_per_piece']) ? (float)$_POST['wages_per_piece'] : 0;
        $production_due_date = $_POST['production_due_date'] ?? '';
        if (empty($artisans)) {
            throw new Exception('No artisans assigned.');
        }
        if ($wages_per_piece < 0) {
            throw new Exception('Wages per piece must be non-negative.');
        }
        if (empty($production_due_date)) {
            throw new Exception('Production due date is required.');
        }

        // Fetch pending quantity
        $sql = "SELECT (sop.OrderedQuantity - invf.AvailableQuantity) AS PendingQuantity
                FROM sales_order_products sop
                JOIN inventory_fulfillment invf ON sop.SalesOrderProductID = invf.SalesOrderProductID
                WHERE sop.SalesOrderProductID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'i', $single_sales_order_product_id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Execute failed: ' . mysqli_error($conn));
        }
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $pending_quantity = $row['PendingQuantity'] ?? 0;
        mysqli_stmt_close($stmt);

        if ($pending_quantity <= 0) {
            throw new Exception('No pending quantity available for this product.');
        }

        // Validate total assigned quantity
        $total_assigned = 0;
        foreach ($artisans as $artisan) {
            $assigned_quantity = (int)($artisan['assigned_quantity'] ?? 0);
            if ($assigned_quantity < 1) {
                throw new Exception('Assigned quantity must be positive for all artisans.');
            }
            $total_assigned += $assigned_quantity;
        }
        if ($total_assigned > $pending_quantity) {
            throw new Exception('Total assigned quantity (' . $total_assigned . ') exceeds pending quantity (' . $pending_quantity . ').');
        }

        // Insert assignments
        $insert_sql = "INSERT INTO production_assignments (SalesOrderProductID, ArtisanName, AssignedQuantity, WagesPerPiece, ProductionDueDate, Status) 
                       VALUES (?, ?, ?, ?, ?, 'Pending')";
        $stmt = mysqli_prepare($conn, $insert_sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . mysqli_error($conn));
        }
        foreach ($artisans as $artisan) {
            $artisan_name = mysqli_real_escape_string($conn, $artisan['artisan_name'] ?? '');
            $assigned_quantity = (int)($artisan['assigned_quantity'] ?? 0);
            if (empty($artisan_name)) {
                throw new Exception('Artisan name is required.');
            }
            mysqli_stmt_bind_param($stmt, 'isids', $single_sales_order_product_id, $artisan_name, $assigned_quantity, $wages_per_piece, $production_due_date);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Insert failed: ' . mysqli_error($conn));
            }
        }
        mysqli_stmt_close($stmt);
    } else {
        // Handle bulk assignment
        if (empty($products)) {
            throw new Exception('No products selected for bulk assignment.');
        }
        foreach ($products as $product) {
            $sales_order_product_id = (int)($product['sales_order_product_id'] ?? 0);
            $artisans = $product['artisans'] ?? [];
            $wages_per_piece = isset($product['wages_per_piece']) ? (float)$product['wages_per_piece'] : 0;
            $production_due_date = $product['production_due_date'] ?? '';
            if (empty($artisans)) {
                throw new Exception('No artisans assigned for product ID: ' . $sales_order_product_id);
            }
            if ($wages_per_piece < 0) {
                throw new Exception('Wages per piece must be non-negative for product ID: ' . $sales_order_product_id);
            }
            if (empty($production_due_date)) {
                throw new Exception('Production due date is required for product ID: ' . $sales_order_product_id);
            }

            // Fetch pending quantity
            $sql = "SELECT (sop.OrderedQuantity - invf.AvailableQuantity) AS PendingQuantity
                    FROM sales_order_products sop
                    JOIN inventory_fulfillment invf ON sop.SalesOrderProductID = invf.SalesOrderProductID
                    WHERE sop.SalesOrderProductID = ?";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt, 'i', $sales_order_product_id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Execute failed: ' . mysqli_error($conn));
            }
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $pending_quantity = $row['PendingQuantity'] ?? 0;
            mysqli_stmt_close($stmt);

            if ($pending_quantity <= 0) {
                throw new Exception('No pending quantity available for product ID: ' . $sales_order_product_id);
            }

            // Validate total assigned quantity
            $total_assigned = 0;
            foreach ($artisans as $artisan) {
                $assigned_quantity = (int)($artisan['assigned_quantity'] ?? 0);
                if ($assigned_quantity < 1) {
                    throw new Exception('Assigned quantity must be positive for product ID: ' . $sales_order_product_id);
                }
                $total_assigned += $assigned_quantity;
            }
            if ($total_assigned > $pending_quantity) {
                throw new Exception('Total assigned quantity (' . $total_assigned . ') exceeds pending quantity (' . $pending_quantity . ') for product ID: ' . $sales_order_product_id);
            }

            // Insert assignments
            $insert_sql = "INSERT INTO production_assignments (SalesOrderProductID, ArtisanName, AssignedQuantity, WagesPerPiece, ProductionDueDate, Status) 
                           VALUES (?, ?, ?, ?, ?, 'Pending')";
            $stmt = mysqli_prepare($conn, $insert_sql);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . mysqli_error($conn));
            }
            foreach ($artisans as $artisan) {
                $artisan_name = mysqli_real_escape_string($conn, $artisan['artisan_name'] ?? '');
                $assigned_quantity = (int)($artisan['assigned_quantity'] ?? 0);
                if (empty($artisan_name)) {
                    throw new Exception('Artisan name is required for product ID: ' . $sales_order_product_id);
                }
                mysqli_stmt_bind_param($stmt, 'isids', $sales_order_product_id, $artisan_name, $assigned_quantity, $wages_per_piece, $production_due_date);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception('Insert failed: ' . mysqli_error($conn));
                }
            }
            mysqli_stmt_close($stmt);
        }
    }

    mysqli_commit($conn);
    $_SESSION['success'] = 'Production assigned successfully.';
    header("Location: pending_production.php");
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = 'Failed to assign production: ' . $e->getMessage();
    header("Location: " . ($single_sales_order_product_id ? "assign_to_artisan.php?sales_order_product_id=$single_sales_order_product_id" : "bulk_assign_to_artisan.php"));
    exit;
}
