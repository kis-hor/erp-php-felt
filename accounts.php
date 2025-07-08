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

            <div class="row">
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
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="get">
                                <div class="row">
                                    <div class="col-3">
                                        <div class="form-group">
                                            <select class="form-select" name="Artisan">
                                                <option disabled selected value="">Select Artisan Name</option>
                                                <?php
                                                $aSql = "SELECT * FROM artisans WHERE is_delete = 0";
                                                $aResult = mysqli_query($conn, $aSql);
                                                if (mysqli_num_rows($aResult) > 0) {
                                                    while ($arow = mysqli_fetch_assoc($aResult)) {
                                                        $select = ($_GET['Artisan'] == $arow['ArtisanName']) ? 'selected' : '';
                                                        echo '<option ' . $select . ' value="' . $arow['ArtisanName'] . '">' . $arow['ArtisanName'] . '</option>';
                                                    }
                                                } else {
                                                    echo '<option disabled selected>No Artisan Found</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <input type="date" class="form-control" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : '' ?>" name="start_date" placeholder="Start Date">
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <input type="date" class="form-control" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : '' ?>" name="end_date" placeholder="End Date">
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <button class="btn btn-success" type="submit">Search</button>
                                        <a href="accounts" class="btn btn-primary">Reset</a>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-hover table-nowrap align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted text-uppercase">
                                            <th scope="col">Order ID</th>
                                            <th scope="col">Artisan Name</th>
                                            <th scope="col">Total Order</th>
                                            <th scope="col">Total Wages</th>
                                            <th scope="col" style="width: 12%;">Action</th>

                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        // Set the number of records per page
                                        $records_per_page = 10;
                                        $current_page = isset($_GET['page']) ? $_GET['page'] : 1;
                                        $offset = ($current_page - 1) * $records_per_page;

                                        // Prepare filters
                                        $filter_conditions = "is_delete = 0";

                                        if (isset($_GET['Artisan']) && !empty($_GET['Artisan'])) {
                                            $Artisan = mysqli_real_escape_string($conn, $_GET['Artisan']);
                                            $filter_conditions .= " AND ArtisanName LIKE '$Artisan'";
                                        }

                                        if (isset($_GET['start_date']) && !empty($_GET['start_date']) && isset($_GET['end_date']) && !empty($_GET['end_date'])) {
                                            $start_date = mysqli_real_escape_string($conn, $_GET['start_date']);
                                            $end_date = mysqli_real_escape_string($conn, $_GET['end_date']);
                                            $filter_conditions .= " AND JoinDate BETWEEN '$start_date' AND '$end_date'";
                                        }

                                        // Get the total number of records
                                        $sql_count = "SELECT COUNT(*) as total FROM artisans WHERE $filter_conditions";
                                        $result_count = mysqli_query($conn, $sql_count);
                                        $total_records = mysqli_fetch_assoc($result_count)['total'];
                                        $total_pages = ceil($total_records / $records_per_page);

                                        // Get records for the current page
                                        $sql = "SELECT * FROM artisans WHERE $filter_conditions ORDER BY ArtisanID DESC LIMIT $offset, $records_per_page";
                                        $result = mysqli_query($conn, $sql);

                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                // Get total orders for this artisan
                                                $total_order = "SELECT COUNT(OrderID) as total_order FROM orders WHERE ArtisanID = {$row['ArtisanID']}";
                                                $order_result = mysqli_query($conn, $total_order);
                                                $row_order = mysqli_fetch_assoc($order_result);

                                                // Fetch total wages
                                                $total_wages = "SELECT SUM(qc.ApprovedQuantity * o.WagesPerPiece) AS total_wages 
                                                                FROM quality_control qc 
                                                                JOIN orders o ON qc.OrderID = o.OrderID 
                                                                JOIN artisans a ON a.ArtisanID = o.ArtisanID 
                                                                WHERE a.ArtisanID = {$row['ArtisanID']} 
                                                                GROUP BY a.ArtisanID";

                                                $wages_result = mysqli_query($conn, $total_wages);

                                                // Check if query is successful
                                                if (!$wages_result) {
                                                    echo "Wages Query Error: " . mysqli_error($conn);
                                                }

                                                // Fetch wages data if available
                                                if (mysqli_num_rows($wages_result) > 0) {
                                                    $row_wages = mysqli_fetch_assoc($wages_result);
                                                } else {
                                                    $row_wages['total_wages'] = 0; // Fallback if no wages found
                                                }
                                        ?>

                                                <tr>
                                                    <td><?php echo $row['ArtisanID']; ?></td>
                                                    <td><?php echo $row['ArtisanName']; ?></td>
                                                    <td><?php echo $row_order['total_order'] ?></td>
                                                    <td><?php echo empty($row_wages['total_wages']) ? 'No Wages' : $row_wages['total_wages']; ?></td>
                                                    <td>
                                                        <ul class="list-inline hstack mb-0">
                                                            <li class="list-inline-item edit" data-bs-toggle="tooltip" title="Details">
                                                                <a href="artisan-details.php?id=<?php echo $row['ArtisanID'] ?>" class="btn btn-soft-info btn-sm">
                                                                    <i class="las la-eye fs-17 align-middle"></i>
                                                                </a>
                                                            </li>
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
</div>
<!-- Add user Modal -->
<!-- Modal -->
<div class="modal fade" id="addpaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header p-4 pb-0">
                <h5 class="modal-title" id="createMemberLabel">Add User</h5>
                <button type="button" class="btn-close" id="createMemberBtn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="memberlist-form" class="needs-validation" novalidate enctype="multipart/form-data" method="post" action="artisan-process">
                    <div class="row">
                        <div class="col-lg-12">

                            <div class="mb-3 mt-4">
                                <label for="ArtisanName" class="form-label">Artisan Name</label>
                                <input type="text" class="form-control" name="ArtisanName" id="ArtisanName" placeholder="Enter Artisan Name" required>
                            </div>


                            <div class="mb-3">
                                <label for="Specialization" class="form-label">Specialization</label>
                                <input type="text" class="form-control" id="Specialization" name="Specialization" placeholder="Enter Specialization" required>
                            </div>

                            <div class="mb-3">
                                <label for="JoinDate" class="form-label">Join Date</label>
                                <input type="date" class="form-control" id="JoinDate" name="JoinDate" placeholder="Enter Join Date" required>
                            </div>

                            <div class="mb-4">
                                <label for="DepartmentID" class="form-label">Department Name</label>
                                <select class="form-select" name="DepartmentID" required>
                                    <option disabled selected>Select Department Name</option>
                                    <?php
                                    $sql = "SELECT * FROM departments WHERE is_delete = 0";
                                    $result = mysqli_query($conn, $sql);
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<option value="' . $row['DepartmentID'] . '">' . $row['DepartmentName'] . '</option>';
                                        }
                                    } else {
                                        echo '<option value="">No records found</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="UserID" class="form-label">User</label>
                                <select class="form-select" name="UserID" required>
                                    <option disabled selected>Select User</option>
                                    <?php
                                    $sql = "SELECT * FROM users WHERE is_delete = 0";
                                    $result = mysqli_query($conn, $sql);
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<option value="' . $row['UserID'] . '">' . $row['Username'] . '</option>';
                                        }
                                    } else {
                                        echo '<option value="">No records found</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="hstack gap-2 justify-content-end">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success" name="Add_Artisan" id="addNewMember">Add Artisan</button>
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



<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>
    $(document).on("click", '.edit', function() {
        var $row = $(this).closest('tr');
        var artisan_id = $row.data('artisan_id');
        var artisan_name = $row.data('artisan_name');
        var specialization = $row.data('specialization');
        var join_date = $row.data('join_date');
        var department_id = $row.data('department_id');
        var user_id = $row.data('user_id');


        // Update form fields
        $('#edit_artisan_id').val(artisan_id);
        $('#old_artisan_name').val(artisan_name);
        $('#old_specialization').val(specialization);
        $('#edit_join_date').val(join_date);
        $('#edit_department_id').val(department_id);
        $('#edit_user_id').val(user_id);

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