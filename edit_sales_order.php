<?php
session_start();
include "config.php";

$sales_order_id = isset($_GET['sales_order_id']) ? intval($_GET['sales_order_id']) : 0;
if ($sales_order_id <= 0) {
    $_SESSION['error'] = "Invalid sales order ID.";
    header("Location: view_sales_orders.php");
    exit;
}

// Fetch order details
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $po_number = $_POST['po_number'];
    $customer_name = $_POST['customer_name'];
    $salesperson_name = $_POST['salesperson_name'];
    $order_date = $_POST['order_date'];
    $remarks = $_POST['remarks'];

    // Update sales order
    $update_sql = "UPDATE sales_orders SET PONumber=?, CustomerName=?, SalespersonName=?, OrderDate=?, Remarks=? WHERE SalesOrderID=?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, 'sssssi', $po_number, $customer_name, $salesperson_name, $order_date, $remarks, $sales_order_id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Update products
    if ($success && isset($_POST['products']) && is_array($_POST['products'])) {
        foreach ($_POST['products'] as $product_id => $product_data) {
            $product_name = $product_data['product_name'];
            $product_description = $product_data['product_description'];
            $ordered_quantity = intval($product_data['ordered_quantity']);

            $update_product_sql = "UPDATE sales_order_products SET ProductName=?, ProductDescription=?, OrderedQuantity=? WHERE SalesOrderProductID=? AND SalesOrderID=?";
            $product_stmt = mysqli_prepare($conn, $update_product_sql);
            mysqli_stmt_bind_param($product_stmt, 'ssiii', $product_name, $product_description, $ordered_quantity, $product_id, $sales_order_id);
            mysqli_stmt_execute($product_stmt);
            mysqli_stmt_close($product_stmt);
        }
    }

    $_SESSION['success'] = "Sales order and products updated successfully.";
    header("Location: view_sales_orders.php");
    exit;
}
?>

<?php include "assets/includes/header.php"; ?>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-xl-8">
                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="mb-4">Edit Sales Order</h4>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="po_number" class="form-label">PO Number</label>
                                    <input type="text" class="form-control" id="po_number" name="po_number" value="<?php echo htmlspecialchars($order['PONumber']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label">Customer Name</label>
                                    <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($order['CustomerName']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="salesperson_name" class="form-label">Salesperson Name</label>
                                    <input type="text" class="form-control" id="salesperson_name" name="salesperson_name" value="<?php echo htmlspecialchars($order['SalespersonName']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="order_date" class="form-label">Order Date</label>
                                    <input type="date" class="form-control" id="order_date" name="order_date" value="<?php echo htmlspecialchars($order['OrderDate']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="remarks" class="form-label">Remarks</label>
                                    <textarea class="form-control" id="remarks" name="remarks"><?php echo htmlspecialchars($order['Remarks']); ?></textarea>
                                </div>
                                <h5 class="mb-3">Edit Products</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Product Name</th>
                                                <th>Description</th>
                                                <th>Ordered Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($products) > 0): ?>
                                                <?php $serial = 1; ?>
                                                <?php foreach ($products as $product): ?>
                                                    <tr>
                                                        <td><?php echo $serial++; ?></td>
                                                        <td>
                                                            <input type="text" name="products[<?php echo $product['SalesOrderProductID']; ?>][product_name]" class="form-control" value="<?php echo htmlspecialchars($product['ProductName']); ?>" required>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="products[<?php echo $product['SalesOrderProductID']; ?>][product_description]" class="form-control" value="<?php echo htmlspecialchars($product['ProductDescription']); ?>">
                                                        </td>
                                                        <td>
                                                            <input type="number" name="products[<?php echo $product['SalesOrderProductID']; ?>][ordered_quantity]" class="form-control" value="<?php echo htmlspecialchars($product['OrderedQuantity']); ?>" min="1" required>
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
                                <button type="submit" class="btn btn-primary">Update Order & Products</button>
                                <a href="view_sales_orders.php" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "assets/includes/footer.php"; ?>