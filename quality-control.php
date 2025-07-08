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
if ($_SESSION['Role'] == 'Admin' || $_SESSION['Role'] == 'Quality Control') {
} else {
    header('Location: dashboard');
}
include "config.php";
$title = 'Quality Control';
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
                        <h4 class="mb-sm-0">Quality Control</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                                <li class=" active"><i class="las la-angle-right"></i>Quality Control List</li>
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

            </div>

            <!-- All detail for order -->

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-hover table-nowrap align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted text-uppercase">
                                            <th scope="col">Orders ID</th>
                                            <th scope="col">Artisan Name</th>
                                            <th scope="col">Product</th>
                                            <th scope="col">Quantity</th>
                                            <th scope="col">Approved Quantity</th>
                                            <th scope="col">Rejected Quantity</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Wages Per Piece</th>
                                            <th scope="col">Production Due Date</th>
                                            <th scope="col">Approval Date</th>
                                            <th scope="col">Approval By</th>
                                            <th scope="col">Customer Name</th>
                                            <th scope="col">Department Name</th>
                                            <!-- <th scope="col" style="width: 12%;">Action</th> -->
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php

                                        // Set the number of records per page
                                        $records_per_page = 10;

                                        // Get the current page number from URL, default is 1 if not present
                                        $current_page = isset($_GET['page']) ? $_GET['page'] : 1;

                                        // Calculate the offset
                                        $offset = ($current_page - 1) * $records_per_page;

                                        // Get the total number of records  quality_control INNER JOIN orders Using(OrderID) INNER JOIN artisans on artisans.ArtisanID = orders.ArtisanID INNER JOIN users on users.UserID = quality_control.ApprovalBy  WHERE orders.is_delete = 0
                                        $sql_count = "SELECT COUNT(*) as total FROM quality_control INNER JOIN orders Using(OrderID) INNER JOIN departments Using(DepartmentID) INNER JOIN artisans on artisans.ArtisanID = orders.ArtisanID INNER JOIN users on users.UserID = quality_control.ApprovalBy  WHERE orders.is_delete = 0";
                                        $result_count = mysqli_query($conn, $sql_count);
                                        $total_records = mysqli_fetch_assoc($result_count)['total'];

                                        // Calculate total pages
                                        $total_pages = ceil($total_records / $records_per_page);

                                        // Get records for the current page
                                        $sql = "SELECT *,orders.DepartmentID as Ord_DepartmentID FROM quality_control INNER JOIN orders Using(OrderID) INNER JOIN departments Using(DepartmentID) INNER JOIN artisans on artisans.ArtisanID = orders.ArtisanID INNER JOIN users on users.UserID = quality_control.ApprovalBy  WHERE orders.is_delete = 0 ORDER BY OrderID  DESC LIMIT $offset, $records_per_page";
                                        $result = mysqli_query($conn, $sql) or die("Query Failed");
                                        // $result = mysqli_query($conn, $sql) or die("Query Failed");
                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                        ?>

                                                <tr data-order_id="<?php echo $row['OrderID']; ?>" data-customer_name="<?php echo $row['CustomerName']; ?>" data-product="<?php echo $row['Product']; ?>" data-quantity="<?php echo $row['Quantity']; ?>" data-wages_per_piece="<?php echo $row['WagesPerPiece']; ?>" data-production_due_date="<?php echo $row['ProductionDueDate']; ?>" data-department_id="<?php echo $row['Ord_DepartmentID']; ?>" data-artisan_id="<?php echo $row['ArtisanID']; ?>">

                                                    <td><?php echo $row['OrderID']; ?></td>
                                                    <td><?php echo $row['ArtisanName']; ?></td>
                                                    <td><?php echo $row['Product']; ?></td>
                                                    <td><?php echo $row['Quantity']; ?></td>
                                                    <td><?php echo $row['ApprovedQuantity']; ?></td>
                                                    <td><?php echo $row['RejectedQuantity']; ?></td>
                                                    <td><span class="badge <?php echo $row['Status'] == 'inprocess' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' ?>  p-2"><?php echo $row['Status']; ?></span></td>
                                                    <td><?php echo $row['WagesPerPiece']; ?></td>
                                                    <td><?php echo date('Y-m-d A', strtotime($row['ProductionDueDate'])); ?></td>
                                                    <td><?php echo date('Y-m-d A', strtotime($row['ApprovalDate'])); ?></td>
                                                    <td><?php echo $row['Username']; ?></td>
                                                    <td><?php echo $row['CustomerName']; ?></td>
                                                    <td><?php echo $row['DepartmentName']; ?></td>


                                                </tr>
                                        <?php
                                            }
                                        } else {
                                            echo '
                                            <tr>
                                                <td colspan="7"><h2>No Records Found</h2></td>
                                            </tr>';
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
                    <p class="mb-0 text-muted">Showing <b><?php echo ($offset + 1); ?></b> to <b><?php echo min($offset + $records_per_page, $total_records); ?></b> of <b><?php echo $total_records; ?></b> results</p>
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
        </div>
        <!-- container-fluid -->
    </div>



    <!--end modal-->
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>
    $(document).on("click", '.edit', function() {










        var $row = $(this).closest('tr');
        var order_id = $row.data('order_id');
        var customer_name = $row.data('customer_name');
        var product = $row.data('product');
        var quantity = $row.data('quantity');
        var wages_per_piece = $row.data('wages_per_piece');
        var production_due_date = $row.data('production_due_date');
        var department_id = $row.data('department_id');
        var artisan_id = $row.data('artisan_id');


        // Update form fields
        $('#edit_order_id').val(order_id);
        $('#eidt_customer_name').val(customer_name);
        $('#eidt_product').val(product);
        $('#eidt_quantity').val(quantity);
        $('#eidt_wages_per_piece').val(wages_per_piece);
        $('#eidt_production_due_date').val(production_due_date);
        $('#eidt_department_id').val(department_id);
        $('#eidt_artisan_id').val(artisan_id);


        // Show the popup
        var myModal = new bootstrap.Modal(document.getElementById('editpaymentModal'));
        myModal.show();
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