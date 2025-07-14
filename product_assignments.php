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
                                        // Change the query to use production_assignments table
                                        $sql = "SELECT pa.*, sop.ProductName, sop.ProductImage, 
                                                      so.PONumber, so.CustomerName 
                                               FROM production_assignments pa
                                               JOIN sales_order_products sop ON pa.SalesOrderProductID = sop.SalesOrderProductID
                                               JOIN sales_orders so ON sop.SalesOrderID = so.SalesOrderID
                                               WHERE pa.is_delete = 0
                                               ORDER BY pa.OrderID DESC";

                                        $result = mysqli_query($conn, $sql);
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<tr>';
                                            echo '<td>ASG-' . str_pad($row['OrderID'], 5, '0', STR_PAD_LEFT) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['PONumber']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['CustomerName']) . '</td>';
                                            echo '<td>';
                                            if (!empty($row['ProductImage'])) {
                                                $imgSrc = ltrim($row['ProductImage'], '/');
                                                $imgSrc = preg_replace('/^Uploads\//', 'Uploads/', $imgSrc);
                                                $imgSrc = dirname($imgSrc) . '/' . rawurlencode(basename($imgSrc));
                                                echo '<img src="' . htmlspecialchars($imgSrc) . '" alt="Product" style="max-width:50px;">';
                                            }
                                            echo '</td>';
                                            echo '<td>' . htmlspecialchars($row['ProductName']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['ArtisanName']) . '</td>';
                                            echo '<td>' . $row['AssignedQuantity'] . '</td>';
                                            echo '<td>₹' . number_format($row['WagesPerPiece'], 2) . '</td>';
                                            echo '<td>' . date('d M Y', strtotime($row['ProductionDueDate'])) . '</td>';

                                            $statusClass = 'warning';
                                            if ($row['Status'] == 'Approved') $statusClass = 'success';
                                            elseif ($row['Status'] == 'In Progress') $statusClass = 'info';

                                            echo '<td><span class="badge bg-info">In Progress</span></td>';
                                            echo '<td>
                                                    <a href="view_production_details.php?id=' . $row['OrderID'] . '" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                  </td>';
                                            echo '</tr>';
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
                                                          WHERE sop.Status != 'Completed'");
                                while ($po = mysqli_fetch_assoc($pos)) {
                                    echo '<option value="' . $po['SalesOrderID'] . '">' .
                                        htmlspecialchars($po['PONumber']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Artisan</label>
                            <select class="form-select" name="artisan_name" required>
                                <option value="">Select Artisan</option>
                                <?php
                                $artisans = mysqli_query($conn, "SELECT DISTINCT ArtisanName 
                                FROM production_assignments 
                                WHERE is_delete = 0");
                                while ($artisan = mysqli_fetch_assoc($artisans)) {
                                    echo '<option value="' . htmlspecialchars($artisan['ArtisanName']) . '">' .
                                        htmlspecialchars($artisan['ArtisanName']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Product</label>
                            <select class="form-select" name="sales_order_product_id" required>
                                <option value="">Select Product</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date" required
                                min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="quantity" min="1" required>
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
                $.getJSON('get_products.php?sales_order_id=' + salesOrderId, function(data) {
                    productSelect.html('<option value="">Select Product</option>');
                    $.each(data, function(index, product) {
                        productSelect.append($('<option>')
                            .val(product.SalesOrderProductID)
                            .text(product.ProductName));
                    });
                });
            }
        });
    });
</script>

<?php include "assets/includes/footer.php"; ?>