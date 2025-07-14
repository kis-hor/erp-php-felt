<?php
session_start();
include "config.php";
$title = 'Sales Orders Overview';
include "assets/includes/header.php";
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <h4 class="mb-4">Sales Orders</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Customer Name</th>
                            <th>Order Placed By</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Add error reporting at the top
                        error_reporting(E_ALL);
                        ini_set('display_errors', 1);

                        // Add debug output
                        echo "<!-- Debug: Starting query execution -->";

                        // Update the main query with error checking
                        $sql = "SELECT SalesOrderID, PONumber, CustomerName, SalespersonName, OrderDate 
                                FROM sales_orders 
                                ORDER BY OrderDate DESC";
                        $result = mysqli_query($conn, $sql);
                        if (!$result) {
                            echo '<div class="alert alert-danger">Error: ' . mysqli_error($conn) . '</div>';
                            exit;
                        }

                        // Update status query with prepared statement
                        while ($row = mysqli_fetch_assoc($result)) {
                            $po_id = $row['SalesOrderID'];
                            $status_sql = "SELECT 
                                            COUNT(*) AS total,
                                            SUM(CASE WHEN invf.PendingQuantity = 0 THEN 1 ELSE 0 END) AS fulfilled,
                                            SUM(CASE WHEN invf.SentToProduction = 1 THEN 1 ELSE 0 END) AS sent
                                        FROM sales_order_products sop
                                        LEFT JOIN inventory_fulfillment invf ON sop.SalesOrderProductID = invf.SalesOrderProductID
                                        WHERE sop.SalesOrderID = ?";

                            $stmt = mysqli_prepare($conn, $status_sql);
                            mysqli_stmt_bind_param($stmt, "i", $po_id);
                            mysqli_stmt_execute($stmt);
                            $status_result = mysqli_stmt_get_result($stmt);
                            $status_row = mysqli_fetch_assoc($status_result);

                            if ($status_row['fulfilled'] == $status_row['total'] && $status_row['total'] > 0) {
                                $status = 'Fulfilled';
                            } elseif ($status_row['sent'] == $status_row['total'] && $status_row['total'] > 0) {
                                $status = 'Sent to Production';
                            } else {
                                $status = 'Pending';
                            }

                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['PONumber']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['CustomerName']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['SalespersonName']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['OrderDate']) . '</td>';
                            echo '<td>' . $status . '</td>';
                            echo '<td><a href="inventory_fulfillment.php?sales_order_id=' . $row['SalesOrderID'] . '" class="btn btn-primary btn-sm">View & Fulfill</a></td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include "assets/includes/footer.php"; ?>