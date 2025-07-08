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
$title = 'Dispatch';
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
                        <h4 class="mb-sm-0">Dispatch</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                                <li class=" active"><i class="las la-angle-right"></i>Dispatch List</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row pb-4 gy-3">
                <div class="col-sm-4">
                    <button class="btn btn-primary" id="bulkDispatchBtn">Bulk Dispatch</button>
                </div>
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

                <!-- <div class="col-sm-4">
                    <button class="btn btn-primary addPayment-modal" data-bs-toggle="modal" data-bs-target="#addpaymentModal"><i class='bx bx-user-plus'></i> Add New</button>
                </div> -->

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
                                            <th scope="col">Select</th>
                                            <th scope="col">Order ID</th>
                                            <th scope="col">Product</th>
                                            <th scope="col">Quantity</th>
                                            <th scope="col">Approved</th>
                                            <th scope="col">Rejected</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">WagesPerUnit</th>
                                            <th scope="col">Approved On</th>
                                            <th scope="col">Customer Name</th>
                                            <th scope="col">Dispatch Date</th>

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
                                        $sql_count = "SELECT COUNT(*) as total FROM quality_control INNER JOIN orders Using(OrderID) INNER JOIN departments Using(DepartmentID) INNER JOIN artisans on artisans.ArtisanID = orders.ArtisanID INNER JOIN users on users.UserID = quality_control.ApprovalBy  WHERE orders.is_delete = 0 ";
                                        $result_count = mysqli_query($conn, $sql_count);
                                        $total_records = mysqli_fetch_assoc($result_count)['total'];

                                        // Calculate total pages
                                        $total_pages = ceil($total_records / $records_per_page);

                                        // Get records for the current page
                                        $sql = "SELECT *,orders.DepartmentID as Ord_DepartmentID FROM quality_control INNER JOIN orders Using(OrderID) INNER JOIN departments Using(DepartmentID) INNER JOIN artisans on artisans.ArtisanID = orders.ArtisanID INNER JOIN users on users.UserID = quality_control.ApprovalBy  WHERE orders.is_delete = 0  ORDER BY OrderID  DESC LIMIT $offset, $records_per_page";
                                        $result = mysqli_query($conn, $sql) or die("Query Failed");
                                        // $result = mysqli_query($conn, $sql) or die("Query Failed");
                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                        ?>

                                                <tr data-order_id="<?php echo $row['OrderID']; ?>" data-customer_name="<?php echo $row['CustomerName']; ?>" data-product="<?php echo $row['Product']; ?>" data-quantity="<?php echo $row['Quantity']; ?>" data-wages_per_piece="<?php echo $row['WagesPerPiece']; ?>" data-production_due_date="<?php echo $row['ProductionDueDate']; ?>" data-department_id="<?php echo $row['Ord_DepartmentID']; ?>" data-artisan_id="<?php echo $row['ArtisanID']; ?>">
                                                    <td>
                                                        <input type="checkbox" name="selectedOrders[]" value="<?php echo $row['OrderID']; ?>">
                                                    </td>
                                                    <td><?php echo $row['OrderID']; ?></td>
                                                    <td><?php echo $row['Product']; ?><br><span><?php echo $row['ArtisanName']; ?></span></td>
                                                    <td><?php echo $row['Quantity']; ?></td>
                                                    <td><?php echo $row['ApprovedQuantity']; ?></td>
                                                    <td><?php echo $row['RejectedQuantity']; ?></td>
                                                    <td><span class="badge <?php if ($row['Status'] == 'inprocess') {
                                                                                echo 'bg-danger-subtle text-danger';
                                                                            } else if ($row['Status'] == 'Dispatch') {
                                                                                echo 'bg-success-subtle text-success';
                                                                            } else {
                                                                                echo 'bg-warning-subtle text-warning';
                                                                            }  ?>  p-2"><?php echo $row['Status']; ?></span></td>
                                                    <td><?php echo $row['WagesPerPiece']; ?></td>
                                                    <td><?php echo date('Y-m-d', strtotime($row['ApprovalDate'])); ?><br> <span><?php echo "by " . $row['Username']; ?></span></td>
                                                    <td><?php echo $row['CustomerName']; ?></td>

                                                    <td><?php if (empty($row['DispatchDate'])) {
                                                            echo 'empty';
                                                        } else {
                                                            echo date('Y-m-d', strtotime($row['DispatchDate']));
                                                        } ?><br><span> <?php echo "by" .  $row['DispatchMethod']; ?></span></td>

                                                    <td>
                                                        <ul class="list-inline hstack  mb-0">
                                                            <?php
                                                            if ($_SESSION['Role'] == 'Quality Control' || $_SESSION['Role'] == 'Admin') {
                                                            ?>
                                                                <?php
                                                                if ($row['Status'] == 'Approved') { ?>
                                                                    <li class="list-inline-item approve" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Edit">
                                                                        <button href="" class="btn btn-soft-info btn-sm d-inline-block " data-bs-toggle="modal3" data-bs-target="#approvepaymentModal">
                                                                            Dispatch
                                                                        </button>
                                                                    </li>
                                                                <?php } else if ($row['Status'] == 'inprocess') {
                                                                    echo '<li class="list-inline-item"> <button class="btn btn-soft-warning btn-sm d-inline-block ">InProcess</button></li>';
                                                                } else {
                                                                    echo '<li class="list-inline-item"> <button class="btn btn-soft-success btn-sm d-inline-block ">Completed</button></li>';
                                                                }
                                                                ?>


                                                            <?php } ?>

                                                        </ul>
                                                    </td>

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


    <div class="modal fade" id="bulkDispatchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header p-4 pb-0">
                    <h5 class="modal-title">Bulk Dispatch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="dispatchForm">
                        <div class="mb-3 mt-4">
                            <label for="DispatchDate" class="form-label">Dispatch Date</label>
                            <input type="date" class="form-control" name="DispatchDate" id="DispatchDate" placeholder="Enter Approval Date" required>
                        </div>
                        <div class="mb-4">
                            <label for="DispatchMathod" class="form-label">Dispatch Method</label>
                            <select class="form-select" name="DispatchMathod" id="DispatchMathod" required>
                                <option disabled selected>Select Dispatch Method</option>
                                <option value="Office Vehicle">Office Vehicle</option>
                                <option value="Office Runner">Office Runner</option>
                            </select>
                        </div>

                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success" name="bulk_dispatch" id="bulkDispatchBtn">Dispatch Orders</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>







    <div class="modal fade" id="approvepaymentModal" tabindex="-3" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header p-4 pb-0">
                    <h5 class="modal-title" id="createMemberLabel">Dispatch Order</h5>
                    <button type="button" class="btn-close" id="createMemberBtn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="memberlist-form" class="needs-validation" novalidate enctype="multipart/form-data" method="post" action="dispatch-process">
                        <div class="row">
                            <div class="col-lg-12">
                                <input type="hidden" name="OrderID" id="approve_order_id" value="">
                                <div class="mb-3 mt-4">
                                    <label for="DispatchDate" class="form-label">Dispatch Date</label>
                                    <input type="date" class="form-control" name="DispatchDate" id="DispatchDate" placeholder="Enter Approval Date" required>
                                </div>
                                <div class="mb-4">
                                    <label for="DispatchMathod" class="form-label">Dispatch Mathod</label>
                                    <select class="form-select" name="DispatchMathod" id="DispatchMathod" required>
                                        <option disabled selected>Select Dispatch Mathod</option>
                                        <option value="Office Vehical">Office Vehical</option>
                                        <option value="Office Runner">Office Runner</option>
                                    </select>
                                </div>

                                <div class="hstack gap-2 justify-content-end">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-success" name="Dispatch_Order" id="addNewMember">Dispatch Order</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!--end modal-content-->
        </div>
        <!--end modal-dialog-->
    </div>
    <!--end modal-->
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>
    $(document).on("click", '.approve', function() {
        // ... (your existing code to handle single order dispatch)
    });

    $('#bulkDispatchBtn').click(function() {
        var selectedOrderIDs = [];
        $('input[name="selectedOrders[]"]:checked').each(function() {
            selectedOrderIDs.push($(this).val());
        });

        if (selectedOrderIDs.length === 0) {
            alert("Please select at least one order for bulk dispatch.");
            return;
        }

        // Show the bulk dispatch modal
        var bulkDispatchModal = new bootstrap.Modal(document.getElementById('bulkDispatchModal'));
        bulkDispatchModal.show();
    });

    // Add an event listener to the form submission in the bulk dispatch modal
    $('#dispatchForm').submit(function(event) {
        event.preventDefault();

        // Serialize form data
        var formData = $(this).serializeArray();

        // Add selected order IDs
        var selectedOrderIDs = [];
        $('input[name="selectedOrders[]"]:checked').each(function() {
            selectedOrderIDs.push($(this).val());
        });
        formData.push({
            name: 'selectedOrderIDs',
            value: JSON.stringify(selectedOrderIDs)
        });
        formData.push({
            name: 'bulk_dispatch',
            value: '1'
        }); // Add this field

        $.ajax({
            type: 'POST',
            url: 'dispatch-process.php',
            data: formData,
            dataType: 'json', // Expecting JSON response
            success: function(response) {
                console.log('Response:', response);
                if (response.status === 'success') {
                    // Hide modal and optionally refresh page or update UI
                    var bulkDispatchModal = bootstrap.Modal.getInstance(document.getElementById('bulkDispatchModal'));
                    bulkDispatchModal.hide();
                    // Optionally refresh page
                    // location.reload(); 
                    // Or update the UI to reflect changes
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });
    });
</script>
<script>
    $(document).on("click", '.approve', function() {
        var $row = $(this).closest('tr');
        var order_id = $row.data('order_id');


        // Update form fields
        $('#approve_order_id').val(order_id);


        var myModal = new bootstrap.Modal(document.getElementById('approvepaymentModal'));
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