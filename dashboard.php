<?php
session_start();
if (!isset($_SESSION['Username'])) {
    header('Location: login');
    exit;
}

include "config.php";
$title = 'Production Dashboard';
include "assets/includes/header.php";

?>

<div class="main-content">

    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Dashboard</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>

                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <?php if ($_SESSION['Role'] == 'Admin') { ?>

                    <div class="col-12 col-sm-6">
                        <div class="card card-body">
                            <div class="d-flex mb-4 align-items-center">
                                <div class="flex-shrink-0" style="width: 50px; height: 50px;">
                                    <div class="avatar-title bg-success-subtle text-success  rounded">
                                        <i class="fa-solid fa-user fs-22"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <p class="text-muted mb-0">Total:</p>
                                    <h5 class="card-title mb-1">Active Artisan</h5>
                                </div>
                                <div>
                                    <?php
                                    $sql_active_artisans = "
                                        SELECT COUNT(DISTINCT artisans.ArtisanID) AS ActiveArtisans
                                        FROM artisans
                                        LEFT JOIN orders ON artisans.ArtisanID = orders.ArtisanID
                                        WHERE artisans.is_delete = 0 AND orders.OrderID IS NOT NULL";
                                    $result_active_artisans = mysqli_query($conn, $sql_active_artisans);
                                    $row_active_artisans = mysqli_fetch_assoc($result_active_artisans);


                                    ?>
                                    <h2 class="mb-1 me-2"> <?php echo $row_active_artisans['ActiveArtisans']; ?></h2>
                                </div>
                            </div>
                            <!-- <p class="card-text text-muted">Expense Account</p> -->
                            <a href="artisans?status=active" class="btn btn-primary ">See Details</a>
                        </div>
                    </div><!-- end col -->
                    <div class="col-12 col-sm-6">
                        <div class="card card-body">
                            <div class="d-flex mb-4 align-items-center">
                                <div class="flex-shrink-0" style="width: 50px; height: 50px;">
                                    <div class="avatar-title bg-success-subtle text-success  rounded">
                                        <i class="fa-solid fa-building-user fs-22"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <p class="text-muted mb-0">Total:</p>
                                    <h5 class="card-title mb-1">Department</h5>
                                </div>
                                <div>
                                    <?php
                                    $sql = "SELECT count(DepartmentID) as Total_DepartmentID FROM departments WHERE is_delete = 0";
                                    $result = mysqli_query($conn, $sql);
                                    $row = mysqli_fetch_assoc($result);

                                    ?>
                                    <h2 class="mb-1 me-2"><?php echo $row['Total_DepartmentID'] ?></h2>
                                </div>
                            </div>
                            <!-- <p class="card-text text-muted">Expense Account</p> -->
                            <a href="departments" class="btn btn-primary ">See Details</a>
                        </div>
                    </div><!-- end col -->
                    <div class="col-12 col-sm-6">
                        <div class="card card-body">
                            <div class="d-flex mb-4 align-items-center">
                                <div class="flex-shrink-0" style="width: 50px; height: 50px;">
                                    <div class="avatar-title bg-success-subtle text-success  rounded">
                                        <i class="fa-solid fa-user-tie fs-22"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <p class="text-muted mb-0">Total:</p>
                                    <h5 class="card-title mb-1">Artisan</h5>
                                </div>
                                <div>
                                    <?php
                                    $sql = "SELECT count(ArtisanID) as Total_ArtisanID FROM artisans WHERE is_delete = 0";
                                    $result = mysqli_query($conn, $sql);
                                    $row = mysqli_fetch_assoc($result);

                                    ?>
                                    <h2 class="mb-1 me-2"><?php echo $row['Total_ArtisanID'] ?></h2>
                                </div>
                            </div>
                            <!-- <p class="card-text text-muted">Expense Account</p> -->
                            <a href="artisans" class="btn btn-primary ">See Details</a>
                        </div>
                    </div><!-- end col -->
                <?php } ?>
                <?php if ($_SESSION['Role'] == 'Admin' || $_SESSION['Role'] == 'Manager' || $_SESSION['Role'] == 'Quality Control' || $_SESSION['Role'] == 'Accounts') { ?>
                    <div class="col-12 col-sm-6">
                        <div class="card card-body">
                            <div class="d-flex mb-4 align-items-center">
                                <div class="flex-shrink-0" style="width: 50px; height: 50px;">
                                    <div class="avatar-title bg-success-subtle text-success  rounded">
                                        <i class="fa-solid fa-cart-shopping fs-22"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <p class="text-muted mb-0">Total:</p>
                                    <h5 class="card-title mb-1">Order</h5>
                                </div>
                                <div>
                                    <?php
                                    $sql = "SELECT count(OrderID) as Total_OrderID FROM orders WHERE is_delete = 0";
                                    $result = mysqli_query($conn, $sql);
                                    $row = mysqli_fetch_assoc($result);

                                    ?>
                                    <h2 class="mb-1 me-2"><?php echo $row['Total_OrderID'] ?></h2>
                                </div>
                            </div>
                            <!-- <p class="card-text text-muted">Expense Account</p> -->
                            <a href="orders" class="btn btn-primary ">See Details</a>
                        </div>
                    </div><!-- end col -->
                    <div class="col-12 col-sm-6">
                        <div class="card card-body">
                            <div class="d-flex mb-4 align-items-center">
                                <div class="flex-shrink-0" style="width: 50px; height: 50px;">
                                    <div class="avatar-title bg-success-subtle text-success  rounded">
                                        <i class="fa-solid fa-chalkboard-user fs-22"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <p class="text-muted mb-0">Total:</p>
                                    <h5 class="card-title mb-1">Inprocess Order</h5>
                                </div>
                                <div>
                                    <?php
                                        // Get the current date
                                        $current_date = date('Y-m-d');
                        
                                        // SQL to count inprocess orders that are not overdue
                                        $sql = "SELECT COUNT(OrderID) as Total_OrderID 
                                                FROM orders 
                                                WHERE is_delete = 0 
                                                AND Status = 'inprocess' 
                                                AND ProductionDueDate >= '$current_date'";
                                        
                                        $result = mysqli_query($conn, $sql);
                                        $row = mysqli_fetch_assoc($result);
                                        ?>
                                    <h2 class="mb-1 me-2"><?php echo $row['Total_OrderID']; ?></h2>
                                </div>
                            </div>
                            <!-- <p class="card-text text-muted">Expense Account</p> -->
                            <a href="orders" class="btn btn-primary ">See Details</a>
                        </div>
                    </div><!-- end col -->
                    <div class="col-12 col-sm-6">
                        <div class="card card-body">
                            <div class="d-flex mb-4 align-items-center">
                                <div class="flex-shrink-0" style="width: 50px; height: 50px;">
                                    <div class="avatar-title bg-success-subtle text-success  rounded">
                                        <i class="fa-solid fa-chalkboard-user fs-22"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <p class="text-muted mb-0">Total:</p>
                                    <h5 class="card-title mb-1">Approved Order</h5>
                                </div>
                                <div>
                                    <?php
                                    $sql = "SELECT count(OrderID) as Total_OrderID FROM orders WHERE is_delete = 0 AND Status = 'Approved'";
                                    $result = mysqli_query($conn, $sql);
                                    $row = mysqli_fetch_assoc($result);

                                    ?>
                                    <h2 class="mb-1 me-2"><?php echo $row['Total_OrderID'] ?></h2>
                                </div>
                            </div>
                            <!-- <p class="card-text text-muted">Expense Account</p> -->
                            <a href="quality-control" class="btn btn-primary ">See Details</a>
                        </div>
                    </div><!-- end col -->
                    <div class="col-12 col-sm-6">
                        <div class="card card-body">
                            <div class="d-flex mb-4 align-items-center">
                                <div class="flex-shrink-0" style="width: 50px; height: 50px;">
                                    <div class="avatar-title bg-danger-subtle text-danger rounded">
                                        <i class="fa-solid fa-exclamation-circle fs-22"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <p class="text-muted mb-0">Total:</p>
                                    <h5 class="card-title mb-1">Overdue Orders</h5>
                                </div>
                                <div>
                                    <?php
                                    // SQL to count overdue orders
                                    $current_date = date('Y-m-d');
                                    $sql = "SELECT count(OrderID) as Total_Overdue FROM orders 
                                            WHERE is_delete = 0 
                                            AND Status = 'inprocess' 
                                            AND ProductionDueDate < '$current_date'";
                                    
                                    $result = mysqli_query($conn, $sql);
                                    $row = mysqli_fetch_assoc($result);
                                    ?>
                                    <h2 class="mb-1 me-2"><?php echo $row['Total_Overdue']; ?></h2>
                                </div>
                            </div>
                            <a href="orders" class="btn btn-primary">See Details</a>
                        </div>
                    </div>
<!-- end col -->
                    <div class="col-12 col-sm-6">
                        <div class="card card-body">
                            <div class="d-flex mb-4 align-items-center">
                                <div class="flex-shrink-0" style="width: 50px; height: 50px;">
                                    <div class="avatar-title bg-success-subtle text-success  rounded">
                                        <i class="fa-solid fa-user fs-22"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <p class="text-muted mb-0">Total:</p>
                                    <h5 class="card-title mb-1">Users</h5>
                                </div>
                                <div>
                                    <?php
                                    $sql = "SELECT count(UserID) as Total_user FROM users WHERE is_delete = 0";
                                    $result = mysqli_query($conn, $sql);
                                    $row = mysqli_fetch_assoc($result);

                                    ?>
                                    <h2 class="mb-1 me-2"><?php echo $row['Total_user'] ?></h2>

                                </div>
                            </div>
                            <!-- <p class="card-text text-muted">Expense Account</p> -->
                            <a href="users" class="btn btn-primary ">See Details</a>
                        </div>
                    </div><!-- end col -->
                <?php } ?>
            </div>

        </div>
        <!-- container-fluid -->
    </div>
</div>


<?php
include "assets/includes/footer.php";
?>