<?php
session_start();
include "config.php";


$sales_order_id = isset($_GET['sales_order_id']) ? intval($_GET['sales_order_id']) : 0;
if ($sales_order_id <= 0) {
    $_SESSION['error'] = "Invalid sales order ID.";
    header("Location: view_sales_orders.php");
    exit;
}

$sql = "SELECT * FROM sales_orders WHERE SalesOrderID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $sales_order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    $_SESSION['error'] = "Sales order not found.";
    header("Location: view_sales_orders.php");
    exit;
}

// Fetch products for this sales order
$product_sql = "SELECT * FROM sales_order_products WHERE SalesOrderID = ?";
$product_stmt = mysqli_prepare($conn, $product_sql);
mysqli_stmt_bind_param($product_stmt, 'i', $sales_order_id);
mysqli_stmt_execute($product_stmt);
$product_result = mysqli_stmt_get_result($product_stmt);
$products = [];
while ($product = mysqli_fetch_assoc($product_result)) {
    $products[] = $product;
}
mysqli_stmt_close($product_stmt);

include "assets/includes/header.php";
?>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-xl-8">
                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="mb-4">Sales Order Details</h4>
                            <table class="table table-bordered mb-4">
                                <tr>
                                    <th>PO Number</th>
                                    <td><?php echo htmlspecialchars($order['PONumber']); ?></td>
                                </tr>
                                <tr>
                                    <th>Order Date</th>
                                    <td><?php echo htmlspecialchars($order['OrderDate']); ?></td>
                                </tr>
                                <tr>
                                    <th>Customer Name</th>
                                    <td><?php echo htmlspecialchars($order['CustomerName']); ?></td>
                                </tr>
                                <tr>
                                    <th>Salesperson Name</th>
                                    <td><?php echo htmlspecialchars($order['SalespersonName']); ?></td>
                                </tr>
                                <tr>
                                    <th>Remarks</th>
                                    <td><?php echo htmlspecialchars($order['Remarks']); ?></td>
                                </tr>
                            </table>
                            <h5 class="mb-3">Product Details</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Description</th>
                                            <th>Ordered Quantity</th>
                                            <th>Product Photo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($products) > 0): ?>
                                            <?php foreach ($products as $product): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($product['ProductName']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['ProductDescription']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['OrderedQuantity']); ?></td>
                                                    <td>
                                                        <?php if (!empty($product['ProductImage'])): ?>
                                                            <img src="<?php echo $product['ProductImage']; ?>" alt="Product Photo" style="max-width:80px;max-height:80px;">
                                                        <?php else: ?>
                                                            <span class="text-muted">No photo</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No products found for this order.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <a href="view_sales_orders.php" class="btn btn-secondary">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "assets/includes/footer.php"; ?>