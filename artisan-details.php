<?php
session_start();
if (!isset($_SESSION['Username'])) {
    header('Location: login');
    exit;
}
include "config.php";
$title = 'Production Accounts';
include "assets/includes/header.php";
?>
<style>
    .modal-lg{
        --in-modal-width: 100%;
    }
</style>
<!-- Start right Content here -->
<!-- ============================================================== -->
<div class="main-content">

    <div class="page-content">
        <div class="container-fluid">


            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Accounts</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                                <li class=" active"><i class="las la-angle-right"></i>Accounts List</li>
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
                $artisan_id = mysqli_real_escape_string($conn, $_GET['id']);
                $sql = "SELECT * FROM artisans INNER JOIN departments Using(DepartmentID) WHERE ArtisanID = {$artisan_id}";
                $result = mysqli_query($conn, $sql);
                if (mysqli_num_rows($result) > 0) {
                    $Arow = mysqli_fetch_assoc($result);


                ?>



            </div>
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <form method="get">
                            <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" class="form-control" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : '' ?>" name="start_date" placeholder="Start Date" required>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label class="form-label">End Date</label>
                                        <input type="date" class="form-control" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : '' ?>" name="end_date" placeholder="End Date" required>
                                    </div>
                                </div>
                                <div class="col-4 d-flex justify-content-start align-items-center">
                                    <button class="btn btn-success" type="submit">Search</button>
                                    <a href="artisan-details.php?id=<?php echo $_GET['id']; ?>" class="btn btn-primary">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="pb-3">Atisan Name : <span class="text-primary"><?php echo $Arow['ArtisanName'] ?></span> | Department Name : <span class="text-primary"><?php echo $Arow['DepartmentName'] ?></span></h3>
                            <h5 class="pb-3">Completed Orders :</h5>
                            <div class="table-responsive table-card">
                                <table class="table table-hover table-nowrap align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted text-uppercase">
                                            <th scope="col">Order ID</th>
                                            <th scope="col">Product</th>
                                            <th scope="col">Quantity</th>
                                            <th scope="col">Approved Quantity</th>
                                            <th scope="col">Wages Per Piece</th>
                                            <th scope="col">Production Due Date</th>
                                            <th scope="col">Total Wages</th>
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
                                        
                                        // Initialize the SQL query with common conditions
                                        $sql_where = "orders.is_delete = 0 AND ArtisanID = {$Arow['ArtisanID']}";
                                        
                                        // Check if a date is provided and add it to the SQL query
                                        if (isset($_GET['start_date']) && !empty($_GET['start_date']) && isset($_GET['end_date']) && !empty($_GET['end_date'])) {
                                            $start_date = $_GET['start_date'];
                                            $end_date = $_GET['end_date'];
                                            $sql_where .= " AND DATE(ProductionDueDate) BETWEEN '$start_date' AND '$end_date'";
                                        }
                                        
                                        // Get the total number of records
                                        $sql_count = "SELECT COUNT(*) as total FROM orders INNER JOIN quality_control Using(OrderID) WHERE $sql_where";
                                        $result_count = mysqli_query($conn, $sql_count);
                                        $total_records = mysqli_fetch_assoc($result_count)['total'];
                                        
                                        // Calculate total pages
                                        $total_pages = ceil($total_records / $records_per_page);
                                        
                                        // Get records for the current page
                                        $sql = "SELECT * FROM orders INNER JOIN quality_control Using(OrderID) WHERE $sql_where ORDER BY OrderID DESC LIMIT $offset, $records_per_page";
                                        $result = mysqli_query($conn, $sql) or die("Query Failed");
                                        
                                        if (mysqli_num_rows($result) > 0) {
                                            $g_total = 0;
                                            while ($row = mysqli_fetch_assoc($result)) {
                                        ?>
                                                <tr>
                                                    <td><?php echo $row['OrderID']; ?></td>
                                                    <td><?php echo $row['Product']; ?></td>
                                                    <td><?php echo $row['Quantity']; ?></td>
                                                    <td><?php echo $row['ApprovedQuantity']; ?></td>
                                                    <td><?php echo number_format($row['WagesPerPiece']); ?></td>
                                                    <td><?php echo date('Y-m-d A', strtotime($row['ProductionDueDate'])) ?></td>
                                                    <td><?php echo number_format($row['WagesPerPiece']  * $row['ApprovedQuantity']); ?></td>
                                                </tr>
                                        <?php
                                                $g_total += $row['WagesPerPiece']  * $row['ApprovedQuantity'];
                                            }
                                        } else {
                                            echo '
                                            <tr>
                                                <td colspan="8"><h2>No Records Found</h2></td>
                                            </tr>';
                                        }
                                        ?>


                                    </tbody>

                                    <!-- end tbody -->
                                </table><!-- end table -->
                            </div><!-- end table responsive -->
                        </div>
                        <style>
                            .crat-footer{
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                padding: 20px;

                            }
                        </style>
                        <div class="crat-footer">
                            <h3>Grand Total : <span class="badge bg-success-subtle text-success "><?php echo number_format($g_total) ?></span></h3> 
                            <?php if($g_total >0){?>
                            <div class="d-flex align-items-center ">
                                <h3 class="me-2">Print Report : </h3>
                                <button class="btn btn-soft-info " onclick="previewPDF(<?php echo $artisan_id; ?>)"><i class="bx bx-printer fs-22"></i></button>
                            </div>
                            <?php }?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
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
   
    <!-- edit user Modal -->
    <!-- Modal -->
    <div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0">
                <div class="modal-header">
                    <h5 class="modal-title">PDF Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe id="pdfIframe" src="" style="width:100%; height:600px;" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!--end modal-->
</div>
<!-- <a href="" ></a> -->
<script>
    function previewPDF(ArtisanID) {
        // Set the iframe source to the PHP script that generates the PDF
        document.getElementById('pdfIframe').src = 'generate_pdf.php?ArtisanID=' + ArtisanID;
        // Show the modal
        new bootstrap.Modal(document.getElementById('pdfPreviewModal')).show();
    }
</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<?php
include "assets/includes/footer.php";
?>