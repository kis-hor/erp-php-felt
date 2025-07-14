<?php
session_start();
include "config.php";
$title = 'Production Dashboard';
include "assets/includes/header.php";
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Production Dashboard</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr class="text-muted text-uppercase">
                                            <th>PO Number</th>
                                            <th>Customer Name</th>
                                            <th>Sales Person</th>
                                            <th>Status</th>
                                            <th>Progress</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT DISTINCT 
                                                    so.PONumber,
                                                    so.CustomerName,
                                                    so.SalespersonName,
                                                    invf.Status,
                                                    invf.Progress,
                                                    invf.InventoryFulfillmentID
                                               FROM inventory_fulfillment invf
                                               JOIN sales_order_products sop ON invf.SalesOrderProductID = sop.SalesOrderProductID
                                               JOIN sales_orders so ON sop.SalesOrderID = so.SalesOrderID
                                               WHERE invf.SentToProduction = 1
                                               ORDER BY so.PONumber";

                                        $result = mysqli_query($conn, $sql);
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($row['PONumber']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['CustomerName']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['SalespersonName']) . '</td>';
                                            echo '<td><span class="badge bg-warning">In Progress</span></td>';
                                            echo '<td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar" role="progressbar" 
                                                             style="width: ' . $row['Progress'] . '%;" 
                                                             aria-valuenow="' . $row['Progress'] . '" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">' . $row['Progress'] . '%</div>
                                                    </div>
                                                  </td>';
                                            echo '<td>
                                                    <a href="view_production_details.php?id=' . $row['InventoryFulfillmentID'] . '" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> View Details
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

<?php include "assets/includes/footer.php"; ?>