<?php
session_start();
if (!isset($_SESSION['Username']) || $_SESSION['Role'] == 'Accounts') {
    header('Location: dashboard');
    exit;
}
include "config.php";
$title = 'Orders';
?>

<?php include "assets/includes/header.php"; ?>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Orders</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                                <li class="active"><i class="las la-angle-right"></i>Orders List</li>
                            </ol>
                        </div>
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
            <div class="row pb-4 gy-3">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="get">
                                <div class="row">
                                    <div class="col-8">
                                        <input type="text" class="form-control" name="search_query" value="<?php echo isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : ''; ?>" placeholder="Search by Customer or Product">
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
                <?php if ($_SESSION['Role'] == 'Admin') { ?>
                    <div class="col-sm-4">
                        <button class="btn btn-primary addPayment-modal" data-bs-toggle="modal" data-bs-target="#addpaymentModal"><i class="fa-solid fa-cart-plus"></i> Add New</button>
                        <button class="btn btn-primary" id="bulkApproveBtn">Approve All</button>
                    </div>
                <?php } ?>
            </div>
            <?php if (in_array($_SESSION['Role'], ['Admin', 'Quality Control', 'Manager'])) { ?>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table table-hover table-nowrap align-middle mb-0">
                                        <thead>
                                            <tr class="text-muted text-uppercase">
                                                <th><input type="checkbox" class="form-check checkall" id="checkall"></th>
                                                <th>Order ID</th>
                                                <th>Artisan</th>
                                                <th>Customer Name</th>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Status</th>
                                                <th>WagesPerUnit</th>
                                                <th>Due Date</th>
                                                <?php if (in_array($_SESSION['Role'], ['Admin', 'Quality Control'])) { ?>
                                                    <th style="width: 12%;">Action</th>
                                                <?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $records_per_page = 10;
                                            $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                            $offset = ($current_page - 1) * $records_per_page;
                                            $sql_where = "orders.is_delete = 0";
                                            if (isset($_GET['search_query']) && !empty($_GET['search_query'])) {
                                                $search_query = mysqli_real_escape_string($conn, $_GET['search_query']);
                                                $sql_where .= " AND (orders.CustomerName LIKE '%$search_query%' OR orders.Product LIKE '%$search_query%')";
                                            }
                                            $sql_count = "SELECT COUNT(*) as total FROM orders WHERE $sql_where";
                                            $result_count = mysqli_query($conn, $sql_count);
                                            $total_records = mysqli_fetch_assoc($result_count)['total'];
                                            $total_pages = ceil($total_records / $records_per_page);
                                            $sql = "SELECT orders.*, artisans.ArtisanName, ic.PendingQuantity
                                                    FROM orders 
                                                    LEFT JOIN artisans ON artisans.ArtisanID = orders.ArtisanID 
                                                    LEFT JOIN inventory_checks ic ON orders.InventoryCheckID = ic.InventoryCheckID
                                                    WHERE $sql_where 
                                                    ORDER BY orders.OrderID DESC 
                                                    LIMIT $offset, $records_per_page";
                                            $result = mysqli_query($conn, $sql) or die("Query Failed: " . mysqli_error($conn));
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $pending_quantity = $row['PendingQuantity'] ?? $row['Quantity'];
                                            ?>
                                                    <tr data-order_id="<?php echo $row['OrderID']; ?>"
                                                        data-customer_name="<?php echo htmlspecialchars($row['CustomerName']); ?>"
                                                        data-product="<?php echo htmlspecialchars($row['Product']); ?>"
                                                        data-quantity="<?php echo $row['Quantity']; ?>"
                                                        data-wages_per_piece="<?php echo $row['WagesPerPiece']; ?>"
                                                        data-production_due_date="<?php echo $row['ProductionDueDate']; ?>"
                                                        data-department_id="<?php echo $row['DepartmentID']; ?>"
                                                        data-artisan_id="<?php echo $row['ArtisanID']; ?>"
                                                        data-artisan_name="<?php echo htmlspecialchars($row['ArtisanName']); ?>"
                                                        data-pending_quantity="<?php echo $pending_quantity; ?>">
                                                        <td>
                                                            <?php if (in_array($row['Status'], ['inprocess', 'overdue'])) { ?>
                                                                <input type="checkbox" class="form-check" name="selectedOrders[]" value="<?php echo $row['OrderID']; ?>">
                                                            <?php } ?>
                                                        </td>
                                                        <td><?php echo $row['OrderID']; ?></td>
                                                        <td><?php echo htmlspecialchars($row['ArtisanName'] ?? 'Not Assigned'); ?></td>
                                                        <td><?php echo htmlspecialchars($row['CustomerName']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['Product']); ?></td>
                                                        <td><?php echo $row['Quantity']; ?></td>
                                                        <?php
                                                        $current_date = date('Y-m-d');
                                                        $is_overdue = $row['ProductionDueDate'] && strtotime($row['ProductionDueDate']) < strtotime($current_date);
                                                        if ($is_overdue && $row['Status'] == 'inprocess') {
                                                            $status_class = 'bg-danger-subtle text-danger';
                                                            $status_label = 'Overdue';
                                                        } elseif ($row['Status'] == 'inprocess') {
                                                            $status_class = 'bg-danger-subtle text-danger';
                                                            $status_label = 'In Process';
                                                        } elseif ($row['Status'] == 'Dispatch') {
                                                            $status_class = 'bg-success-subtle text-success';
                                                            $status_label = 'Dispatch';
                                                        } else {
                                                            $status_class = 'bg-warning-subtle text-warning';
                                                            $status_label = $row['Status'];
                                                        }
                                                        ?>
                                                        <td><span class="badge text-capitalize <?php echo $status_class; ?> p-2"><?php echo $status_label; ?></span></td>
                                                        <td><?php echo $row['WagesPerPiece'] ?: 'N/A'; ?></td>
                                                        <td><?php echo $row['ProductionDueDate'] ?: 'N/A'; ?></td>
                                                        <?php if (in_array($_SESSION['Role'], ['Admin', 'Quality Control'])) { ?>
                                                            <td>
                                                                <ul class="list-inline hstack mb-0">
                                                                    <?php if ($row['Status'] == 'inprocess') { ?>
                                                                        <?php if (in_array($_SESSION['Role'], ['Admin', 'Quality Control'])) { ?>
                                                                            <li class="list-inline-item approve" data-bs-toggle="tooltip" title="Approve">
                                                                                <button class="btn btn-soft-info btn-sm d-inline-block" data-bs-toggle="modal" data-bs-target="#approvepaymentModal">Approve</button>
                                                                            </li>
                                                                        <?php } ?>
                                                                        <?php if (in_array($_SESSION['Role'], ['Admin', 'Manager'])) { ?>
                                                                            <li class="list-inline-item edit" data-bs-toggle="tooltip" title="Edit">
                                                                                <button class="btn btn-soft-info btn-sm d-inline-block" data-bs-toggle="modal" data-bs-target="#editpaymentModal">
                                                                                    <i class="las la-pen fs-17 align-middle"></i>
                                                                                </button>
                                                                            </li>
                                                                            <?php if ($_SESSION['Role'] == 'Admin') { ?>
                                                                                <li class="list-inline-item" data-bs-toggle="tooltip" title="Remove">
                                                                                    <a href="order-process?id=<?php echo $row['OrderID']; ?>" class="btn btn-soft-danger btn-sm d-inline-block" onclick="return confirm('Do you want to delete this Order?')">
                                                                                        <i class="las la-file-download fs-17 align-middle"></i>
                                                                                    </a>
                                                                                </li>
                                                                            <?php } ?>
                                                                        <?php } ?>
                                                                    <?php } elseif ($row['Status'] == 'Dispatch') { ?>
                                                                        <li class="list-inline-item"><button class="btn btn-soft-success btn-sm d-inline-block">Completed</button></li>
                                                                    <?php } else { ?>
                                                                        <li class="list-inline-item"><button class="btn btn-soft-warning btn-sm d-inline-block">Approved</button></li>
                                                                    <?php } ?>
                                                                </ul>
                                                            </td>
                                                        <?php } ?>
                                                    </tr>
                                            <?php
                                                }
                                            } else {
                                                echo '<tr><td colspan="10"><h2>No Records Found</h2></td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
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
                                                <th>Order ID</th>
                                                <th>Customer Name</th>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Status</th>
                                                <th>Wages Per Piece</th>
                                                <th>Production Due Date</th>
                                                <th>Artisan Name</th>
                                                <th>Department Name</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT orders.*, artisans.ArtisanName, departments.DepartmentName
                                                    FROM orders 
                                                    LEFT JOIN artisans ON artisans.ArtisanID = orders.ArtisanID 
                                                    LEFT JOIN departments ON departments.DepartmentID = orders.DepartmentID
                                                    WHERE orders.is_delete = 0 
                                                    ORDER BY orders.OrderID DESC 
                                                    LIMIT $offset, $records_per_page";
                                            $result = mysqli_query($conn, $sql) or die("Query Failed: " . mysqli_error($conn));
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                            ?>
                                                    <tr>
                                                        <td><?php echo $row['OrderID']; ?></td>
                                                        <td><?php echo htmlspecialchars($row['CustomerName']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['Product']); ?></td>
                                                        <td><?php echo $row['Quantity']; ?></td>
                                                        <?php
                                                        $current_date = date('Y-m-d');
                                                        $is_overdue = $row['ProductionDueDate'] && strtotime($row['ProductionDueDate']) < strtotime($current_date);
                                                        if ($is_overdue && $row['Status'] == 'inprocess') {
                                                            $status_class = 'bg-danger-subtle text-danger';
                                                            $status_label = 'Overdue';
                                                        } elseif ($row['Status'] == 'inprocess') {
                                                            $status_class = 'bg-danger-subtle text-danger';
                                                            $status_label = 'In Process';
                                                        } elseif ($row['Status'] == 'Dispatch') {
                                                            $status_class = 'bg-success-subtle text-success';
                                                            $status_label = 'Dispatch';
                                                        } else {
                                                            $status_class = 'bg-warning-subtle text-warning';
                                                            $status_label = $row['Status'];
                                                        }
                                                        ?>
                                                        <td><span class="badge text-capitalize <?php echo $status_class; ?> p-2"><?php echo $status_label; ?></span></td>
                                                        <td><?php echo $row['WagesPerPiece'] ?: 'N/A'; ?></td>
                                                        <td><?php echo $row['ProductionDueDate'] ?: 'N/A'; ?></td>
                                                        <td><?php echo htmlspecialchars($row['ArtisanName'] ?? 'Not Assigned'); ?></td>
                                                        <td><?php echo htmlspecialchars($row['DepartmentName'] ?? 'Not Assigned'); ?></td>
                                                    </tr>
                                            <?php
                                                }
                                            } else {
                                                echo '<tr><td colspan="9"><h2>No Records Found</h2></td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
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
    <!-- Add Order Modal -->
    <div class="modal fade" id="addpaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header p-4 pb-0">
                    <h5 class="modal-title">Add Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="add-order-form" class="needs-validation" novalidate method="post" action="order-process">
                        <div class="mb-3">
                            <label for="OrderID" class="form-label">Order</label>
                            <select class="form-select" id="OrderID" name="OrderID" required>
                                <option disabled selected>Select Order</option>
                                <?php
                                $sql_orders = "SELECT o.OrderID, o.CustomerName, o.Product, o.Quantity, ic.PendingQuantity 
                                               FROM orders o 
                                               JOIN inventory_checks ic ON o.InventoryCheckID = ic.InventoryCheckID 
                                               WHERE o.is_delete = 0 AND o.Status = 'SentToProduction'";
                                $result_orders = mysqli_query($conn, $sql_orders);
                                if (mysqli_num_rows($result_orders) > 0) {
                                    while ($row_order = mysqli_fetch_assoc($result_orders)) {
                                        echo '<option value="' . $row_order['OrderID'] . '" 
                                                    data-customer_name="' . htmlspecialchars($row_order['CustomerName']) . '" 
                                                    data-product="' . htmlspecialchars($row_order['Product']) . '" 
                                                    data-quantity="' . $row_order['PendingQuantity'] . '">
                                                    ' . htmlspecialchars($row_order['CustomerName']) . ' - ' . htmlspecialchars($row_order['Product']) . ' (Qty: ' . $row_order['PendingQuantity'] . ')
                                              </option>';
                                    }
                                } else {
                                    echo '<option value="">No pending orders found</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="CustomerName" class="form-label">Customer Name</label>
                            <input type="text" class="form-control" name="CustomerName" id="CustomerName" readonly required>
                        </div>
                        <div class="mb-3">
                            <label for="Product" class="form-label">Product</label>
                            <input type="text" class="form-control" name="Product" id="Product" readonly required>
                        </div>
                        <div class="mb-3">
                            <label for="Quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="Quantity" id="Quantity" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="WagesPerPiece" class="form-label">Wages Per Piece</label>
                            <input type="number" class="form-control" name="WagesPerPiece" id="WagesPerPiece" step="0.01" min="0" placeholder="Enter Wages Per Piece" required>
                        </div>
                        <div class="mb-3">
                            <label for="ProductionDueDate" class="form-label">Production Due Date</label>
                            <input type="date" class="form-control" name="ProductionDueDate" id="ProductionDueDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="ArtisanID" class="form-label">Artisan Name</label>
                            <select class="form-select" name="ArtisanID" id="ArtisanID" required>
                                <option disabled selected>Select Artisan Name</option>
                                <?php
                                $sql_artisan = "SELECT * FROM artisans WHERE is_delete = 0";
                                $result_artisan = mysqli_query($conn, $sql_artisan);
                                if (mysqli_num_rows($result_artisan) > 0) {
                                    while ($row_artisan = mysqli_fetch_assoc($result_artisan)) {
                                        echo '<option value="' . $row_artisan['ArtisanID'] . '">' . htmlspecialchars($row_artisan['ArtisanName']) . '</option>';
                                    }
                                } else {
                                    echo '<option value="">No artisans found</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="DepartmentID" class="form-label">Department Name</label>
                            <select class="form-select" name="DepartmentID" id="DepartmentID" required>
                                <option disabled selected>Select Department Name</option>
                                <?php
                                $sql_dept = "SELECT * FROM departments WHERE is_delete = 0";
                                $result_dept = mysqli_query($conn, $sql_dept);
                                if (mysqli_num_rows($result_dept) > 0) {
                                    while ($row_dept = mysqli_fetch_assoc($result_dept)) {
                                        echo '<option value="' . $row_dept['DepartmentID'] . '">' . htmlspecialchars($row_dept['DepartmentName']) . '</option>';
                                    }
                                } else {
                                    echo '<option value="">No departments found</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success" name="Assign_Order">Assign Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Order Modal -->
    <div class="modal fade" id="editpaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header p-4 pb-0">
                    <h5 class="modal-title">Edit Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="edit-order-form" class="needs-validation" novalidate method="post" action="order-process">
                        <input type="hidden" name="OrderID" id="edit_order_id">
                        <div class="mb-3">
                            <label for="CustomerName" class="form-label">Customer Name</label>
                            <input type="text" class="form-control" name="CustomerName" id="edit_customer_name" readonly required>
                        </div>
                        <div class="mb-3">
                            <label for="Product" class="form-label">Product</label>
                            <input type="text" class="form-control" name="Product" id="edit_product" readonly required>
                        </div>
                        <div class="mb-3">
                            <label for="Quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="Quantity" id="edit_quantity" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="WagesPerPiece" class="form-label">Wages Per Piece</label>
                            <input type="number" class="form-control" name="WagesPerPiece" id="edit_wages_per_piece" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="ProductionDueDate" class="form-label">Production Due Date</label>
                            <input type="date" class="form-control" name="ProductionDueDate" id="edit_production_due_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="ArtisanID" class="form-label">Artisan Name</label>
                            <select class="form-select" name="ArtisanID" id="edit_artisan_id" required>
                                <option disabled selected>Select Artisan Name</option>
                                <?php
                                $sql_artisan = "SELECT * FROM artisans WHERE is_delete = 0";
                                $result_artisan = mysqli_query($conn, $sql_artisan);
                                if (mysqli_num_rows($result_artisan) > 0) {
                                    while ($row_artisan = mysqli_fetch_assoc($result_artisan)) {
                                        echo '<option value="' . $row_artisan['ArtisanID'] . '">' . htmlspecialchars($row_artisan['ArtisanName']) . '</option>';
                                    }
                                } else {
                                    echo '<option value="">No artisans found</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="DepartmentID" class="form-label">Department Name</label>
                            <select class="form-select" name="DepartmentID" id="edit_department_id" required>
                                <option disabled selected>Select Department Name</option>
                                <?php
                                $sql_dept = "SELECT * FROM departments WHERE is_delete = 0";
                                $result_dept = mysqli_query($conn, $sql_dept);
                                if (mysqli_num_rows($result_dept) > 0) {
                                    while ($row_dept = mysqli_fetch_assoc($result_dept)) {
                                        echo '<option value="' . $row_dept['DepartmentID'] . '">' . htmlspecialchars($row_dept['DepartmentName']) . '</option>';
                                    }
                                } else {
                                    echo '<option value="">No departments found</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success" name="Edit_Order">Update Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Approve Order Modal -->
    <div class="modal fade" id="approvepaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header p-4 pb-0">
                    <h5 class="modal-title">Approve Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="approve-order-form" class="needs-validation" novalidate method="post" action="quality-control-process">
                        <div class="mb-3">
                            <label for="ApprovedQuantity" class="form-label">Approved Quantity</label>
                            <input type="number" class="form-control" name="ApprovedQuantity" id="approve_quantity" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="ApprovalDate" class="form-label">Approval Date</label>
                            <input type="date" class="form-control" name="ApprovalDate" id="ApprovalDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="OrderID" class="form-label">Order</label>
                            <input type="text" class="form-control" id="approve_order_id" readonly>
                            <input type="hidden" name="OrderID" id="approve_order_id_new">
                        </div>
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success" name="Approve_Order">Approve Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Bulk Approve Orders Modal -->
    <div class="modal fade" id="bulkapprovepaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header p-4 pb-0">
                    <h5 class="modal-title">Bulk Approve Orders</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="bulk-approve-form" class="needs-validation" novalidate method="post" action="quality-control-process">
                        <table class="table table-striped table-hover" id="bulkApproveTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Artisan</th>
                                    <th>Quantity to Approve</th>
                                    <th>Approval Date</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
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
</div>

<script>
    $(document).ready(function() {
        // Populate Add Order modal fields based on selected OrderID
        $('#OrderID').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var customerName = selectedOption.data('customer_name');
            var product = selectedOption.data('product');
            var quantity = selectedOption.data('quantity');
            $('#CustomerName').val(customerName || '');
            $('#Product').val(product || '');
            $('#Quantity').val(quantity || '');
            $('#Quantity').attr('max', quantity || '');
        });

        // Populate Edit Order modal
        $(document).on('click', '.edit', function() {
            var $row = $(this).closest('tr');
            $('#edit_order_id').val($row.data('order_id'));
            $('#edit_customer_name').val($row.data('customer_name'));
            $('#edit_product').val($row.data('product'));
            $('#edit_quantity').val($row.data('quantity'));
            $('#edit_quantity').attr('max', $row.data('pending_quantity'));
            $('#edit_wages_per_piece').val($row.data('wages_per_piece'));
            $('#edit_production_due_date').val($row.data('production_due_date'));
            $('#edit_department_id').val($row.data('department_id'));
            $('#edit_artisan_id').val($row.data('artisan_id'));
            var myModal = new bootstrap.Modal(document.getElementById('editpaymentModal'));
            myModal.show();
        });

        // Populate Approve Order modal
        $(document).on('click', '.approve', function() {
            var $row = $(this).closest('tr');
            $('#approve_quantity').val($row.data('quantity'));
            $('#approve_quantity').attr('max', $row.data('pending_quantity'));
            $('#approve_order_id').val($row.data('product'));
            $('#approve_order_id_new').val($row.data('order_id'));
            var myModal = new bootstrap.Modal(document.getElementById('approvepaymentModal'));
            myModal.show();
        });

        // Select All checkbox logic
        $('#checkall').on('change', function() {
            $('input[name="selectedOrders[]"]').each(function() {
                var status = $(this).closest('tr').find('td:nth-child(7) span').text().trim();
                if (status === 'In Process' || status === 'Overdue') {
                    $(this).prop('checked', $('#checkall').is(':checked'));
                }
            });
        });

        // Individual checkbox logic
        $('input[name="selectedOrders[]"]').on('change', function() {
            if (!$(this).is(':checked')) {
                $('#checkall').prop('checked', false);
            } else if ($('input[name="selectedOrders[]"]:checked').length === $('input[name="selectedOrders[]"]').length) {
                $('#checkall').prop('checked', true);
            }
        });

        // Bulk Approve modal
        $('#bulkApproveBtn').on('click', function() {
            var selectedOrders = [];
            $('input[name="selectedOrders[]"]:checked').each(function() {
                var $row = $(this).closest('tr');
                selectedOrders.push({
                    order_id: $row.data('order_id'),
                    product: $row.data('product'),
                    artisan: $row.data('artisan_name'),
                    quantity: $row.data('quantity'),
                    pending_quantity: $row.data('pending_quantity')
                });
            });
            if (selectedOrders.length === 0) {
                alert('Please select at least one order to approve.');
                return;
            }
            var modalBody = $('#bulkApproveTable tbody');
            modalBody.empty();
            selectedOrders.forEach(function(order) {
                var row = '<tr>' +
                    '<td>' + order.product + '</td>' +
                    '<td>' + (order.artisan || 'Not Assigned') + '</td>' +
                    '<td><input type="number" name="ApprovedQuantity[]" class="form-control" value="' + order.quantity + '" max="' + order.pending_quantity + '" required></td>' +
                    '<td><input type="date" name="ApprovalDate[]" class="form-control" required></td>' +
                    '<td><input type="hidden" name="OrderID[]" value="' + order.order_id + '"></td>' +
                    '</tr>';
                modalBody.append(row);
            });
            var myModal = new bootstrap.Modal(document.getElementById('bulkapprovepaymentModal'));
            myModal.show();
        });

        // Form validation for quantity
        $('#add-order-form, #edit-order-form').on('submit', function(e) {
            var $quantityInput = $(this).find('input[name="Quantity"]');
            var maxQuantity = parseInt($quantityInput.attr('max')) || Infinity;
            var quantity = parseInt($quantityInput.val());
            if (quantity > maxQuantity) {
                alert('Quantity cannot exceed the pending quantity (' + maxQuantity + ').');
                e.preventDefault();
                return false;
            }
            $(this).find('button[type="submit"]').prop('disabled', true).html('Submitting...');
        });
    });
</script>

<?php include "assets/includes/footer.php"; ?>