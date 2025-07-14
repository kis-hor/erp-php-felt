<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['Username'])) {
    header('Location: login');
    exit;
}
include "config.php";
$title = 'Add Sales Order';
include "assets/includes/header.php";
?>

<!-- Start right Content here -->
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Add Sales Order</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item active"><i class="las la-angle-right"></i>Add Sales Order</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->

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
                            <form action="insert_sales_order.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="po_number" class="form-label">PO Number</label>
                                    <input type="text" class="form-control" id="po_number" name="po_number" required>
                                </div>
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label">Customer Name</label>
                                    <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="salesperson_name" class="form-label">Salesperson Name</label>
                                    <input type="text" class="form-control" id="salesperson_name" name="salesperson_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="order_date" class="form-label">Order Date</label>
                                    <input type="date" class="form-control" id="order_date" name="order_date" required>
                                </div>
                                <div class="mb-3">
                                    <label for="remarks" class="form-label">Remarks</label>
                                    <textarea class="form-control" id="remarks" name="remarks"></textarea>
                                </div>

                                <h4>Products</h4>
                                <div id="product-repeater">
                                    <div class="product-item row g-2 align-items-end mb-2">
                                        <div class="col-md-3">
                                            <label class="form-label">Product Name</label>
                                            <input type="text" class="form-control" name="products[0][product_name]" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Description</label>
                                            <input type="text" class="form-control" name="products[0][product_description]">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Photo</label>
                                            <input type="file" class="form-control" name="product_photos[0]" accept="image/*">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Quantity</label>
                                            <input type="number" class="form-control" name="products[0][ordered_quantity]" min="1" required>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger remove-product w-100">Remove</button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-secondary mb-3" id="add-product">Add Product</button>

                                <div class="hstack gap-2 justify-content-end">
                                    <button type="submit" class="btn btn-primary">Submit Order</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- container-fluid -->
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        let productIndex = 1;
        $('#add-product').click(function() {
            const productHtml = `
            <div class="product-item row g-2 align-items-end mb-2">
                <div class="col-md-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" class="form-control" name="products[${productIndex}][product_name]" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="products[${productIndex}][product_description]">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Photo</label>
                    <input type="file" class="form-control" name="product_photos[${productIndex}]" accept="image/*">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control" name="products[${productIndex}][ordered_quantity]" min="1" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-product w-100">Remove</button>
                </div>
            </div>`;
            $('#product-repeater').append(productHtml);
            productIndex++;
        });

        $(document).on('click', '.remove-product', function() {
            if ($('.product-item').length > 1) {
                $(this).closest('.product-item').remove();
            }
        });
    });
</script>
<?php
include "assets/includes/footer.php";
?>