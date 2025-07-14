<?php
include "config.php";

$sales_order_id = isset($_GET['sales_order_id']) ? intval($_GET['sales_order_id']) : 0;

$sql = "SELECT sop.SalesOrderProductID, sop.ProductName, sop.ProductImage,
               inf.PendingQuantity
        FROM sales_order_products sop
        JOIN inventory_fulfillment inf ON sop.SalesOrderProductID = inf.SalesOrderProductID
        WHERE sop.SalesOrderID = ? 
        AND inf.SentToProduction = 1
        AND inf.PendingQuantity > 0";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $sales_order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Format image path
    if (!empty($row['ProductImage'])) {
        $imgSrc = ltrim($row['ProductImage'], '/');
        $imgSrc = preg_replace('/^Uploads\//', 'Uploads/', $imgSrc);
        $imgSrc = dirname($imgSrc) . '/' . rawurlencode(basename($imgSrc));
        $row['ProductImage'] = $imgSrc;
    }
    $products[] = $row;
}

header('Content-Type: application/json');
echo json_encode($products);
