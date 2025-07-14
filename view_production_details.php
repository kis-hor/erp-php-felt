<?php
session_start();
include "config.php";
$title = 'Production Details';
include "assets/includes/header.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    $_SESSION['error'] = 'Invalid ID';
    header('Location: production_dashboard.php');
    exit;
}

$sql = "SELECT pa.*, sop.ProductName, sop.ProductDescription, sop.ProductImage,
               so.PONumber, so.CustomerName, so.SalespersonName
        FROM production_assignments pa
        JOIN sales_order_products sop ON pa.SalesOrderProductID = sop.SalesOrderProductID
        JOIN sales_orders so ON sop.SalesOrderID = so.SalesOrderID
        WHERE pa.OrderID = ? AND pa.is_delete = 0";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$details = mysqli_fetch_assoc($result);
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="mb-sm-0">Production Details - <?php echo htmlspecialchars($details['PONumber']); ?></h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h5>Order Details</h5>
                                        <p><strong>PO Number:</strong> <?php echo htmlspecialchars($details['PONumber']); ?></p>
                                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($details['CustomerName']); ?></p>
                                        <p><strong>Sales Person:</strong> <?php echo htmlspecialchars($details['SalespersonName']); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h5>Production Status</h5>
                                        <p><strong>Status:</strong> <span class="badge bg-warning">In Progress</span></p>
                                        <p><strong>Progress:</strong></p>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar"
                                                style="width: <?php echo $details['Progress']; ?>%;"
                                                aria-valuenow="<?php echo $details['Progress']; ?>"
                                                aria-valuemin="0"
                                                aria-valuemax="100"><?php echo $details['Progress']; ?>%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Product Information</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <?php if (!empty($details['ProductImage'])): ?>
                                                <?php
                                                $imgSrc = ltrim($details['ProductImage'], '/');
                                                $imgSrc = preg_replace('/^Uploads\//', 'Uploads/', $imgSrc); // Ensure correct folder
                                                $imgSrc = dirname($imgSrc) . '/' . rawurlencode(basename($imgSrc));
                                                ?>
                                                <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                                    alt="Product Photo"
                                                    class="img-fluid">
                                            <?php else: ?>
                                                <span class="text-muted">No photo</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-9">
                                            <p><strong>Product Name:</strong> <?php echo htmlspecialchars($details['ProductName']); ?></p>
                                            <p><strong>Description:</strong> <?php echo htmlspecialchars($details['ProductDescription']); ?></p>
                                            <p><strong>Pending Quantity:</strong> <?php echo $details['PendingQuantity']; ?></p>
                                            <p><strong>Remarks:</strong> <?php echo htmlspecialchars($details['Remarks']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "assets/includes/footer.php"; ?>