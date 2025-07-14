<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "config.php";
$sales_order_product_id = intval($_GET['sales_order_product_id'] ?? 0);

// Fetch product details
$product_sql = "SELECT sop.*, so.PONumber, so.CustomerName FROM sales_order_products sop JOIN sales_orders so ON sop.SalesOrderID = so.SalesOrderID WHERE sop.SalesOrderProductID = ?";
$product_stmt = mysqli_prepare($conn, $product_sql);
mysqli_stmt_bind_param($product_stmt, 'i', $sales_order_product_id);
mysqli_stmt_execute($product_stmt);
$product_result = mysqli_stmt_get_result($product_stmt);
$product = mysqli_fetch_assoc($product_result);
mysqli_stmt_close($product_stmt);

// Fetch artisans
$artisans = [];
$artisan_sql = "SELECT ArtisanName FROM artisans WHERE is_delete = 0";
$artisan_result = mysqli_query($conn, $artisan_sql);
while ($row = mysqli_fetch_assoc($artisan_result)) {
    $artisans[] = $row['ArtisanName'];
}
?>

<?php include "assets/includes/header.php"; ?>
<div class="container mt-4">
    <h4>Assign Product to Artisans</h4>
    <form method="POST" action="process_assign_to_artisan.php">
        <input type="hidden" name="sales_order_product_id" value="<?php echo $sales_order_product_id; ?>">
        <div class="mb-2"><b>Order:</b> <?php echo htmlspecialchars($product['PONumber']); ?></div>
        <div class="mb-2"><b>Customer:</b> <?php echo htmlspecialchars($product['CustomerName']); ?></div>
        <div class="mb-2"><b>Product:</b> <?php echo htmlspecialchars($product['ProductName']); ?></div>
        <div class="mb-2"><b>Pending Quantity:</b> <?php echo $product['OrderedQuantity'] - $product['AvailableQuantity']; ?></div>
        <hr>
        <div class="table-responsive">
            <table class="table table-bordered" id="artisan-assignments-table">
                <thead>
                    <tr>
                        <th>Artisan</th>
                        <th>Quantity</th>
                        <th>Wages/unit</th>
                        <th>Due Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="artisan-row">
                        <td>
                            <select name="assignments[0][artisan_name]" class="form-control" required>
                                <option value="">Select Artisan</option>
                                <?php foreach ($artisans as $name): ?>
                                    <option value="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="assignments[0][assigned_quantity]" class="form-control" min="1" max="<?php echo $product['OrderedQuantity'] - $product['AvailableQuantity']; ?>" required>
                        </td>
                        <td>
                            <input type="number" name="assignments[0][wages_per_piece]" class="form-control" min="0" step="0.01" required>
                        </td>
                        <td>
                            <input type="date" name="assignments[0][production_due_date]" class="form-control" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-secondary add-artisan-row">+</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Assign</button>
    </form>
</div>
<script>
    $(document).ready(function() {
        let rowIndex = 1;
        $('.add-artisan-row').click(function() {
            let maxQty = <?php echo $product['OrderedQuantity'] - $product['AvailableQuantity']; ?>;
            let rowHtml = `
            <tr class="artisan-row">
                <td>
                    <select name="assignments[${rowIndex}][artisan_name]" class="form-control" required>
                        <option value="">Select Artisan</option>
                        <?php foreach ($artisans as $name): ?>
                            <option value="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <input type="number" name="assignments[${rowIndex}][assigned_quantity]" class="form-control" min="1" max="${maxQty}" required>
                </td>
                <td>
                    <input type="number" name="assignments[${rowIndex}][wages_per_piece]" class="form-control" min="0" step="0.01" required>
                </td>
                <td>
                    <input type="date" name="assignments[${rowIndex}][production_due_date]" class="form-control" required>
                </td>
                <td>
                    <button type="button" class="btn btn-danger remove-artisan-row">-</button>
                </td>
            </tr>`;
            $('#artisan-assignments-table tbody').append(rowHtml);
            rowIndex++;
        });
        $(document).on('click', '.remove-artisan-row', function() {
            $(this).closest('.artisan-row').remove();
        });
    });
</script>
<?php include "assets/includes/footer.php"; ?>