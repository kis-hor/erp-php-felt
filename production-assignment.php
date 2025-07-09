<?php
session_start();
if (!isset($_SESSION['Username']) || !in_array($_SESSION['Role'], ['Admin', 'BusinessOperations'])) {
    header('Location: login');
    exit;
}
include "config.php";
$title = 'Pending Fulfillment Status';

if (isset($_POST['Send_To_Production'])) {
    $inventoryCheckIDs = isset($_POST['inventoryCheckIDs']) ? $_POST['inventoryCheckIDs'] : [];
    $remarks = isset($_POST['remarks']) ? array_map(function ($remark) use ($conn) {
        return mysqli_real_escape_string($conn, $remark);
    }, $_POST['remarks']) : [];
    $bulkRemark = isset($_POST['bulkRemark']) ? mysqli_real_escape_string($conn, $_POST['bulkRemark']) : '';

    mysqli_begin_transaction($conn);

    try {
        if (empty($inventoryCheckIDs)) {
            throw new Exception('No products selected.');
        }

        foreach ($inventoryCheckIDs as $inventoryCheckID) {
            $inventoryCheckID = mysqli_real_escape_string($conn, $inventoryCheckID);
            $remark = !empty($bulkRemark) ? $bulkRemark : (isset($remarks[$inventoryCheckID]) ? $remarks[$inventoryCheckID] : '');

            // Update inventory_checks
            $update_sql = "UPDATE inventory_checks SET Status = 'SentToProduction', Remarks = '$remark' WHERE InventoryCheckID = $inventoryCheckID AND is_delete = 0";
            if (!mysqli_query($conn, $update_sql)) {
                throw new Exception('Failed to update inventory_checks: ' . mysqli_error($conn));
            }

            // Fetch details for orders table
            $fetch_sql = "SELECT ic.SalesOrderProductID, ic.ProductName, ic.ProductDescription, ic.PendingQuantity, sop.SalesOrderID
                          FROM inventory_checks ic
                          JOIN sales_order_products sop ON ic.SalesOrderProductID = sop.SalesOrderProductID
                          WHERE ic.InventoryCheckID = $inventoryCheckID AND ic.is_delete = 0";
            $fetch_result = mysqli_query($conn, $fetch_sql);
            if (!$fetch_result || mysqli_num_rows($fetch_result) == 0) {
                throw new Exception('Failed to fetch inventory check details for ID: ' . $inventoryCheckID);
            }
            $row = mysqli_fetch_assoc($fetch_result);

            // Insert into orders table with Remarks
            $insert_sql = "INSERT INTO orders (InventoryCheckID, SalesOrderID, SalesOrderProductID, ProductName, ProductDescription, PendingQuantity, Status, ArtisanID, Remarks, CreatedAt, is_delete)
                           VALUES ($inventoryCheckID, {$row['SalesOrderID']}, {$row['SalesOrderProductID']}, '" . mysqli_real_escape_string($conn, $row['ProductName']) . "',
                                   '" . mysqli_real_escape_string($conn, $row['ProductDescription']) . "', {$row['PendingQuantity']}, 'SentToProduction', NULL, '$remark', NOW(), 0)";
            if (!mysqli_query($conn, $insert_sql)) {
                throw new Exception('Failed to insert into orders table: ' . mysqli_error($conn));
            }
        }

        mysqli_commit($conn);
        $_SESSION['success'] = 'Products sent to production successfully.';
        header("Location: production-assignment");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
        header("Location: production-assignment");
        exit;
    }
}
?>

<?php include "assets/includes/header.php"; ?>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Pending Fulfillment Status</h4>
                    </div>
                </div>
            </div>
            <?php
            if (isset($_SESSION['error']) && !empty($_SESSION['error'])) {
                echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success']) && !empty($_SESSION['success'])) {
                echo '<div class="alert alert-primary" role="alert">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
            ?>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title pb-3"><b>Pending Fulfillment Report</b></h5>
                            <form method="post" id="pendingFulfillmentForm">
                                <div class="table-responsive table-card">
                                    <table class="table table-hover table-nowrap align-middle mb-0">
                                        <thead>
                                            <tr class="text-muted text-uppercase">
                                                <th><input type="checkbox" id="selectAll"></th>
                                                <th>Order ID</th>
                                                <th>PO Number</th>
                                                <th>Customer Name</th>
                                                <th>Product Name</th>
                                                <th>Product Description</th>
                                                <th>Pending Quantity</th>
                                                <th>Product Photo</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT ic.InventoryCheckID, ic.ProductName, ic.ProductDescription, ic.PendingQuantity, ic.Remarks, so.SalesOrderID, so.PONumber, so.CustomerName, sop.ProductPhoto
                                                    FROM inventory_checks ic
                                                    JOIN sales_order_products sop ON ic.SalesOrderProductID = sop.SalesOrderProductID
                                                    JOIN sales_orders so ON sop.SalesOrderID = so.SalesOrderID
                                                    WHERE ic.Status = 'Pending' AND ic.is_delete = 0 AND sop.is_delete = 0 AND so.is_delete = 0";
                                            $result = mysqli_query($conn, $sql);
                                            if (!$result) {
                                                echo '<tr><td colspan="9" class="text-center">Database error: ' . mysqli_error($conn) . '</td></tr>';
                                            } else {
                                                $row_count = mysqli_num_rows($result);
                                                echo '<tr><td colspan="9" class="text-center">Found ' . $row_count . ' pending products.</td></tr>';
                                                if ($row_count == 0) {
                                                    echo '<tr><td colspan="9" class="text-center">No pending products found for assignment.</td></tr>';
                                                } else {
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        $order_id = 'ORD-' . sprintf('%03d', $row['SalesOrderID']);
                                                        $photo = !empty($row['ProductPhoto']) ? htmlspecialchars($row['ProductPhoto']) : '';
                                                        echo '<tr>';
                                                        echo '<td><input type="checkbox" name="inventoryCheckIDs[]" value="' . $row['InventoryCheckID'] . '" class="product-checkbox"></td>';
                                                        echo '<td>' . htmlspecialchars($order_id) . '</td>';
                                                        echo '<td>' . htmlspecialchars($row['PONumber']) . '</td>';
                                                        echo '<td>' . htmlspecialchars($row['CustomerName']) . '</td>';
                                                        echo '<td>' . htmlspecialchars($row['ProductName']) . '</td>';
                                                        echo '<td>' . htmlspecialchars($row['ProductDescription']) . '</td>';
                                                        echo '<td>' . $row['PendingQuantity'] . '</td>';
                                                        echo '<td>' . ($photo ? '<img src="' . $photo . '" width="50" alt="Product" onerror="this.src=\'/images/placeholder.jpg\'">' : 'No Photo') . '</td>';
                                                        echo '<td><input type="text" name="remarks[' . $row['InventoryCheckID'] . ']" class="form-control remark-input" value="' . (isset($row['Remarks']) ? htmlspecialchars($row['Remarks']) : '') . '"></td>';
                                                        echo '</tr>';
                                                    }
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkRemarkModal" id="sendToProductionBtn" disabled>Send to Production</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Remark Modal -->
            <div class="modal fade" id="bulkRemarkModal" tabindex="-1" aria-labelledby="bulkRemarkModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="bulkRemarkModalLabel">Send to Production</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="post" id="bulkRemarkForm">
                                <input type="hidden" name="inventoryCheckIDs[]" id="bulkInventoryCheckIDs">
                                <div class="mb-3">
                                    <label for="bulkRemark" class="form-label">Bulk Remark (optional, applies to all selected if provided):</label>
                                    <input type="text" name="bulkRemark" id="bulkRemark" class="form-control" placeholder="Enter remark for all selected products">
                                </div>
                                <p>If no bulk remark is provided, individual remarks will be used.</p>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" name="Send_To_Production">Confirm Send to Production</button>
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
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    console.log('production-assignment.js script started');
    // Fallback for jQuery


    (function($) {
        $(document).ready(function() {
            console.log('Document ready executed');
            console.log('Found ' + $('.product-checkbox').length + ' product checkboxes');

            // Select All checkbox
            $(document).on('change', '#selectAll', function() {
                console.log('Select All changed: ' + this.checked);
                $('.product-checkbox').prop('checked', this.checked).trigger('change');
                toggleSendButton();
            });

            // Individual checkbox
            $(document).on('change', '.product-checkbox', function() {
                console.log('Checkbox changed: ' + $(this).val() + ', checked: ' + this.checked);
                const allChecked = $('.product-checkbox').length === $('.product-checkbox:checked').length;
                $('#selectAll').prop('checked', allChecked);
                toggleSendButton();
            });

            // Enable/disable Send to Production button
            function toggleSendButton() {
                const anyChecked = $('.product-checkbox:checked').length > 0;
                console.log('Toggle Send Button: anyChecked = ' + anyChecked);
                $('#sendToProductionBtn').prop('disabled', !anyChecked);
            }

            // Initialize button state
            toggleSendButton();

            // Populate hidden input for modal
            $(document).on('show.bs.modal', '#bulkRemarkModal', function() {
                const selectedIDs = $('.product-checkbox:checked').map(function() {
                    return this.value;
                }).get();
                console.log('Selected IDs for modal: ' + selectedIDs.join(','));
                $('#bulkInventoryCheckIDs').val(selectedIDs.join(','));
                // Copy individual remarks
                $('#bulkRemarkForm').find('input[name^="remarks"]').remove(); // Clear previous remarks
                $('.product-checkbox:checked').each(function() {
                    const inventoryCheckID = $(this).val();
                    const remark = $(this).closest('tr').find('.remark-input').val();
                    console.log('Adding remark for ID ' + inventoryCheckID + ': ' + remark);
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'remarks[' + inventoryCheckID + ']',
                        value: remark
                    }).appendTo('#bulkRemarkForm');
                });
            });

            // Form submission
            $(document).on('submit', '#bulkRemarkForm', function(e) {
                const selectedIDs = $('.product-checkbox:checked').map(function() {
                    return this.value;
                }).get();
                console.log('Form submitting with IDs: ' + selectedIDs.join(','));
                if (selectedIDs.length === 0) {
                    console.warn('No products selected');
                    alert('Please select at least one product.');
                    e.preventDefault();
                    return false;
                }
                $(this).find('button[type="submit"]').prop('disabled', true).html('Submitting...');
                selectedIDs.forEach(function(id) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'inventoryCheckIDs[]',
                        value: id
                    }).appendTo('#bulkRemarkForm');
                });
            });
        });
    })(jQuery);
</script>

<?php include "assets/includes/footer.php"; ?>