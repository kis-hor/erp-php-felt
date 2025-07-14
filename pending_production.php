<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "config.php";
$title = 'Pending Production';
include "assets/includes/header.php";
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Pending Production</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item active"><i class="las la-angle-right"></i>Pending Production</li>
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
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title"><b>Products with Pending Quantities</b></h5>
                                <button type="button" class="btn btn-primary" id="bulk-assign-btn" disabled>Assign Selected to Artisans</button>
                            </div>
                            <form id="bulk-assign-form" method="GET" action="bulk_assign_to_artisan.php">
                                <div class="table-responsive table-card">
                                    <table class="table table-hover table-nowrap align-middle mb-0">
                                        <thead>
                                            <tr class="text-muted text-uppercase">
                                                <th><input type="checkbox" id="select-all"></th>
                                                <th>PO Number</th>
                                                <th>Product Name</th>
                                                <th>Ordered Quantity</th>
                                                <th>Available Quantity</th>
                                                <th>Pending Quantity</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT sop.SalesOrderProductID, so.PONumber, sop.ProductName, 
                                                           sop.OrderedQuantity, invf.AvailableQuantity, 
                                                           (sop.OrderedQuantity - invf.AvailableQuantity) AS PendingQuantity
                                                    FROM sales_order_products sop
                                                    JOIN sales_orders so ON sop.SalesOrderID = so.SalesOrderID
                                                    JOIN inventory_fulfillment invf ON sop.SalesOrderProductID = invf.SalesOrderProductID
                                                    WHERE sop.OrderedQuantity > invf.AvailableQuantity";
                                            $result = mysqli_query($conn, $sql);
                                            if (!$result) {
                                                echo '<tr><td colspan="7" class="text-center">Database error: ' . htmlspecialchars(mysqli_error($conn)) . '</td></tr>';
                                            } elseif (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo '<tr>';
                                                    echo '<td><input type="checkbox" name="sales_order_product_ids[]" value="' . $row['SalesOrderProductID'] . '" class="product-checkbox"></td>';
                                                    echo '<td>' . htmlspecialchars($row['PONumber']) . '</td>';
                                                    echo '<td>' . htmlspecialchars($row['ProductName']) . '</td>';
                                                    echo '<td>' . $row['OrderedQuantity'] . '</td>';
                                                    echo '<td>' . $row['AvailableQuantity'] . '</td>';
                                                    echo '<td>' . $row['PendingQuantity'] . '</td>';
                                                    echo '<td><a href="assign_to_artisan.php?sales_order_product_id=' . $row['SalesOrderProductID'] . '" class="btn btn-primary btn-sm">Assign to Artisan</a></td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="7" class="text-center">No products with pending quantities found.</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
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
        $('#select-all').click(function() {
            $('.product-checkbox').prop('checked', this.checked);
            $('#bulk-assign-btn').prop('disabled', $('.product-checkbox:checked').length === 0);
        });

        $('.product-checkbox').click(function() {
            $('#bulk-assign-btn').prop('disabled', $('.product-checkbox:checked').length === 0);
            if (!this.checked) {
                $('#select-all').prop('checked', false);
            }
        });

        $('#bulk-assign-btn').click(function() {
            if ($('.product-checkbox:checked').length > 0) {
                $('#bulk-assign-form').submit();
            }
        });
    });
</script>
<?php
include "assets/includes/footer.php";
?>