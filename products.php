<?php
session_start();
if (!isset($_SESSION['Username'])) {
    header('Location: login');
    exit;
}
if ($_SESSION['Role'] == 'Accounts') {
    header('Location: dashboard');
    exit;
}
include "config.php";
$title = 'Products';
include "assets/includes/header.php";
?>


<!-- Start right Content here -->
<!-- ============================================================== -->
<div class="main-content">

    <div class="page-content">
        <div class="container-fluid">


            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Products</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                                <li class=" active"><i class="las la-angle-right"></i>Products List</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row pb-4 gy-3">
                <?php

                if (isset($_SESSION['error']) && !empty($_SESSION['error'])) {
                    echo '<div class="alert alert-danger" role="alert" id="primary-alert">';
                    echo '<strong>Error!</strong> ' . $_SESSION['error'];
                    echo '</div>';
                    unset($_SESSION['error']);
                }

                if (isset($_SESSION['success']) && !empty($_SESSION['success'])) {
                    echo '<div class="alert alert-primary" role="alert" id="primary-alert">';
                    echo '<strong>Success!</strong> ' . $_SESSION['success'];
                    echo '</div>';
                    unset($_SESSION['success']);
                }
                ?>
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="get">
                                <div class="row">
                                    <div class="col-8">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="search_query" value="<?php echo isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : ''; ?>" placeholder="Search by Customer or Product">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <button class="btn btn-success" type="submit">Search</button>
                                        <a href="orders" class="btn btn-primary">Reset</a>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

                <?php
                if ($_SESSION['Role'] == 'Admin') {
                ?>
                    <div class="col-sm-4">
                        <button class="btn btn-primary addPayment-modal" data-bs-toggle="modal" data-bs-target="#addProductModal"><i class="fa-solid fa-cart-plus"></i> Add New</button>
                    </div>
                <?php } ?>
            </div>
            <?php if ($_SESSION['Role'] == 'Admin' || $_SESSION['Role'] == 'Quality Control' || $_SESSION['Role'] == 'Manager') { ?>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table table-hover table-nowrap align-middle mb-0">
                                        <thead>
                                            <tr class="text-muted text-uppercase">
                                                <th scope="col">Product ID</th>
                                                <th scope="col">Product Name</th>
                                                <th scope="col">Size</th>
                                                <th scope="col">Color</th>
                                                <th scope="col">Weight</th>
                                                <th scope="col">Image</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dynamic product rows will be loaded here via PHP or AJAX -->
                                            <?php
                                            $sql = "SELECT * FROM products";
                                            $result = mysqli_query($conn, $sql);

                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo "<tr>";
                                                    echo "<td>" . $row['ProductID'] . "</td>";
                                                    echo "<td>" . $row['ProductName'] . "</td>";
                                                    echo "<td>" . $row['ProductSize'] . "</td>";
                                                    echo "<td>" . $row['ProductColor'] . "</td>";
                                                    echo "<td>" . $row['ProductWeight'] . "</td>";
                                                    echo "<td><img src='uploads/" . htmlspecialchars($row['ProductImage']) . "' alt='" . htmlspecialchars($row['ProductName']) . "' width='50'></td>";
                                                    echo "<td>
                                                            <button class='btn btn-warning btn-sm edit-btn' data-bs-toggle='modal' data-bs-target='#editProductModal' data-id='" . $row['ProductID'] . "'>Edit</button>
                                                            <button class='btn btn-danger btn-sm delete-btn' data-id='" . $row['ProductID'] . "'>Delete</button>
                                                            <button class='btn btn-soft-info' data-bs-toggle='modal' data-bs-target='#pdfPreviewModal' data-product-id=' " . $row['ProductID'] . "'><i class='bx bx-printer fs-22'></i></button>
                                                            </td>";
                                                    echo "</tr>";
                                                }
                                            }
                                            ?>
                                        </tbody>

                                        <!-- end tbody -->
                                    </table><!-- end table -->
                                </div><!-- end table responsive -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center mb-4 gy-3">
                    <div class="col-md-5">
                        <?php if (isset($offset, $records_per_page, $total_records)) : ?>
                            <p class="mb-0 text-muted">
                                Showing <b><?php echo ($offset + 1); ?></b> to
                                <b><?php echo min($offset + $records_per_page, $total_records); ?></b> of
                                <b><?php echo $total_records; ?></b> results
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm-auto ms-auto">
                        <nav aria-label="...">
                            <ul class="pagination mb-0">
                                <li class="page-item <?php if ($current_page <= 1) echo 'disabled'; ?>">
                                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                                    <li class="page-item <?php if ($current_page == $i) echo 'active'; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php } ?>
                                <li class="page-item <?php if ($current_page >= $total_pages) echo 'disabled'; ?>">
                                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="productForm" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="productName">Product Name</label>
                                <input type="text" class="form-control" id="productName" name="productName" required>
                            </div>
                            <div class="form-group">
                                <label for="productSize">Product Size</label>
                                <input type="text" class="form-control" id="productSize" name="productSize">
                            </div>
                            <div class="form-group">
                                <label for="productColor">Product Color</label>
                                <input type="text" class="form-control" id="productColor" name="productColor">
                            </div>
                            <div class="form-group">
                                <label for="productWeight">Product Weight</label>
                                <input type="text" class="form-control" id="productWeight" name="productWeight">
                            </div>
                            <div class="form-group">
                                <label for="productImage">Product Image</label>
                                <input type="file" class="form-control" id="productImage" name="productImage" accept="image/*" required>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Save Product</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add user Modal -->
        <!-- Modal -->
        <!-- Edit Product Modal -->
        <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editProductForm" enctype="multipart/form-data">
                            <input type="hidden" id="editProductID" name="productID">
                            <div class="form-group">
                                <label for="editProductName">Product Name</label>
                                <input type="text" class="form-control" id="editProductName" name="productName" required>
                            </div>
                            <div class="form-group">
                                <label for="editProductSize">Product Size</label>
                                <input type="text" class="form-control" id="editProductSize" name="productSize">
                            </div>
                            <div class="form-group">
                                <label for="editProductColor">Product Color</label>
                                <input type="text" class="form-control" id="editProductColor" name="productColor">
                            </div>
                            <div class="form-group">
                                <label for="editProductWeight">Product Weight</label>
                                <input type="text" class="form-control" id="editProductWeight" name="productWeight">
                            </div>
                            <div class="form-group">
                                <label for="editProductImage">Product Image</label>
                                <input type="file" class="form-control" id="editProductImage" name="productImage" accept="image/*">
                                <img id="currentImagePreview" src="" alt="Current Image" width="100" class="mt-2">
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for PDF Preview -->
        <div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-labelledby="pdfPreviewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pdfPreviewModalLabel">PDF Preview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Iframe to display PDF -->
                        <iframe id="pdfIframe" style="width: 100%; height: 600px;" frameborder="0"></iframe>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="window.open($('#pdfIframe').attr('src'))">Print</button>
                    </div>
                </div>
            </div>
        </div>



        <!--end modal-->
    </div>
    <!-- <a href="" ></a> -->

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).on('click', '.btn-soft-info', function() {
            var ProductID = $(this).data('product-id');
            var pdfUrl = 'generate_product_pdf.php?ProductID=' + encodeURIComponent(ProductID);
            $('#pdfIframe').attr('src', pdfUrl);
            var modal = new bootstrap.Modal(document.getElementById('pdfPreviewModal'));
            modal.show();
        });
        $('#pdfPreviewModal').on('hidden.bs.modal', function() {
            $('.modal-backdrop').remove(); // Ensure the backdrop is removed
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#productForm').on('submit', function(event) {
                event.preventDefault();

                var formData = new FormData(this); // Prepare form data for the request

                $.ajax({
                    url: 'save_product.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#addProductModal').modal('hide');
                        location.reload(); // Reload the page to update the product table
                    },
                    error: function() {
                        alert('Error adding product');
                    }
                });
            });
        });

        $(document).on('click', '.delete-btn', function() {
            var productId = $(this).data('id');

            if (confirm('Are you sure you want to delete this product?')) {
                $.ajax({
                    url: 'delete_product.php',
                    type: 'POST',
                    data: {
                        id: productId
                    },
                    success: function(response) {
                        alert('Product deleted successfully!');
                        location.reload(); // Reload the page to update the product table
                    },
                    error: function() {
                        alert('Error deleting product');
                    }
                });
            }
        });

        $(document).ready(function() {
            // Handle the click event for the edit button
            $(document).on('click', '.edit-btn', function() {
                var productId = $(this).data('id'); // Get the product ID from the button's data attribute

                // AJAX request to fetch the product details
                $.ajax({
                    url: 'get_product.php', // PHP script to fetch product details
                    type: 'POST',
                    data: {
                        id: productId
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Populate the modal with the fetched product details
                        $('#editProductID').val(response.ProductID);
                        $('#editProductName').val(response.ProductName);
                        $('#editProductSize').val(response.ProductSize);
                        $('#editProductColor').val(response.ProductColor);
                        $('#editProductWeight').val(response.ProductWeight);

                        // Show the current image if available
                        if (response.ProductImage) {
                            $('#currentImagePreview').attr('src', 'uploads/' + response.ProductImage);
                        } else {
                            $('#currentImagePreview').attr('src', ''); // Clear the image preview if no image
                        }

                        // Show the modal
                        $('#editProductModal').modal('show');
                    },
                    error: function() {
                        alert('Error fetching product details');
                    }
                });
            });

            // Handle the form submission
            $('#editProductForm').on('submit', function(event) {
                event.preventDefault(); // Prevent the default form submission

                var formData = new FormData(this); // Collect the updated form data

                $.ajax({
                    url: 'update_product.php', // PHP script to update the product
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        alert('Product updated successfully!');
                        $('#editProductModal').modal('hide');
                        location.reload(); // Reload the page to refresh the product table
                    },
                    error: function() {
                        alert('Error updating product');
                    }
                });
            });
        });
    </script>
    <!-- image privew -->
    <script>
        const imageInput = document.getElementById('member-image-input');
        const memberImg = document.getElementById('member-img');

        imageInput.addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    memberImg.src = e.target.result;
                }

                reader.readAsDataURL(file);
            }
        });
    </script>
    <!-- image privew -->
    <script>
        const imageInput_E = document.getElementById('member-image-input_E');
        const memberImg_E = document.getElementById('edit_profile_pic');

        imageInput_E.addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    memberImg_E.src = e.target.result;
                }

                reader.readAsDataURL(file);
            }
        });
    </script>
    <?php
    include "assets/includes/footer.php";
    ?>