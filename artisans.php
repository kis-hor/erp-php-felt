<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['Username'])) {
    header('Location: login');
    exit;
}
include "config.php";
$title = 'Production Users';
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
                        <h4 class="mb-sm-0">Artisans</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                                <li class=" active"><i class="las la-angle-right"></i>Artisans List</li>
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

                <div class="col-sm-4">
                    <button class="btn btn-primary addPayment-modal" data-bs-toggle="modal" data-bs-target="#addpaymentModal"><i class='bx bx-user-plus'></i> Add New</button>
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
                                            <th scope="col">Artisans ID</th>
                                            <th scope="col">Artisans Name</th>
                                            <th scope="col">Specialization</th>
                                            <th scope="col">Join Date</th>
                                            <th scope="col">Department Name</th>
                                            <th scope="col">Status</th>
                                            <th scope="col" style="width: 12%;">Action</th>

                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        if (isset($_GET['status']) && $_GET['status'] == 'active') {
                                            $statusQuery = 'AND orders.OrderID IS NOT NULL';
                                        } else {
                                            $statusQuery = '';
                                        }
                                        // Set the number of records per page
                                        $records_per_page = 10;

                                        // Get the current page number from URL, default is 1 if not present
                                        $current_page = isset($_GET['page']) ? $_GET['page'] : 1;

                                        // Calculate the offset
                                        $offset = ($current_page - 1) * $records_per_page;

                                        // Get the total number of records with order count
                                        $sql_count = "SELECT COUNT(DISTINCT artisans.ArtisanID) as total
                                                      FROM artisans
                                                      INNER JOIN departments ON artisans.DepartmentID = departments.DepartmentID
                                                      LEFT JOIN orders ON artisans.ArtisanID = orders.ArtisanID
                                                      WHERE artisans.is_delete = 0 AND departments.is_delete = 0";
                                        $result_count = mysqli_query($conn, $sql_count);
                                        $total_records = mysqli_fetch_assoc($result_count)['total'];

                                        // Calculate total pages
                                        $total_pages = ceil($total_records / $records_per_page);

                                        // Get records for the current page along with order count
                                        $sql = "SELECT
                                        artisans.ArtisanID,
                                        artisans.ArtisanName,
                                        artisans.Specialization,
                                        artisans.JoinDate,
                                        departments.DepartmentName,
                                        COUNT(orders.OrderID) AS OrderCount
                                        FROM artisans
                                        INNER JOIN departments ON artisans.DepartmentID = departments.DepartmentID
                                        LEFT JOIN orders ON artisans.ArtisanID = orders.ArtisanID
                                        AND orders.Status IN ('inprocess', 'pending')  -- Only count incomplete orders
                                        WHERE artisans.is_delete = 0 AND departments.is_delete = 0 $statusQuery
                                        GROUP BY artisans.ArtisanID, artisans.ArtisanName, artisans.Specialization, artisans.JoinDate, departments.DepartmentName
                                        ORDER BY artisans.ArtisanID DESC
                                        LIMIT $offset, $records_per_page";

                                        $result = mysqli_query($conn, $sql) or die("Query Failed");
                                        // $result = mysqli_query($conn, $sql) or die("Query Failed");
                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                        ?>

                                                <tr
                                                    data-artisan_id="<?php echo isset($row['ArtisanID']) ? htmlspecialchars($row['ArtisanID']) : ''; ?>"
                                                    data-artisan_name="<?php echo isset($row['ArtisanName']) ? htmlspecialchars($row['ArtisanName']) : ''; ?>"
                                                    data-specialization="<?php echo isset($row['Specialization']) ? htmlspecialchars($row['Specialization']) : ''; ?>"
                                                    data-join_date="<?php echo isset($row['JoinDate']) ? htmlspecialchars($row['JoinDate']) : ''; ?>"
                                                    data-department_id="<?php echo isset($row['DepartmentID']) ? htmlspecialchars($row['DepartmentID']) : ''; ?>">


                                                    <td><?php echo $row['ArtisanID']; ?></td>
                                                    <td><?php echo $row['ArtisanName']; ?></td>
                                                    <td><?php echo $row['Specialization']; ?></td>
                                                    <td><?php echo date('Y-m-d A', strtotime($row['JoinDate'])); ?></td>
                                                    <td><?php echo $row['DepartmentName']; ?></td>
                                                    <td>
                                                        <?php if ($row['OrderCount'] > 0): ?>
                                                            <span class="badge bg-success-subtle text-success  p-2">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger-subtle text-danger  p-2">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>

                                                    <td>
                                                        <ul class="list-inline hstack  mb-0">
                                                            <li class="list-inline-item edit" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Edit">
                                                                <button href="" class="btn btn-soft-info btn-sm d-inline-block " data-bs-toggle="modal2" data-bs-target="#editpaymentModal">
                                                                    <i class="las la-pen fs-17 align-middle"></i>
                                                                </button>
                                                            </li>
                                                            <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Remove">
                                                                <a href="artisan-process?id=<?php echo $row['ArtisanID']; ?>" class="btn btn-soft-danger btn-sm d-inline-block" onclick="return confirm('Do you want to delete this Artisan?')">
                                                                    <i class="las la-file-download fs-17 align-middle"></i>
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
    <div class="modal fade" id="editpaymentModal" tabindex="-2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header p-4 pb-0">
                    <h5 class="modal-title" id="createMemberLabel">Edit Artisan</h5>
                    <button type="button" class="btn-close" id="createMemberBtn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="memberlist-form" class="needs-validation" novalidate enctype="multipart/form-data" method="post" action="artisan-process">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="mb-3 mt-4">
                                    <input type="hidden" name="ArtisanID" id="edit_artisan_id">
                                    <label for="old_artisan_name" class="form-label">Artisan Name</label>
                                    <input type="text" class="form-control" name="ArtisanName" id="old_artisan_name" placeholder="Enter Artisan Name" required>
                                </div>


                                <div class="mb-3">
                                    <label for="old_specialization" class="form-label">Specialization</label>
                                    <input type="text" class="form-control" id="old_specialization" name="Specialization" placeholder="Enter Specialization" required>
                                </div>

                                <div class="mb-3">
                                    <label for="edit_join_date" class="form-label">Join Date</label>
                                    <input type="date" class="form-control" id="edit_join_date" name="JoinDate" placeholder="Enter Join Date" required>
                                </div>

                                <div class="mb-4">
                                    <label for="edit_department_id" class="form-label">Department Name</label>
                                    <select class="form-select" name="DepartmentID" id="edit_department_id" required>
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

                                <div class="hstack gap-2 justify-content-end">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-success" name="Edit_Artisan" id="addNewMember">Update Artisan</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!--end modal-->
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