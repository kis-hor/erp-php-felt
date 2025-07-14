<?php
session_start();
include "config.php";
$title = 'Send to Production';
include "assets/includes/header.php";
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Send to Production</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="process_send_to_production.php">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr class="text-muted text-uppercase">
                                                <th>PO Number</th>
                                                <th>Customer Name</th>
                                                <th>Product Photo</th>
                                                <th>Product Name</th>
                                                <th>Product Description</th>
                                                <th>Pending Quantity</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Get all pending products from inventory_fulfillment
                                            $sql = "SELECT sop.SalesOrderProductID, so.PONumber, so.CustomerName, 
                                                          sop.ProductName, sop.ProductDescription, sop.ProductImage,
                                                          invf.PendingQuantity, invf.InventoryFulfillmentID
                                                   FROM inventory_fulfillment invf
                                                   JOIN sales_order_products sop ON invf.SalesOrderProductID = sop.SalesOrderProductID
                                                   JOIN sales_orders so ON sop.SalesOrderID = so.SalesOrderID
                                                   WHERE invf.PendingQuantity > 0 
                                                   AND (invf.SentToProduction = 0 OR invf.SentToProduction IS NULL)
                                                   ORDER BY so.PONumber";

                                            $result = mysqli_query($conn, $sql);
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo '<tr>';
                                                    echo '<td>' . htmlspecialchars($row['PONumber']) . '</td>';
                                                    echo '<td>' . htmlspecialchars($row['CustomerName']) . '</td>';
                                                    echo '<td>';
                                                    if (!empty($row['ProductImage'])) {
                                                        $imgSrc = ltrim($row['ProductImage'], '/');
                                                        $imgSrc = preg_replace('/^Uploads\//', 'Uploads/', $imgSrc); // Ensure correct folder
                                                        $imgSrc = dirname($imgSrc) . '/' . rawurlencode(basename($imgSrc));
                                                        echo '<img src="' . htmlspecialchars($imgSrc) . '" alt="Product Photo" style="max-width:80px;max-height:80px;">';
                                                    } else {
                                                        echo '<span class="text-muted">No photo</span>';
                                                    }
                                                    echo '</td>';
                                                    echo '<td>' . htmlspecialchars($row['ProductName']) . '</td>';
                                                    echo '<td>' . htmlspecialchars($row['ProductDescription']) . '</td>';
                                                    echo '<td>' . $row['PendingQuantity'] . '</td>';
                                                    echo '<td>
                                                            <input type="text" class="form-control" 
                                                                   name="remarks[' . $row['InventoryFulfillmentID'] . ']" 
                                                                   placeholder="Add remarks for production"
                                                                   required>
                                                          </td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="7" class="text-center">No pending products found for production.</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if (mysqli_num_rows($result) > 0): ?>
                                    <div class="hstack gap-2 justify-content-end mt-3">
                                        <button type="submit" class="btn btn-primary">Send to Production</button>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "assets/includes/footer.php"; ?>