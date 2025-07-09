<?php
session_start();
if (!isset($_SESSION['Username']) || !in_array($_SESSION['Role'], ['Admin', 'BusinessOperations'])) {
    header('Location: login');
    exit;
}
include "config.php";
$title = 'Inventory Check';

if (isset($_POST['Record_Fulfillment'])) {
    $salesOrderID = mysqli_real_escape_string($conn, $_POST['salesOrderID']);
    $salesOrderProductIDs = $_POST['salesOrderProductID'];
    $productNames = $_POST['productName'];
    $productDescriptions = $_POST['productDescription'];
    $orderedQuantities = $_POST['orderedQuantity'];
    $availableQuantities = $_POST['availableQuantity'];
    $productIDs = $_POST['productID'];
    $checkedBy = $_SESSION['UserID'];

    mysqli_begin_transaction($conn);

    try {
        for ($i = 0; $i < count($salesOrderProductIDs); $i++) {
            $salesOrderProductID = mysqli_real_escape_string($conn, $salesOrderProductIDs[$i]);
            $productName = mysqli_real_escape_string($conn, $productNames[$i]);
            $productDescription = mysqli_real_escape_string($conn, $productDescriptions[$i]);
            $orderedQuantity = (int)$orderedQuantities[$i];
            $availableQuantity = (int)$availableQuantities[$i];
            $pendingQuantity = $orderedQuantity - $availableQuantity;
            $productID = mysqli_real_escape_string($conn, $productIDs[$i]);

            if ($pendingQuantity < 0) {
                throw new Exception('Available quantity cannot exceed ordered quantity for product: ' . $productName);
            }

            // Check if record exists
            $check_sql = "SELECT InventoryCheckID, Status FROM inventory_checks WHERE SalesOrderProductID = $salesOrderProductID AND is_delete = 0";
            $check_result = mysqli_query($conn, $check_sql);
            if (!$check_result) {
                throw new Exception('Error checking existing record: ' . mysqli_error($conn));
            }

            if (mysqli_num_rows($check_result) > 0) {
                throw new Exception('Inventory check already recorded for product: ' . $productName . '. Use Edit to modify.');
            }

            // Insert new record
            $sql = "INSERT INTO inventory_checks (SalesOrderProductID, ProductName, ProductDescription, OrderedQuantity, AvailableQuantity, PendingQuantity, CheckedBy, CheckedAt, Status, is_delete)
                    VALUES ($salesOrderProductID, '$productName', '$productDescription', $orderedQuantity, $availableQuantity, $pendingQuantity, $checkedBy, NOW(), 'Pending', 0)";
            if (!mysqli_query($conn, $sql)) {
                throw new Exception('Failed to record inventory check for product: ' . $productName . ' - ' . mysqli_error($conn));
            }
        }

        mysqli_commit($conn);
        $_SESSION['success'] = 'Inventory Check Recorded Successfully.';
        header("Location: inventory-check");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
        header("Location: inventory-check");
        exit;
    }
}

if (isset($_POST['Edit_Fulfillment'])) {
    $salesOrderID = mysqli_real_escape_string($conn, $_POST['salesOrderID']);
    $salesOrderProductIDs = $_POST['salesOrderProductID'];
    $productNames = $_POST['productName'];
    $productDescriptions = $_POST['productDescription'];
    $orderedQuantities = $_POST['orderedQuantity'];
    $availableQuantities = $_POST['availableQuantity'];
    $productIDs = $_POST['productID'];
    $checkedBy = $_SESSION['UserID'];

    mysqli_begin_transaction($conn);

    try {
        for ($i = 0; $i < count($salesOrderProductIDs); $i++) {
            $salesOrderProductID = mysqli_real_escape_string($conn, $salesOrderProductIDs[$i]);
            $productName = mysqli_real_escape_string($conn, $productNames[$i]);
            $productDescription = mysqli_real_escape_string($conn, $productDescriptions[$i]);
            $orderedQuantity = (int)$orderedQuantities[$i];
            $availableQuantity = (int)$availableQuantities[$i];
            $pendingQuantity = $orderedQuantity - $availableQuantity;
            $productID = mysqli_real_escape_string($conn, $productIDs[$i]);

            if ($pendingQuantity < 0) {
                throw new Exception('Available quantity cannot exceed ordered quantity for product: ' . $productName);
            }

            // Update existing record
            $sql = "UPDATE inventory_checks 
                    SET ProductName = '$productName', ProductDescription = '$productDescription', 
                        OrderedQuantity = $orderedQuantity, AvailableQuantity = $availableQuantity, 
                        PendingQuantity = $pendingQuantity, CheckedBy = $checkedBy, 
                        CheckedAt = NOW(), Status = 'Pending', is_delete = 0
                    WHERE SalesOrderProductID = $salesOrderProductID AND is_delete = 0";
            if (!mysqli_query($conn, $sql)) {
                throw new Exception('Failed to update inventory check for product: ' . $productName . ' - ' . mysqli_error($conn));
            }
        }

        mysqli_commit($conn);
        $_SESSION['success'] = 'Inventory Check Updated Successfully.';
        header("Location: inventory-check");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
        header("Location: inventory-check");
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
                        <h4 class="mb-sm-0">Inventory Check</h4>
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
                            <h5 class="card-title pb-3"><b>Inventory Check Dashboard</b></h5>
                            <div class="table-responsive table-card">
                                <table class="table table-hover table-nowrap align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted text-uppercase">
                                            <th>PO Number</th>
                                            <th>Customer Name</th>
                                            <th>Order Placed By</th>
                                            <th>Total Ordered Quantity</th>
                                            <th>Fulfilled from Inventory</th>
                                            <th>Pending Quantity</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT so.SalesOrderID, so.PONumber, so.CustomerName, u.Username AS Salesperson,
                                                SUM(sop.OrderedQuantity) AS TotalOrderedQuantity,
                                                SUM(COALESCE(ic.AvailableQuantity, 0)) AS FulfilledQuantity,
                                                SUM(COALESCE(ic.PendingQuantity, sop.OrderedQuantity)) AS PendingQuantity,
                                                COUNT(ic.InventoryCheckID) AS record_count
                                                FROM sales_orders so
                                                JOIN users u ON so.CreatedBy = u.UserID
                                                JOIN sales_order_products sop ON so.SalesOrderID = sop.SalesOrderID
                                                LEFT JOIN inventory_checks ic ON sop.SalesOrderProductID = ic.SalesOrderProductID AND ic.is_delete = 0
                                                WHERE so.is_delete = 0 AND sop.is_delete = 0
                                                GROUP BY so.SalesOrderID";
                                        $result = mysqli_query($conn, $sql);
                                        if (!$result) {
                                            echo '<tr><td colspan="8" class="text-center">Database error: ' . mysqli_error($conn) . '</td></tr>';
                                            exit;
                                        }
                                        $po_count = mysqli_num_rows($result);
                                        $modal_data = [];

                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $check_sql = "SELECT COUNT(*) AS total_products,
                                                         SUM(CASE WHEN ic.Status = 'Pending' THEN 1 ELSE 0 END) AS pending_count,
                                                         SUM(CASE WHEN ic.Status = 'SentToProduction' THEN 1 ELSE 0 END) AS sent_count
                                                  FROM sales_order_products sop
                                                  LEFT JOIN inventory_checks ic ON sop.SalesOrderProductID = ic.SalesOrderProductID AND ic.is_delete = 0
                                                  WHERE sop.SalesOrderID = {$row['SalesOrderID']} AND sop.is_delete = 0";
                                            $check_result = mysqli_query($conn, $check_sql);
                                            if (!$check_result) {
                                                echo '<tr><td colspan="8" class="text-center">Database error: ' . mysqli_error($conn) . '</td></tr>';
                                                exit;
                                            }
                                            $check_data = mysqli_fetch_assoc($check_result);
                                            $total_products = $check_data['total_products'];
                                            $pending_count = $check_data['pending_count'];
                                            $sent_count = $check_data['sent_count'];

                                            if ($row['record_count'] == 0) {
                                                $status = 'Not Recorded';
                                                $status_class = 'bg-secondary-subtle text-secondary';
                                                $action_button = '<button class="btn btn-primary btn-sm view-fulfill-btn" data-bs-toggle="modal" data-bs-target="#fulfillmentModal-' . $row['SalesOrderID'] . '">View and Fulfill</button>';
                                            } elseif ($pending_count > 0) {
                                                $status = 'Pending';
                                                $status_class = 'bg-warning-subtle text-warning';
                                                $action_button = '<button class="btn btn-warning btn-sm edit-fulfill-btn" data-bs-toggle="modal" data-bs-target="#editModal-' . $row['SalesOrderID'] . '">Edit</button>';
                                            } else {
                                                $status = 'Sent to Production';
                                                $status_class = 'bg-success-subtle text-success';
                                                $action_button = ''; // No action for fully sent orders
                                            }

                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($row['PONumber']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['CustomerName']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['Salesperson']) . '</td>';
                                            echo '<td>' . $row['TotalOrderedQuantity'] . '</td>';
                                            echo '<td>' . $row['FulfilledQuantity'] . '</td>';
                                            echo '<td>' . $row['PendingQuantity'] . '</td>';
                                            echo '<td><span class="badge ' . $status_class . '">' . $status . '</span></td>';
                                            echo '<td>' . $action_button . '</td>';
                                            echo '</tr>';

                                            $modal_data[$row['SalesOrderID']] = [
                                                'PONumber' => $row['PONumber'],
                                                'CustomerName' => $row['CustomerName'],
                                                'products' => []
                                            ];
                                        }

                                        if ($po_count == 0) {
                                            echo '<tr><td colspan="8" class="text-center">No purchase orders found. Check sales_orders and sales_order_products for data.</td></tr>';
                                        } else {
                                            echo '<tr><td colspan="8" class="text-center">Found ' . $po_count . ' purchase orders.</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fulfillment Modals for each Sales Order -->
            <?php
            $sql = "SELECT so.SalesOrderID, so.PONumber, so.CustomerName, sop.SalesOrderProductID, sop.ProductName, sop.ProductDescription, sop.ProductPhoto, sop.OrderedQuantity,
                    ic.AvailableQuantity, ic.PendingQuantity, ic.Status
                    FROM sales_orders so
                    JOIN sales_order_products sop ON so.SalesOrderID = sop.SalesOrderID
                    LEFT JOIN inventory_checks ic ON sop.SalesOrderProductID = ic.SalesOrderProductID AND ic.is_delete = 0
                    WHERE so.is_delete = 0 AND sop.is_delete = 0";
            $result = mysqli_query($conn, $sql);
            if (!$result) {
                echo '<div class="alert alert-danger">Modal data query failed: ' . mysqli_error($conn) . '</div>';
            } else {
                while ($row = mysqli_fetch_assoc($result)) {
                    if (isset($modal_data[$row['SalesOrderID']])) {
                        $modal_data[$row['SalesOrderID']]['products'][] = [
                            'SalesOrderProductID' => $row['SalesOrderProductID'],
                            'ProductName' => $row['ProductName'],
                            'ProductDescription' => $row['ProductDescription'],
                            'ProductPhoto' => $row['ProductPhoto'],
                            'OrderedQuantity' => $row['OrderedQuantity'],
                            'AvailableQuantity' => $row['AvailableQuantity'] ?? 0,
                            'PendingQuantity' => $row['PendingQuantity'] ?? $row['OrderedQuantity'],
                            'Status' => $row['Status']
                        ];
                    }
                }

                foreach ($modal_data as $salesOrderID => $data) {
                    echo "<!-- Fulfillment Modal for SalesOrderID: $salesOrderID -->";
            ?>
                    <div class="modal fade" id="fulfillmentModal-<?php echo $salesOrderID; ?>" tabindex="-1" aria-labelledby="fulfillmentModalLabel-<?php echo $salesOrderID; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="fulfillmentModalLabel-<?php echo $salesOrderID; ?>">Inventory Check for PO: <?php echo htmlspecialchars($data['PONumber']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="post">
                                        <input type="hidden" name="salesOrderID" value="<?php echo $salesOrderID; ?>">
                                        <div class="mb-3">
                                            <label>Customer Name:</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['CustomerName']); ?>" readonly>
                                        </div>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Product Photo</th>
                                                    <th>Product Name</th>
                                                    <th>Product Description</th>
                                                    <th>Ordered Quantity</th>
                                                    <th>Available in Inventory</th>
                                                    <th>Pending Quantity</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($data['products'] as $product) {
                                                    if ($product['Status'] === 'Pending' || $product['Status'] === 'SentToProduction') {
                                                        continue; // Skip recorded products
                                                    }
                                                    $product_sql = "SELECT ProductID, ProductName FROM products WHERE ProductName LIKE '%" . mysqli_real_escape_string($conn, $product['ProductName']) . "%' LIMIT 1";
                                                    $product_result = mysqli_query($conn, $product_sql);
                                                    if (!$product_result) {
                                                        echo '<tr><td colspan="7">Product query error: ' . mysqli_error($conn) . '</td></tr>';
                                                        continue;
                                                    }
                                                    $product_data = mysqli_fetch_assoc($product_result);
                                                ?>
                                                    <tr>
                                                        <input type="hidden" name="salesOrderProductID[]" value="<?php echo $product['SalesOrderProductID']; ?>">
                                                        <input type="hidden" name="productName[]" value="<?php echo htmlspecialchars($product['ProductName']); ?>">
                                                        <input type="hidden" name="productID[]" value="<?php echo $product_data['ProductID'] ?? ''; ?>">
                                                        <td><?php echo $product['ProductPhoto'] ? '<img src="' . htmlspecialchars($product['ProductPhoto']) . '" alt="Product" width="50">' : 'No Photo'; ?></td>
                                                        <td><input type="text" class="form-control" value="<?php echo htmlspecialchars($product['ProductName']); ?>" readonly></td>
                                                        <td><textarea name="productDescription[]" readonly class="form-control"><?php echo htmlspecialchars($product['ProductDescription']); ?></textarea></td>
                                                        <td><input type="number" name="orderedQuantity[]" value="<?php echo $product['OrderedQuantity']; ?>" readonly class="form-control"></td>
                                                        <td><input type="number" name="availableQuantity[]" class="form-control available-quantity" data-ordered="<?php echo $product['OrderedQuantity']; ?>" value="<?php echo $product['AvailableQuantity']; ?>" min="0" required></td>
                                                        <td><input type="number" class="form-control pending-quantity" value="<?php echo $product['PendingQuantity']; ?>" readonly></td>
                                                        <td><span class="badge bg-secondary-subtle text-secondary">Not Recorded</span></td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <?php
                                            $has_unrecorded = false;
                                            foreach ($data['products'] as $product) {
                                                if ($product['Status'] !== 'Pending' && $product['Status'] !== 'SentToProduction') {
                                                    $has_unrecorded = true;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <button type="submit" class="btn btn-primary" name="Record_Fulfillment" <?php echo !$has_unrecorded ? 'disabled' : ''; ?>>Record Fulfillment Status</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php
                }
            }
            ?>

            <!-- Edit Modals for each Sales Order -->
            <?php
            foreach ($modal_data as $salesOrderID => $data) {
                $has_pending = false;
                foreach ($data['products'] as $product) {
                    if ($product['Status'] === 'Pending') {
                        $has_pending = true;
                        break;
                    }
                }
                if (!$has_pending) {
                    continue;
                }
                echo "<!-- Edit Modal for SalesOrderID: $salesOrderID -->";
            ?>
                <div class="modal fade" id="editModal-<?php echo $salesOrderID; ?>" tabindex="-1" aria-labelledby="editModalLabel-<?php echo $salesOrderID; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel-<?php echo $salesOrderID; ?>">Edit Inventory Check for PO: <?php echo htmlspecialchars($data['PONumber']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="post">
                                    <input type="hidden" name="salesOrderID" value="<?php echo $salesOrderID; ?>">
                                    <div class="mb-3">
                                        <label>Customer Name:</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['CustomerName']); ?>" readonly>
                                    </div>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Product Photo</th>
                                                <th>Product Name</th>
                                                <th>Product Description</th>
                                                <th>Ordered Quantity</th>
                                                <th>Available in Inventory</th>
                                                <th>Pending Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($data['products'] as $product) {
                                                if ($product['Status'] !== 'Pending') {
                                                    continue;
                                                }
                                                $product_sql = "SELECT ProductID, ProductName FROM products WHERE ProductName LIKE '%" . mysqli_real_escape_string($conn, $product['ProductName']) . "%' LIMIT 1";
                                                $product_result = mysqli_query($conn, $product_sql);
                                                if (!$product_result) {
                                                    echo '<tr><td colspan="6">Product query error: ' . mysqli_error($conn) . '</td></tr>';
                                                    continue;
                                                }
                                                $product_data = mysqli_fetch_assoc($product_result);
                                            ?>
                                                <tr>
                                                    <input type="hidden" name="salesOrderProductID[]" value="<?php echo $product['SalesOrderProductID']; ?>">
                                                    <input type="hidden" name="productName[]" value="<?php echo htmlspecialchars($product['ProductName']); ?>">
                                                    <input type="hidden" name="productID[]" value="<?php echo $product_data['ProductID'] ?? ''; ?>">
                                                    <td><?php echo $product['ProductPhoto'] ? '<img src="' . htmlspecialchars($product['ProductPhoto']) . '" alt="Product" width="50">' : 'No Photo'; ?></td>
                                                    <td><input type="text" class="form-control" value="<?php echo htmlspecialchars($product['ProductName']); ?>" readonly></td>
                                                    <td><textarea name="productDescription[]" readonly class="form-control"><?php echo htmlspecialchars($product['ProductDescription']); ?></textarea></td>
                                                    <td><input type="number" name="orderedQuantity[]" value="<?php echo $product['OrderedQuantity']; ?>" readonly class="form-control"></td>
                                                    <td><input type="number" name="availableQuantity[]" class="form-control available-quantity" data-ordered="<?php echo $product['OrderedQuantity']; ?>" value="<?php echo $product['AvailableQuantity']; ?>" min="0" required></td>
                                                    <td><input type="number" class="form-control pending-quantity" value="<?php echo $product['PendingQuantity']; ?>" readonly></td>
                                                </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary" name="Edit_Fulfillment">Update Fulfillment Status</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>

<script>
    console.log('inventory-check.js script started');
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded');
    } else {
        console.log('jQuery version: ' + jQuery.fn.jquery);
    }
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap is not loaded');
    } else {
        console.log('Bootstrap is available');
    }

    (function($) {
        $(document).ready(function() {
            console.log('Document ready executed');
            $('.modal').each(function() {
                console.log('Modal found with ID: ' + $(this).attr('id'));
            });

            $('.available-quantity').on('input', function() {
                var ordered = parseInt($(this).data('ordered'));
                var available = parseInt($(this).val()) || 0;
                if (available > ordered) {
                    this.setCustomValidity('Available quantity cannot exceed ordered quantity (' + ordered + ').');
                    this.reportValidity();
                } else {
                    this.setCustomValidity('');
                    var $row = $(this).closest('tr');
                    var pending = ordered - available;
                    $row.find('.pending-quantity').val(pending >= 0 ? pending : 0);
                }
            });

            $('form').on('submit', function() {
                var submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).html('Submitting...');
            });
        });
    })(jQuery);
</script>

<?php include "assets/includes/footer.php"; ?>