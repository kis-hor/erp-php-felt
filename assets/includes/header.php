<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="light" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>

    <meta charset="utf-8" />
    <title><?php echo isset($title) ? $title : 'Production Management' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- plugin css -->
    <link href="assets/libs/jsvectormap/css/jsvectormap.min.css" rel="stylesheet" type="text/css" />

    <!-- Layout config Js -->
    <script src="assets/js/layout.js"></script>
    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />




    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

</head>

<body>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">
                    <div class="d-flex">
                        <!-- LOGO -->
                        <div class="navbar-brand-box horizontal-logo">
                            <a href="dashboard" class="logo logo-dark">
                                <span class="logo-sm">
                                    <img src="assets/images/logo-sm.png" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="assets/images/logo-dark.png" alt="" height="21">
                                </span>
                            </a>

                            <a href="dashboard" class="logo logo-light">
                                <span class="logo-sm">
                                    <img src="assets/images/logo-sm.png" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="assets/images/logo-light.png" alt="" height="21">
                                </span>
                            </a>
                        </div>

                        <button type="button"
                            class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger"
                            id="topnav-hamburger-icon">
                            <span class="hamburger-icon">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>



                    </div>

                    <div class="d-flex align-items-center">



                        <div class="dropdown d-md-none topbar-head-dropdown header-item">
                            <button type="button" class="btn btn-icon btn-topbar btn-ghost-primary rounded-circle"
                                id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <i class="bx bx-search fs-22"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                                aria-labelledby="page-header-search-dropdown">
                                <form class="p-3">
                                    <div class="form-group m-0">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search ..."
                                                aria-label="Recipient's username">
                                            <button class="btn btn-primary" type="submit"><i
                                                    class="mdi mdi-magnify"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="dropdown header-item">
                            <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <span class="d-flex align-items-center">
                                    <span class="text-start ms-xl-2">
                                        <?php
                                        $sql = "SELECT * FROM users WHERE UserID = {$_SESSION['UserID']}";
                                        $result = mysqli_query($conn, $sql);
                                        $row = mysqli_fetch_assoc($result);


                                        ?>
                                        <span class="d-none d-xl-inline-block fw-medium user-name-text fs-16"><?php echo $row['Username'] ?>
                                            <i class="las la-angle-down fs-12 ms-1"></i></span>
                                    </span>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <!-- item-->
                                <a class="dropdown-item" href="#"><i class="bx bx-user fs-15 align-middle me-1"></i>
                                    <span key="t-profile">Profile</span></a>

                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="logout"><i
                                        class="bx bx-power-off fs-15 align-middle me-1 text-danger"></i> <span
                                        key="t-logout">Logout</span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- removeNotificationModal -->
        <div id="removeNotificationModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            id="NotificationModalbtn-close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mt-2 text-center">
                            <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop"
                                colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                            <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                                <h4>Are you sure ?</h4>
                                <p class="text-muted mx-4 mb-0">Are you sure you want to remove this Notification ?</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                            <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn w-sm btn-danger" id="delete-notification">Yes, Delete
                                It!</button>
                        </div>
                    </div>

                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <!-- ========== App Menu ========== -->
        <div class="app-menu navbar-menu">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <!-- Dark Logo-->
                <a href="dashboard" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="assets/images/logo-sm.png" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="assets/images/logo-dark.png" alt="" height="21">
                    </span>
                </a>
                <!-- Light Logo-->
                <a href="dashboard" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="assets/images/logo-sm.png" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="assets/images/logo-light.png" alt="" height="21">
                    </span>
                </a>
                <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
                    id="vertical-hover">
                    <i class="ri-record-circle-line"></i>
                </button>
            </div>

            <div id="scrollbar">
                <div class="container-fluid">

                    <div id="two-column-menu">
                    </div>
                    <ul class="navbar-nav" id="navbar-nav">
                        <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="dashboard">
                                <i class="fa-solid fa-house-chimney"></i> <span data-key="t-dashboard">Dashboard</span>
                            </a>
                        </li>

                        <li class="menu-title"><i class='bx bx-dots-horizontal-rounded'></i> <span data-key="t-pages">Pages</span></li>
                        <!-- Admin side work -->
                        <?php if ($_SESSION['Role'] == 'Admin') { ?>
                            <li class="nav-item">
                                <a class="nav-link menu-link" href="users">
                                    <i class="fa-solid fa-user"></i><span data-key="t-users">Users</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link menu-link" href="departments">
                                    <i class="fa-solid fa-building-user"></i> <span data-key="t-departments">Departments</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link menu-link" href="artisans">
                                    <i class="fa-solid fa-user-tie"></i> <span data-key="t-artisans">Artisans</span>
                                </a>
                            </li>

                        <?php } ?>
                        <?php if ($_SESSION['Role'] == 'Admin' || $_SESSION['Role'] == 'Quality Control' || $_SESSION['Role'] == 'Manager') { ?>
                            <li class="nav-item">
                                <a class="nav-link menu-link" href="orders">
                                    <i class="fa-solid fa-cart-shopping"></i> <span data-key="t-orders">Orders</span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($_SESSION['Role'] == 'Admin' || $_SESSION['Role'] == 'Quality Control') { ?>
                            <!-- <li class="nav-item">
                                <a class="nav-link menu-link" href="quality-control">
                                    <i class="fa-solid fa-chalkboard-user"></i> <span data-key="t-QualityControl">Quality Control</span>
                                </a>
                            </li> -->
                            <li class="nav-item">
                                <a class="nav-link menu-link" href="dispatch">
                                    <i class="fa-solid fa-truck"></i> <span data-key="t-dispatch">Dispatch</span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($_SESSION['Role'] == 'Admin' || $_SESSION['Role'] == 'Accounts') { ?>
                            <li class="nav-item">
                                <a class="nav-link menu-link" href="accounts">
                                    <i class="fa-solid fa-file-invoice"></i> <span data-key="t-accounts">Accounts</span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($_SESSION['Role'] == 'Admin' || $_SESSION['Role'] == 'Products') { ?>
                            <li class="nav-item">
                                <a class="nav-link menu-link" href="products">
                                    <i class="fa-solid fa-file-invoice"></i> <span data-key="t-products">Products</span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <!-- Sidebar -->
            </div>

            <div class="sidebar-background"></div>
        </div>
        <!-- Left Sidebar End -->
        <!-- Vertical Overlay-->
        <div class="vertical-overlay"></div>