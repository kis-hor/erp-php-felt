<?php
session_start();
include "config.php";
$title = 'Production Assignments';
include "assets/includes/header.php";
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <h4 class="mb-4">Production Assignments</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Orders ID</th>
                            <th>Artisan</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Wages/unit</th>
                            <th>Due Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT pa.*, so.PONumber, so.CustomerName, sop.ProductName
                                FROM production_assignments pa
                                JOIN sales_order_products sop ON pa.SalesOrderProductID = sop.SalesOrderProductID
                                JOIN sales_orders so ON sop.SalesOrderID = so.SalesOrderID
                                WHERE pa.is_delete = 0";
                        $result = mysqli_query($conn, $sql);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['OrderID']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['ArtisanName']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['CustomerName']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['ProductName']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['AssignedQuantity']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['Status']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['WagesPerPiece']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['ProductionDueDate']) . '</td>';
                            echo '<td><!-- Action buttons here --></td>';
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