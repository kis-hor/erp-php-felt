<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "config.php";
$title = 'Inventory Fulfillment';
include "assets/includes/header.php";

$sales_order_id = isset($_GET['sales_order_id']) ? intval($_GET['sales_order_id']) : 0;
if ($sales_order_id <= 0) {
    echo '<div class="alert alert-danger">Invalid Sales Order ID.</div>';
    include "assets/includes/footer.php";
    exit;
}
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Inventory Fulfillment</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item active"><i class="las la-angle-right"></i>Inventory Fulfillment</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            if (isset($_SESSION['error']) && !empty($_SESSION['error'])) {
                echo '<div class="alert alert-danger" role="alert" id="primary-alert">';
                echo '<strong>Error!</strong> ' . htmlspecialchars($_SESSION['error']);
                echo '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success']) && !empty($_SESSION['success'])) {
                echo '<div class="alert alert-primary" role="alert" id="primary-alert">';
                echo '<strong>Success!</strong> ' . htmlspecialchars($_SESSION['success']);
                echo '</div>';
                unset($_SESSION['success']);
            }
            ?>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title pb-3"><b>Unfulfilled Products</b></h5>
                            <form method="POST" action="insert_inventory_fulfillment.php">
                                <div class="table-responsive table-card">
                                    <table class="table table-hover table-nowrap align-middle mb-0">
                                        <thead>
                                            <tr class="text-muted text-uppercase">
                                                <th>PO Number</th>
                                                <th>Customer Name</th>
                                                <th>Product Photo</th>
                                                <th>Product Name</th>
                                                <th>Ordered Quantity</th>
                                                <th>Available Quantity</th>
                                                <th>Pending Quantity</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT sop.SalesOrderProductID, so.PONumber, so.CustomerName, sop.ProductName, sop.OrderedQuantity,
                                                           sop.ProductImage, invf.AvailableQuantity, invf.PendingQuantity, invf.SentToProduction
                                                    FROM sales_order_products sop
                                                    JOIN sales_orders so ON sop.SalesOrderID = so.SalesOrderID
                                                    LEFT JOIN inventory_fulfillment invf ON sop.SalesOrderProductID = invf.SalesOrderProductID
                                                    WHERE sop.SalesOrderID = $sales_order_id";
                                            $result = mysqli_query($conn, $sql);
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo '<tr>';
                                                    echo '<td>' . htmlspecialchars($row['PONumber']) . '</td>';
                                                    echo '<td>' . htmlspecialchars($row['CustomerName']) . '</td>';
                                                    echo '<td>';
                                                    if (!empty($row['ProductImage'])) {
                                                        // If $row['ProductImage'] is like 'Uploads/filename.png'


                                                        // If $row['ProductImage'] is like '/Uploads/filename.png', remove the leading slash:
                                                        $imgSrc = ltrim($row['ProductImage'], '/');
                                                        $imgSrc = preg_replace('/^Uploads\//', 'Uploads/', $imgSrc); // Ensure correct folder
                                                        $imgSrc = dirname($imgSrc) . '/' . rawurlencode(basename($imgSrc));
                                                        echo '<img src="' . htmlspecialchars($imgSrc) . '" alt="Product Photo" style="max-width:80px;max-height:80px;">';
                                                    } else {
                                                        echo '<span class="text-muted">No photo</span>';
                                                    }
                                                    echo '</td>';
                                                    echo '<td>' . htmlspecialchars($row['ProductName']) . '</td>';
                                                    echo '<td>' . $row['OrderedQuantity'] . '</td>';

                                                    if (isset($row['AvailableQuantity'])) {
                                                        echo '<td>' . $row['AvailableQuantity'] . '</td>';
                                                        echo '<td>' . $row['PendingQuantity'] . '</td>';
                                                        $status = ($row['PendingQuantity'] == 0) ? 'Fulfilled' : 'Pending';
                                                        echo '<td>' . $status . '</td>';
                                                    } else {
                                                        echo '<td><input type="number" class="form-control available-quantity" name="fulfillment[' . $row['SalesOrderProductID'] . '][available_quantity]" min="0" max="' . $row['OrderedQuantity'] . '" required data-ordered-quantity="' . $row['OrderedQuantity'] . '"></td>';
                                                        echo '<td><input type="number" class="form-control pending-quantity" name="fulfillment[' . $row['SalesOrderProductID'] . '][pending_quantity]" readonly></td>';
                                                        echo '<td>Pending</td>';
                                                    }
                                                    echo '</tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="8" class="text-center">No unfulfilled products found.</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="hstack gap-2 justify-content-end mt-3">
                                    <button type="submit" class="btn btn-primary">Submit Fulfillment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $('.available-quantity').on('input', function() {
            let orderedQuantity = parseInt($(this).data('ordered-quantity'));
            let availableQuantity = parseInt($(this).val()) || 0;
            let pendingQuantity = orderedQuantity - availableQuantity;
            $(this).closest('tr').find('.pending-quantity').val(pendingQuantity >= 0 ? pendingQuantity : 0);
        });
    });
</script>
<?php
include "assets/includes/footer.php";
?>