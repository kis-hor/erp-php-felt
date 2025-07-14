<?php
session_start();
include "config.php";
$title = 'Product Assignments';
include "assets/includes/header.php";
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">Product Assignments</h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">
                            <i class="fas fa-plus"></i> New Assignment
                        </button>
                    </div>
                </div>
            </div>

            <!-- Assignments Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Assignment ID</th>
                                            <th>PO Number</th>
                                            <th>Customer Name</th>
                                            <th>Product Photo</th>
                                            <th>Product Name</th>
                                            <th>Artisan Name</th>
                                            <th>Assigned Qty</th>
                                            <th>Wages/Piece</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT pa.*, sop.ProductName, sop.ProductImage, 
                                                      so.PONumber, so.CustomerName
                                               FROM production_assignments pa
                                               JOIN sales_order_products sop ON pa.SalesOrderProductID = sop.SalesOrderProductID
                                               JOIN sales_orders so ON sop.SalesOrderID = so.SalesOrderID
                                               WHERE pa.is_delete = 0
                                               ORDER BY pa.OrderID DESC";

                                        $result = mysqli_query($conn, $sql);

                                        if (!$result) {
                                            echo "Query error: " . mysqli_error($conn);
                                        } else {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                echo '<tr>';
                                                echo '<td>ASG-' . str_pad($row['OrderID'], 5, '0', STR_PAD_LEFT) . '</td>';
                                                echo '<td>' . htmlspecialchars($row['PONumber']) . '</td>';
                                                echo '<td>' . htmlspecialchars($row['CustomerName']) . '</td>';
                                                echo '<td>';
                                                if (!empty($row['ProductImage'])) {
                                                    $imgSrc = ltrim($row['ProductImage'], '/');
                                                    $imgSrc = dirname($imgSrc) . '/' . rawurlencode(basename($imgSrc));
                                                    echo '<img src="' . htmlspecialchars($imgSrc) . '" alt="Product" style="max-width:50px;">';
                                                }
                                                echo '</td>';
                                                echo '<td>' . htmlspecialchars($row['ProductName']) . '</td>';
                                                echo '<td>' . htmlspecialchars($row['ArtisanName']) . '</td>';
                                                echo '<td>' . $row['AssignedQuantity'] . '</td>';
                                                echo '<td>₹' . number_format($row['WagesPerUnit'], 2) . '</td>';
                                                echo '<td>' . date('d M Y', strtotime($row['ProductionDueDate'])) . '</td>';
                                                echo '<td><span class="badge bg-info">In Progress</span></td>';
                                                echo '<td>
                                                        <a href="view_production_details.php?id=' . $row['OrderID'] . '" 
                                                           class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                      </td>';
                                                echo '</tr>';
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Assignment Modal -->
<div class="modal fade" id="addAssignmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Product Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignmentForm" method="POST" action="process_assignment.php">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">PO Number</label>
                            <select class="form-select" name="sales_order_id" required>
                                <option value="">Select PO</option>
                                <?php
                                $pos = mysqli_query($conn, "SELECT DISTINCT so.SalesOrderID, so.PONumber 
                                                              FROM sales_orders so 
                                                              JOIN sales_order_products sop ON so.SalesOrderID = sop.SalesOrderID 
                                                              JOIN inventory_fulfillment inf ON sop.SalesOrderProductID = inf.SalesOrderProductID 
                                                              WHERE inf.SentToProduction = 1 
                                                              AND inf.PendingQuantity > 0");

                                if (!$pos) {
                                    echo "Query error: " . mysqli_error($conn);
                                } else {
                                    while ($po = mysqli_fetch_assoc($pos)) {
                                        echo '<option value="' . $po['SalesOrderID'] . '">' .
                                            htmlspecialchars($po['PONumber']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Artisan</label>
                            <select class="form-select" name="artisan_name" required>
                                <option value="">Select Artisan</option>
                                <?php
                                // Replace the artisans dropdown query
                                $artisans = mysqli_query($conn, "SELECT DISTINCT a.ArtisanName 
                                FROM artisans a 
                                LEFT JOIN production_assignments pa 
                                    ON a.ArtisanName = pa.ArtisanName 
                                WHERE a.is_delete = 0 
                                AND (
                                    pa.ArtisanName IS NULL 
                                    OR pa.Status != 'In Progress'
                                )
                                GROUP BY a.ArtisanName");

                                if (!$artisans) {
                                    echo "Query error: " . mysqli_error($conn);
                                } else {
                                    while ($artisan = mysqli_fetch_assoc($artisans)) {
                                        echo '<option value="' . htmlspecialchars($artisan['ArtisanName']) . '">' .
                                            htmlspecialchars($artisan['ArtisanName']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Product</label>
                            <select class="form-select" name="sales_order_product_id" required>
                                <option value="">Select Product</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div id="productImage" class="text-center mb-2"></div>
                            <input type="hidden" name="pending_quantity" id="pendingQuantity">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Pending Qty</label>
                            <div id="pendingQtyDisplay" class="form-control-plaintext">-</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Assign Qty</label>
                            <input type="number" class="form-control" name="quantity" min="1" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date" required
                                min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Wages Per Piece</label>
                            <input type="number" class="form-control" name="wages" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Create Assignment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add only jQuery before footer -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        // Product population code
        $('select[name="sales_order_id"]').on('change', function() {
            const salesOrderId = $(this).val();
            const productSelect = $('select[name="sales_order_product_id"]');

            if (salesOrderId) {
                // Clear previous values
                productSelect.html('<option value="">Select Product</option>');
                $('#productImage').empty();
                $('#pendingQtyDisplay').text('-');
                $('#pendingQuantity').val('');

                $.ajax({
                    url: 'get_products.php',
                    method: 'GET',
                    data: {
                        sales_order_id: salesOrderId
                    },
                    dataType: 'json',
                    success: function(data) {
                        data.forEach(function(product) {
                            productSelect.append($('<option>')
                                .val(product.SalesOrderProductID)
                                .data('pending', product.PendingQuantity)
                                .data('image', product.ProductImage)
                                .text(product.ProductName)
                            );
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        alert('Error loading products: ' + error);
                    }
                });
            }
        });

        // Handle product selection
        $('select[name="sales_order_product_id"]').on('change', function() {
            const selected = $(this).find('option:selected');
            const pendingQty = selected.data('pending');
            const productImage = selected.data('image');

            console.log('Selected product:', {
                pending: pendingQty,
                image: productImage
            }); // Debug log

            $('#pendingQtyDisplay').text(pendingQty || '-');
            $('#pendingQuantity').val(pendingQty);

            if (productImage) {
                $('#productImage').html(`<img src="${productImage}" alt="Product" style="max-height:50px;">`);
            } else {
                $('#productImage').empty();
            }

            // Update quantity input max value
            $('input[name="quantity"]').attr('max', pendingQty);
        });

        // Validate quantity on input
        $('input[name="quantity"]').on('input', function() {
            const max = parseInt($(this).attr('max'));
            const val = parseInt($(this).val());

            if (val > max) {
                alert('Quantity cannot exceed pending quantity: ' + max);
                $(this).val(max);
            }
        });

        // Handle form submission
        $('#assignmentForm').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();

            $.ajax({
                url: 'process_assignment.php',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        alert('Assignment created successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + (response.error || 'Unknown error occurred'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Form submission error:', error);
                    alert('Error submitting form: ' + error);
                }
            });
        });
    });
</script>

<?php include "assets/includes/footer.php"; ?>