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
$title = 'Orders';
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
                        <h4 class="mb-sm-0">Orders</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                                <li class=" active"><i class="las la-angle-right"></i>Orders List</li>
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
                        <button class="btn btn-primary addPayment-modal" data-bs-toggle="modal" data-bs-target="#addpaymentModal"><i class="fa-solid fa-cart-plus"></i> Add New</button>
                        <button class="btn btn-primary" id="bulkApproveBtn">
                            Approve All
                        </button>

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
                                                <th><input type="checkbox" class="form-check checkall" id="checkall"></th>
                                                <th scope="col">Orders ID</th>
                                                <th scope="col">Artisan</th>
                                                <th scope="col">Customer Name</th>
                                                <th scope="col">Product</th>
                                                <th scope="col">Quantity</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">WagesPerUnit</th>
                                                <th scope="col">Due Date</th>
                                                <?php if ($_SESSION['Role'] == 'Admin' || $_SESSION['Role'] == 'Quality Control') { ?>
                                                    <th scope="col" style="width: 12%;">Action</th>
                                                <?php } ?>
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

                                            // Initialize the SQL WHERE clause
                                            $sql_where = "orders.is_delete = 0 AND artisans.is_delete = 0 AND departments.is_delete = 0";

                                            // Check for the combined search query and add it to the SQL query
                                            if (isset($_GET['search_query']) && !empty($_GET['search_query'])) {
                                                $search_query = mysqli_real_escape_string($conn, $_GET['search_query']);
                                                $sql_where .= " AND (orders.CustomerName LIKE '%$search_query%' OR orders.Product LIKE '%$search_query%')";
                                            }

                                            // Get the total number of records
                                            $sql_count = "SELECT COUNT(*) as total FROM orders 
                                                                  INNER JOIN departments Using(DepartmentID) 
                                                                  INNER JOIN artisans on artisans.ArtisanID = orders.ArtisanID 
                                                                  WHERE $sql_where";
                                            $result_count = mysqli_query($conn, $sql_count);
                                            $total_records = mysqli_fetch_assoc($result_count)['total'];

                                            // Calculate total pages
                                            $total_pages = ceil($total_records / $records_per_page);

                                            // Get records for the current page
                                            $sql = "SELECT *, orders.DepartmentID as Ord_DepartmentID FROM orders 
                                                            INNER JOIN departments Using(DepartmentID) 
                                                            INNER JOIN artisans on artisans.ArtisanID = orders.ArtisanID 
                                                            WHERE $sql_where 
                                                            ORDER BY OrderID DESC 
                                                            LIMIT $offset, $records_per_page";

                                            $result = mysqli_query($conn, $sql) or die("Query Failed");



                                            // $result = mysqli_query($conn, $sql) or die("Query Failed");
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                            ?>

                                                    <tr data-order_id="<?php echo $row['OrderID']; ?>" data-customer_name="<?php echo $row['CustomerName']; ?>" data-product="<?php echo $row['Product']; ?>" data-quantity="<?php echo $row['Quantity']; ?>" data-wages_per_piece="<?php echo $row['WagesPerPiece']; ?>" data-production_due_date="<?php echo $row['ProductionDueDate']; ?>" data-department_id="<?php echo $row['Ord_DepartmentID']; ?>" data-artisan_id="<?php echo $row['ArtisanID']; ?>" data-artisan_name=" <?php echo $row['ArtisanName']; ?>">
                                                        <td>
                                                            <?php if ($row['Status'] == 'inprocess' || $row['Status'] == 'overdue') { ?>
                                                                <input type="checkbox" class="form-check" name="selectedOrders[]" value="<?php echo $row['OrderID']; ?>">
                                                            <?php } ?>
                                                        </td>
                                                        <td><?php echo $row['OrderID']; ?></td>
                                                        <td><?php echo $row['ArtisanName']; ?></td>
                                                        <td><?php echo $row['CustomerName']; ?></td>
                                                        <td><?php echo $row['Product']; ?></td>
                                                        <td><?php echo $row['Quantity']; ?></td>
                                                        <?php
                                                        // Check if the order is overdue
                                                        $current_date = date('Y-m-d');
                                                        $is_overdue = strtotime($row['ProductionDueDate']) < strtotime($current_date);

                                                        // Determine the status class and label
                                                        if ($is_overdue && $row['Status'] == 'inprocess') {
                                                            $status_class = 'bg-danger-subtle text-danger';
                                                            $status_label = 'Overdue';
                                                        } else if ($row['Status'] == 'inprocess') {
                                                            $status_class = 'bg-danger-subtle text-danger';
                                                            $status_label = $row['Status'];
                                                        } else if ($row['Status'] == 'Dispatch') {
                                                            $status_class = 'bg-success-subtle text-success';
                                                            $status_label = $row['Status'];
                                                        } else {
                                                            $status_class = 'bg-warning-subtle text-warning';
                                                            $status_label = $row['Status'];
                                                        }
                                                        ?>

                                                        <td>
                                                            <span class="badge text-capitalized <?php echo $status_class; ?> p-2">
                                                                <?php echo $status_label; ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $row['WagesPerPiece']; ?></td>
                                                        <td><?php echo $row['ProductionDueDate']; ?></td>


                                                        <td>
                                                            <ul class="list-inline hstack  mb-0">
                                                                <?php
                                                                if ($_SESSION['Role'] == 'Quality Control' || $_SESSION['Role'] == 'Admin' || $_SESSION['Role'] == 'Manager') {
                                                                ?>
                                                                    <?php

                                                                    if ($row['Status'] == 'inprocess') {
                                                                        if ($_SESSION['Role'] == 'Quality Control' || $_SESSION['Role'] == 'Admin') {
                                                                    ?>

                                                                            <li class="list-inline-item approve" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Edit">
                                                                                <button href="" class="btn btn-soft-info btn-sm d-inline-block " data-bs-toggle="modal3" data-bs-target="#approvepaymentModal">
                                                                                    Approve
                                                                                </button>
                                                                            </li>
                                                                        <?php
                                                                        }
                                                                        if ($_SESSION['Role'] == 'Admin' || $_SESSION['Role'] == 'Manager') {
                                                                        ?>
                                                                            <li class="list-inline-item edit" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Edit">
                                                                                <button href="" class="btn btn-soft-info btn-sm d-inline-block " data-bs-toggle="modal2" data-bs-target="#editpaymentModal">
                                                                                    <i class="las la-pen fs-17 align-middle"></i>
                                                                                </button>
                                                                            </li>
                                                                            <?php if ($_SESSION['Role'] == 'Admin') { ?>
                                                                                <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Remove">
                                                                                    <a href="order-process?id=<?php echo $row['OrderID']; ?>" class="btn btn-soft-danger btn-sm d-inline-block" onclick="return confirm('Do you want to delete this Order?')">
                                                                                        <i class="las la-file-download fs-17 align-middle"></i>
                                                                                    </a>
                                                                                </li>
                                                                            <?php } ?>
                                                                        <?php } ?>
                                                                    <?php } else if ($row['Status'] == 'Dispatch') {
                                                                        echo '<li class="list-inline-item"> <button class="btn btn-soft-success btn-sm d-inline-block ">Completed</button></li>';
                                                                    } else {
                                                                        echo '<li class="list-inline-item"> <button class="btn btn-soft-warning btn btn-sm d-inline-block">Approved</button></li>';
                                                                    }
                                                                    ?>
                                                            </ul>
                                                        </td>
                                                    <?php } ?>

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
            <?php } ?>
            <?php if ($_SESSION['Role'] == 'Manager') { ?>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title pb-3"><b>Completed Order :</b></h5>
                                <div class="table-responsive table-card">
                                    <table class="table table-hover table-nowrap align-middle mb-0">
                                        <thead>
                                            <tr class="text-muted text-uppercase">
                                                <th scope="col">Orders ID</th>
                                                <th scope="col">Customer Name</th>
                                                <th scope="col">Product</th>
                                                <th scope="col">Quantity</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Wages Per Piece</th>
                                                <th scope="col">Production Due Date</th>
                                                <th scope="col">Artisan Name</th>
                                                <th scope="col">Department Name</th>

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

                                            // Initialize the SQL WHERE clause
                                            $sql_where = "orders.is_delete = 0 AND artisans.is_delete = 0 AND departments.is_delete = 0";

                                            // Check for additional filters and add them to the SQL query
                                            if (isset($_GET['customer_name']) && !empty($_GET['customer_name'])) {
                                                $customer_name = mysqli_real_escape_string($conn, $_GET['customer_name']);
                                                $sql_where .= " AND orders.CustomerName LIKE '%$customer_name%'";
                                            }

                                            if (isset($_GET['product_name']) && !empty($_GET['product_name'])) {
                                                $product_name = mysqli_real_escape_string($conn, $_GET['product_name']);
                                                $sql_where .= " AND orders.Product LIKE '%$product_name%'";
                                            }

                                            // Get the total number of records
                                            $sql_count = "SELECT COUNT(*) as total FROM orders INNER JOIN departments Using(DepartmentID) INNER JOIN artisans on artisans.ArtisanID = orders.ArtisanID WHERE $sql_where";
                                            $result_count = mysqli_query($conn, $sql_count);
                                            $total_records = mysqli_fetch_assoc($result_count)['total'];

                                            // Calculate total pages
                                            $total_pages = ceil($total_records / $records_per_page);

                                            // Get records for the current page
                                            $sql = "SELECT *, orders.DepartmentID as Ord_DepartmentID FROM orders INNER JOIN departments Using(DepartmentID) INNER JOIN artisans on artisans.ArtisanID = orders.ArtisanID WHERE $sql_where ORDER BY OrderID DESC LIMIT $offset, $records_per_page";
                                            $result = mysqli_query($conn, $sql) or die("Query Failed");

                                            // $result = mysqli_query($conn, $sql) or die("Query Failed");
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                            ?>

                                                    <tr data-order_id="<?php echo $row['OrderID']; ?>" data-customer_name="<?php echo $row['CustomerName']; ?>" data-product="<?php echo $row['Product']; ?>" data-quantity="<?php echo $row['Quantity']; ?>" data-wages_per_piece="<?php echo $row['WagesPerPiece']; ?>" data-production_due_date="<?php echo $row['ProductionDueDate']; ?>" data-department_id="<?php echo $row['Ord_DepartmentID']; ?>" data-artisan_id="<?php echo $row['ArtisanID']; ?>">

                                                        <td><?php echo $row['OrderID']; ?></td>
                                                        <td><?php echo $row['CustomerName']; ?></td>
                                                        <td><?php echo $row['Product']; ?></td>
                                                        <td><?php echo $row['Quantity']; ?></td>
                                                        <?php
                                                        // Check if the order is overdue
                                                        $current_date = date('Y-m-d');
                                                        $is_overdue = strtotime($row['ProductionDueDate']) < strtotime($current_date);

                                                        // Determine the status class and label
                                                        if ($is_overdue && $row['Status'] == 'inprocess') {
                                                            $status_class = 'bg-danger-subtle text-danger';
                                                            $status_label = 'Overdue';
                                                        } else if ($row['Status'] == 'inprocess') {
                                                            $status_class = 'bg-danger-subtle text-danger';
                                                            $status_label = $row['Status'];
                                                        } else if ($row['Status'] == 'Dispatch') {
                                                            $status_class = 'bg-success-subtle text-success';
                                                            $status_label = $row['Status'];
                                                        } else {
                                                            $status_class = 'bg-warning-subtle text-warning';
                                                            $status_label = $row['Status'];
                                                        }
                                                        ?>

                                                        <td>
                                                            <span class="badge text-capitalized <?php echo $status_class; ?> p-2">
                                                                <?php echo $status_label; ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $row['WagesPerPiece']; ?></td>
                                                        <td><?php echo $row['ProductionDueDate']; ?></td>
                                                        <td><?php echo $row['ArtisanName']; ?></td>
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
            <?php } ?>
        </div>
    </div>
    <!-- container-fluid -->
</div>
<!-- Add user Modal -->
<!-- Modal -->
<div class="modal fade" id="addpaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header p-4 pb-0">
                <h5 class="modal-title" id="createMemberLabel">Add Order</h5>
                <button type="button" class="btn-close" id="createMemberBtn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="memberlist-form" class="needs-validation" novalidate enctype="multipart/form-data" method="post" action="order-process">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="mb-3 mt-4">
                                <label for="CustomerName" class="form-label">Customer Name</label>
                                <input type="text" class="form-control" name="CustomerName" id="CustomerName" placeholder="Enter Customer Name" required>
                            </div>
                            <div class="mb-3 mt-4">
                                <label for="Product" class="form-label">Product</label>
                                <input type="text" class="form-control" name="Product" id="Product" placeholder="Enter Product" required>
                            </div>
                            <div class="mb-3 mt-4">
                                <label for="Quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" name="Quantity" id="Quantity" placeholder="Enter Quantity" required>
                            </div>
                            <div class="mb-3 mt-4">
                                <label for="WagesPerPiece" class="form-label">Wages Per Piece</label>
                                <input type="number" class="form-control" name="WagesPerPiece" id="WagesPerPiece" placeholder="Enter Wages Per Piece" required>
                            </div>
                            <div class="mb-3 mt-4">
                                <label for="ProductionDueDate" class="form-label">Production Due Date</label>
                                <input type="date" class="form-control" name="ProductionDueDate" id="ProductionDueDate" required>
                            </div>
                            <div class="mb-4">
                                <label for="ArtisanID" class="form-label">Artisan Name</label>
                                <select class="form-select" name="ArtisanID" id="ArtisanID" required>
                                    <option disabled selected>Select Artisan Name</option>
                                    <?php
                                    $sql_artisan = "SELECT * FROM artisans WHERE is_delete = 0";
                                    $result_artisan = mysqli_query($conn, $sql_artisan);
                                    if (mysqli_num_rows($result_artisan) > 0) {
                                        while ($row_artisan = mysqli_fetch_assoc($result_artisan)) {
                                            echo '<option value="' . $row_artisan['ArtisanID'] . '">' . $row_artisan['ArtisanName'] . '</option>';
                                        }
                                    } else {
                                        echo '<option value="">No records found</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="DepartmentID" class="form-label">Department Name</label>
                                <select class="form-select" name="DepartmentID" id="DepartmentID" required>
                                    <option disabled selected>Select Department Name</option>


                                </select>
                            </div>
                            <div class="hstack gap-2 justify-content-end">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success" name="Add_Order" id="addNewMember">Add Order</button>
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
<!-- edit user Modal -->
<!-- Modal -->
<div class="modal fade" id="editpaymentModal" tabindex="-2" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header p-4 pb-0">
                <h5 class="modal-title" id="createMemberLabel">Edit Order</h5>
                <button type="button" class="btn-close" id="createMemberBtn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="memberlist-form" class="needs-validation" novalidate enctype="multipart/form-data" method="post" action="order-process">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="mb-3 mt-4">
                                <input type="hidden" name="OrderID" id="edit_order_id">
                                <label for="CustomerName" class="form-label">Customer Name</label>
                                <input type="text" class="form-control" name="CustomerName" id="eidt_customer_name" placeholder="Enter Customer Name" required>
                            </div>
                            <div class="mb-3 mt-4">
                                <label for="Product" class="form-label">Product</label>
                                <input type="text" class="form-control" name="Product" id="eidt_product" placeholder="Enter Product" required>
                            </div>
                            <div class="mb-3 mt-4">
                                <label for="Quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" name="Quantity" id="eidt_quantity" placeholder="Enter Quantity" required>
                            </div>
                            <div class="mb-3 mt-4">
                                <label for="WagesPerPiece" class="form-label">Wages Per Piece</label>
                                <input type="number" class="form-control" name="WagesPerPiece" id="eidt_wages_per_piece" placeholder="Enter Wages Per Piece" required>
                            </div>
                            <div class="mb-3 mt-4">
                                <label for="eidt_production_due_date" class="form-label">Production Due Date</label>
                                <input type="date" class="form-control" name="ProductionDueDate" id="eidt_production_due_date" required>
                            </div>
                            <div class="mb-4">
                                <label for="ArtisanID" class="form-label">Artisan Name</label>
                                <select class="form-select" name="ArtisanID" id="eidt_artisan_id" required>
                                    <option disabled selected>Select Artisan Name</option>
                                    <?php
                                    $sql_artisan = "SELECT * FROM artisans WHERE is_delete = 0";
                                    $result_artisan = mysqli_query($conn, $sql_artisan);
                                    if (mysqli_num_rows($result_artisan) > 0) {
                                        while ($row_artisan = mysqli_fetch_assoc($result_artisan)) {
                                            echo '<option value="' . $row_artisan['ArtisanID'] . '">' . $row_artisan['ArtisanName'] . '</option>';
                                        }
                                    } else {
                                        echo '<option value="">No records found</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="DepartmentID" class="form-label">Department Name</label>
                                <select class="form-select" name="DepartmentID" id="eidt_department_id" required>
                                    <option disabled selected>Select Department NAme</option>
                                    <?php
                                    $sql_artisan = "SELECT * FROM departments WHERE is_delete = 0";
                                    $result_artisan = mysqli_query($conn, $sql_artisan);
                                    if (mysqli_num_rows($result_artisan) > 0) {
                                        while ($row_artisan = mysqli_fetch_assoc($result_artisan)) {
                                            echo '<option value="' . $row_artisan['DepartmentID'] . '">' . $row_artisan['DepartmentName'] . '</option>';
                                        }
                                    } else {
                                        echo '<option value="">No records found</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="hstack gap-2 justify-content-end">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success" name="Edit_Order" id="addNewMember">Update Order</button>
                            </div>
                        </div>
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
                <h5 class="modal-title" id="createMemberLabel">Approve Order</h5>
                <button type="button" class="btn-close" id="createMemberBtn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="memberlist-form" class="needs-validation" novalidate enctype="multipart/form-data" method="post" action="quality-control-process">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="mb-3 mt-4">
                                <label for="ApprovedQuantity" class="form-label">Approved Quantity</label>
                                <input type="number" class="form-control" name="ApprovedQuantity" id="approve_quantity" placeholder="Enter Approved Quantity" required>
                            </div>
                            <!-- <div class="mb-3 mt-4">

                                    <label for="RejectedQuantity" class="form-label">Rejected Quantity</label>
                                    <input type="number" class="form-control" name="RejectedQuantity" id="approve_reject_quantity" placeholder="Enter Rejected Quantity" required>
                                </div> -->
                            <div class="mb-3 mt-4">
                                <label for="ApprovalDate" class="form-label">Approval Date</label>
                                <input type="date" class="form-control" name="ApprovalDate" id="ApprovalDate" placeholder="Enter Approval Date" required>
                            </div>
                            <div class="mb-4">
                                <label for="OrderID" class="form-label">Orders</label>

                                <input type="text" class="form-control" id="approve_order_id" readonly>
                                <input type="hidden" name="OrderID" id="approve_order_id_new">

                            </div>

                            <div class="hstack gap-2 justify-content-end">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success" name="Approve_Order" id="addNewMember">Approve Order</button>
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
<div class="modal fade" id="bulkapprovepaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header p-4 pb-0">
                <h5 class="modal-title">Bulk Approve Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="bulk-approve-form" class="needs-validation" novalidate enctype="multipart/form-data" method="post" action="quality-control-process">
                    <table class="table table-striped table-hover" id="bulkApproveTable">
                        <thead>
                            <tr>
                                <th class="col" style="width: 75%;">Product</th>
                                <th class="col">Artisan</th>
                                <th class="col">Quantity to Approve</th>
                                <th class="col">Approval Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic rows will be appended here -->

                        </tbody>
                    </table>
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" name="approve_all">Approve All</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!--end modal-->
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


<script>
    $(document).ready(function() {


        $('#ArtisanID').on('change', function() {
            var artisanID = $(this).val();

            if (artisanID) {
                $.ajax({
                    url: 'fetch_department.php',
                    type: 'POST',
                    data: {
                        ArtisanID: artisanID
                    },
                    success: function(response) {
                        $('#DepartmentID').html(response);
                    }
                });
            } else {
                $('#DepartmentID').html('<option disabled selected>Select Department Name</option>');
                $('#DepartmentID').prop('disabled', false); // Enable the dropdown if no artisan is selected
            }
        });
    });



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
        // Show the popup

    });

    $(document).on("click", '.approve', function() {
        var $row = $(this).closest('tr');
        var order_id = $row.data('order_id');
        var product = $row.data('product');
        var quantity = $row.data('quantity');
        // Update form fields
        $('#approve_quantity').val(quantity);
        $('#approve_reject_quantity').val(0);
        $('#approve_order_id').val(product);
        $('#approve_order_id_new').val(order_id);

        var myModal = new bootstrap.Modal(document.getElementById('approvepaymentModal'));
        myModal.show();
    });


    document.addEventListener("DOMContentLoaded", function() {
        // Get the "Select All" checkbox and all individual checkboxes in the table
        const selectAllCheckbox = document.getElementById('checkall');
        const checkboxes = document.querySelectorAll('input[name="selectedOrders[]"]');

        // Function to check if a checkbox's row belongs to an inprocess or overdue order
        function isSelectableStatus(status) {
            return status === 'inprocess' || status === 'Overdue';
        }

        // When the "Select All" checkbox is clicked
        selectAllCheckbox.addEventListener('change', function() {
            // Select only checkboxes for rows with inprocess or overdue status
            checkboxes.forEach(checkbox => {
                const status = checkbox.closest('tr').querySelector('td:nth-child(7) span').textContent.trim();
                if (isSelectableStatus(status)) {
                    checkbox.checked = selectAllCheckbox.checked;
                }
            });
        });

        // If any individual checkbox is unchecked, uncheck the "Select All" checkbox
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (!checkbox.checked) {
                    selectAllCheckbox.checked = false;
                } else if (Array.from(checkboxes).every(checkbox => {
                        const status = checkbox.closest('tr').querySelector('td:nth-child(7) span').textContent.trim();
                        return checkbox.checked && isSelectableStatus(status);
                    })) {
                    selectAllCheckbox.checked = true;
                }
            });
        });
    });



    $(document).on('click', '#bulkApproveBtn', function() {
        var selectedOrders = [];

        // Collect selected orders with details
        $('input[name="selectedOrders[]"]:checked').each(function() {
            var $row = $(this).closest('tr');
            var orderID = $row.data('order_id');
            var product = $row.data('product');
            var artisan = $row.data('artisan_name');
            var quantity = $row.data('quantity');

            // Add order details to the selectedOrders array
            selectedOrders.push({
                order_id: orderID,
                product: product,
                artisan: artisan,
                quantity: quantity
            });
        });

        // If no orders are selected, show an alert
        if (selectedOrders.length === 0) {
            alert("Please select at least one order to approve.");
            return;
        }

        // Clear any previous rows in the modal body
        var modalBody = $('#bulkApproveTable tbody');
        modalBody.empty();

        // Populate the table with selected orders
        selectedOrders.forEach(function(order) {
            var row = '<tr>';
            row += '<td class="col">' + order.product + '</td>';
            row += '<td>' + order.artisan + '</td>';
            row += '<td><input type="number" name="ApprovedQuantity[]" class="form-control" value="' + order.quantity + '" required></td>';
            row += '<td><input type="date" name="ApprovalDate[]" class="form-control" required></td>';
            row += '<td><input type="hidden" name="OrderID[]" value="' + order.order_id + '"></td>';
            row += '</tr>';
            modalBody.append(row);
        });

        // Show the modal
        var myModal = new bootstrap.Modal(document.getElementById('bulkapprovepaymentModal'));
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