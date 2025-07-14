<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "config.php";
$title = 'Bulk Assign to Artisans';
include "assets/includes/header.php";

$sales_order_product_ids = isset($_GET['sales_order_product_ids']) ? array_map('intval', $_GET['sales_order_product_ids']) : [];

// Fetch product details for selected IDs
$products = [];
if (!empty($sales_order_product_ids)) {
    $placeholders = implode(',', array_fill(0, count($sales_order_product_ids), '?'));
    $sql = "SELECT sop.SalesOrderProductID, so.PONumber, sop.ProductName, 
                   sop.OrderedQuantity, invf.AvailableQuantity, 
                   (sop.OrderedQuantity - invf.AvailableQuantity) AS PendingQuantity
            FROM sales_order_products sop
            JOIN sales_orders so ON sop.SalesOrderID = so.SalesOrderID
            JOIN inventory_fulfillment invf ON sop.SalesOrderProductID = invf.SalesOrderProductID
            WHERE sop.SalesOrderProductID IN ($placeholders) 
            AND sop.OrderedQuantity > invf.AvailableQuantity";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        $_SESSION['error'] = 'Database error: ' . mysqli_error($conn);
        header('Location: pending_production.php');
        exit;
    }
    mysqli_stmt_bind_param($stmt, str_repeat('i', count($sales_order_product_ids)), ...$sales_order_product_ids);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    mysqli_stmt_close($stmt);
}

if (empty($products)) {
    $_SESSION['error'] = 'No valid products selected.';
    header('Location: pending_production.php');
    exit;
}

// Fetch artisans for dropdown
$artisan_sql = "SELECT ArtisanName FROM artisans WHERE is_delete = 0";
$artisan_result = mysqli_query($conn, $artisan_sql);
if (!$artisan_result) {
    $_SESSION['error'] = 'Failed to fetch artisans: ' . mysqli_error($conn);
    header('Location: pending_production.php');
    exit;
}
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Bulk Assign to Artisans</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="pending_production.php">Pending Production</a></li>
                                <li class="breadcrumb-item active"><i class="las la-angle-right"></i>Bulk Assign to Artisans</li>
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
                            <h5 class="card-title pb-3"><b>Bulk Assign Production</b></h5>
                            <form method="POST" action="insert_production_assignment.php" id="bulkAssignForm">
                                <?php foreach ($products as $index => $product) { ?>
                                    <input type="hidden" name="products[<?php echo $index; ?>][sales_order_product_id]" value="<?php echo $product['SalesOrderProductID']; ?>">
                                    <h6 class="mb-3">Product: <?php echo htmlspecialchars($product['ProductName']); ?> (PO: <?php echo htmlspecialchars($product['PONumber']); ?>)</h6>
                                    <div class="mb-3">
                                        <label class="form-label">Pending Quantity</label>
                                        <input type="number" class="form-control" value="<?php echo $product['PendingQuantity']; ?>" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="wages_per_piece_<?php echo $index; ?>" class="form-label">Wages Per Piece</label>
                                        <input type="number" step="0.01" class="form-control" name="products[<?php echo $index; ?>][wages_per_piece]" min="0" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="production_due_date_<?php echo $index; ?>" class="form-label">Production Due Date</label>
                                        <input type="date" class="form-control" name="products[<?php echo $index; ?>][production_due_date]" required>
                                    </div>
                                    <div class="artisan-repeater" data-product-index="<?php echo $index; ?>">
                                        <div class="artisan-item mb-3 border p-3">
                                            <div class="mb-3">
                                                <label for="artisan_name_<?php echo $index; ?>_0" class="form-label">Artisan Name</label>
                                                <select class="form-select" name="products[<?php echo $index; ?>][artisans][0][artisan_name]" required>
                                                    <option value="" disabled selected>Select Artisan</option>
                                                    <?php
                                                    mysqli_data_seek($artisan_result, 0);
                                                    while ($artisan = mysqli_fetch_assoc($artisan_result)) {
                                                        echo '<option value="' . htmlspecialchars($artisan['ArtisanName']) . '">' . htmlspecialchars($artisan['ArtisanName']) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="assigned_quantity_<?php echo $index; ?>_0" class="form-label">Assigned Quantity</label>
                                                <input type="number" class="form-control assigned-quantity" name="products[<?php echo $index; ?>][artisans][0][assigned_quantity]" min="1" max="<?php echo $product['PendingQuantity']; ?>" required>
                                            </div>
                                            <button type="button" class="btn btn-danger remove-artisan">Remove</button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-secondary mb-3 add-artisan" data-product-index="<?php echo $index; ?>">Add Artisan</button>
                                    <div class="mb-3">
                                        <label class="form-label">Total Assigned Quantity</label>
                                        <input type="number" class="form-control total-assigned-quantity" data-product-index="<?php echo $index; ?>" value="0" disabled>
                                    </div>
                                <?php } ?>
                                <div class="hstack gap-2 justify-content-end">
                                    <a href="pending_production.php" class="btn btn-light">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Assign</button>
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
        <?php foreach ($products as $index => $product) { ?>
            let artisanIndex_<?php echo $index; ?> = 1;
            const maxPendingQuantity_<?php echo $index; ?> = <?php echo $product['PendingQuantity']; ?>;

            function updateTotalAssigned_<?php echo $index; ?>() {
                let total = 0;
                $('.artisan-repeater[data-product-index="<?php echo $index; ?>"] .assigned-quantity').each(function() {
                    total += parseInt($(this).val()) || 0;
                });
                $('.total-assigned-quantity[data-product-index="<?php echo $index; ?>"]').val(total);
                if (total > maxPendingQuantity_<?php echo $index; ?>) {
                    $('.total-assigned-quantity[data-product-index="<?php echo $index; ?>"]').addClass('is-invalid');
                    $('.btn-primary[type="submit"]').prop('disabled', true);
                } else {
                    $('.total-assigned-quantity[data-product-index="<?php echo $index; ?>"]').removeClass('is-invalid');
                    $('.btn-primary[type="submit"]').prop('disabled', false);
                }
            }

            $('.add-artisan[data-product-index="<?php echo $index; ?>"]').click(function() {
                const artisanHtml = `
                <div class="artisan-item mb-3 border p-3">
                    <div class="mb-3">
                        <label for="artisan_name_<?php echo $index; ?>_${artisanIndex_<?php echo $index; ?>}" class="form-label">Artisan Name</label>
                        <select class="form-select" name="products[<?php echo $index; ?>][artisans][${artisanIndex_<?php echo $index; ?>}][artisan_name]" required>
                            <option value="" disabled selected>Select Artisan</option>
                            <?php
                            mysqli_data_seek($artisan_result, 0);
                            while ($artisan = mysqli_fetch_assoc($artisan_result)) {
                                echo '<option value="' . htmlspecialchars($artisan['ArtisanName']) . '">' . htmlspecialchars($artisan['ArtisanName']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="assigned_quantity_<?php echo $index; ?>_${artisanIndex_<?php echo $index; ?>}" class="form-label">Assigned Quantity</label>
                        <input type="number" class="form-control assigned-quantity" name="products[<?php echo $index; ?>][artisans][${artisanIndex_<?php echo $index; ?>}][assigned_quantity]" min="1" max="<?php echo $product['PendingQuantity']; ?>" required>
                    </div>
                    <button type="button" class="btn btn-danger remove-artisan">Remove</button>
                </div>`;
                $('.artisan-repeater[data-product-index="<?php echo $index; ?>"]').append(artisanHtml);
                artisanIndex_<?php echo $index; ?>++;
                updateTotalAssigned_<?php echo $index; ?>();
            });

            $(document).on('click', '.artisan-repeater[data-product-index="<?php echo $index; ?>"] .remove-artisan', function() {
                if ($('.artisan-repeater[data-product-index="<?php echo $index; ?>"] .artisan-item').length > 1) {
                    $(this).closest('.artisan-item').remove();
                    updateTotalAssigned_<?php echo $index; ?>();
                }
            });

            $(document).on('input', '.artisan-repeater[data-product-index="<?php echo $index; ?>"] .assigned-quantity', updateTotalAssigned_<?php echo $index; ?>);

            updateTotalAssigned_<?php echo $index; ?>();
        <?php } ?>
    });
</script>
<?php
include "assets/includes/footer.php";
?>