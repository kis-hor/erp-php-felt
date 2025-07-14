<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "config.php";
$title = 'View Sales Orders';
include "assets/includes/header.php";

// Pagination settings
$limit = 10; // Orders per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM sales_orders";
$count_result = mysqli_query($conn, $count_sql);
$total_orders = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_orders / $limit);

// Fetch paginated sales orders
$sql = "SELECT SalesOrderID, PONumber, OrderDate, CustomerName 
        FROM sales_orders 
        ORDER BY OrderDate DESC
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
if (!$result) {
    $_SESSION['error'] = 'Database error: ' . mysqli_error($conn);
}
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">View Sales Orders</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item active"><i class="las la-angle-right"></i>View Sales Orders</li>
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
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title"><b>Sales Orders</b></h5>
                                <a href="add_sales_order.php" class="btn btn-primary">Add New Order</a>
                            </div>
                            <div class="table-responsive table-card">
                                <table class="table table-hover table-nowrap align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted text-uppercase">
                                            <th>#</th>
                                            <th>PO Number</th>
                                            <th>Customer Name</th>
                                            <th>Order Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result && mysqli_num_rows($result) > 0) {
                                            $serial = $offset + 1;
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                echo '<tr>';
                                                echo '<td>' . $serial++ . '</td>';
                                                echo '<td>' . htmlspecialchars($row['PONumber']) . '</td>';
                                                echo '<td>' . htmlspecialchars($row['CustomerName']) . '</td>';
                                                echo '<td>' . htmlspecialchars($row['OrderDate']) . '</td>';
                                                echo '<td>
                                                        <a href="edit_sales_order.php?sales_order_id=' . $row['SalesOrderID'] . '" class="btn btn-primary btn-sm">Edit</a>
                                                        <a href="view_sales_order.php?sales_order_id=' . $row['SalesOrderID'] . '" class="btn btn-info btn-sm">View</a>
                                                        <a href="delete_sales_order.php?sales_order_id=' . $row['SalesOrderID'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this sales order?\');">Delete</a>
                                                      </td>';
                                                echo '</tr>';
                                            }
                                        } else {
                                            echo '<tr><td colspan="5" class="text-center">No sales orders found.</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Pagination -->
                            <nav>
                                <ul class="pagination justify-content-center mt-3">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a></li>
                                    <?php endif; ?>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a></li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<?php
include "assets/includes/footer.php";
?>