<?php
session_start();
if (isset($_SESSION['Username'])) {
    header('Location: dashboard');
    exit;
}
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="light" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>
    <meta charset="utf-8" />
    <title>Production Management Sign In</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Layout config Js -->
    <script src="assets/js/layout.js"></script>
    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />

    <!-- Include reCAPTCHA API -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body class="auth-bg 100-vh">
    <div class="bg-overlay bg-light"></div>

    <div class="account-pages">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-11">
                    <div class="auth-full-page-content d-flex min-vh-100 py-sm-5 py-4">
                        <div class="w-100">
                            <div class="d-flex flex-column h-100 py-0">

                                <div class="text-center mb-5">
                                    <a href="index.html">
                                        <span class="logo-lg">
                                            <img src="assets/images/logo/library_logo.png" alt="" height="100">
                                        </span>
                                    </a>
                                </div>

                                <div class="card my-auto overflow-hidden">
                                    <div class="row g-0">
                                        <div class="col-lg-6">
                                            <div class="p-lg-5 p-4">
                                                <div class="text-center">
                                                    <h5 class="mb-0">Welcome Back!</h5>
                                                    <p class="text-muted mt-2">Sign in to continue to Production Managment System.</p>
                                                </div>
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
                                                <div class="mt-4">
                                                    <form action="login-process" class="auth-input" method="post">
                                                        <div class="mb-3">
                                                            <label for="Username" class="form-label">Username</label>
                                                            <input type="text" class="form-control" id="Username" name="Username" placeholder="Enter Username">
                                                        </div>

                                                        <div class="mb-2">
                                                            <label for="Password" class="form-label">Password</label>
                                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                                <input type="Password" name="Password" class="form-control pe-5 password-input" placeholder="Enter Password" id="password-input">
                                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="las la-eye align-middle fs-18"></i></button>
                                                            </div>
                                                        </div>

                                                        <div class="form-check form-check-primary fs-16 py-2">
                                                            <input class="form-check-input" type="checkbox" id="remember-check">
                                                            <div class="float-end">
                                                                <!-- <a href="reset_password" class="text-muted text-decoration-underline fs-14">Forgot your password?</a> -->
                                                            </div>
                                                            <label class="form-check-label fs-14" for="remember-check">
                                                                Remember me
                                                            </label>
                                                        </div>

                                                        <!-- reCAPTCHA -->
                                                        <!--<div class="g-recaptcha mb-3" data-sitekey="6LfSBTEqAAAAAACNH8-cYR8xH1FrT9X8W104us08"></div>-->
                                                        
                                                        <div class="mt-2">
                                                            <button class="btn btn-primary w-100" type="submit" name="Log_In">Log In</button>
                                                        </div>

                                                        <!--<div class="mt-4 text-center">-->
                                                        <!--    <p class="mb-0">Don't have an account? <a href="signup" class="fw-medium text-primary text-decoration-underline"> Signup now </a></p>-->
                                                        <!--</div>-->
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="d-flex h-100 bg-auth align-items-end">
                                                <div class="p-lg-5 p-4">
                                                    <div class="bg-overlay bg-primary"></div>
                                                    <div class="p-0 p-sm-4 px-xl-0 py-5">
                                                        <div id="reviewcarouselIndicators" class="carousel slide auth-carousel" data-bs-ride="carousel">
                                                            <div class="carousel-indicators carousel-indicators-rounded">
                                                                <button type="button" data-bs-target="#reviewcarouselIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                                                <button type="button" data-bs-target="#reviewcarouselIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                                                <button type="button" data-bs-target="#reviewcarouselIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                                                            </div>

                                                            <!-- end carouselIndicators -->
                                                            <div class="carousel-inner mx-auto">
                                                                <div class="carousel-item active">
                                                                    <div class="testi-contain text-center">
                                                                        <h5 class="fs-20 text-white mb-0">“I feel confident imposing on myself”</h5>
                                                                        <p class="fs-15 text-white-50 mt-2 mb-0">Vestibulum auctor orci in risus iaculis consequat suscipit felis rutrum aliquet iaculis augue sed tempus In elementum ullamcorper lectus vitae pretium Nullam ultricies diam eu ultrices sagittis.</p>
                                                                    </div>
                                                                </div>

                                                                <div class="carousel-item">
                                                                    <div class="testi-contain text-center">
                                                                        <h5 class="fs-20 text-white mb-0">“Our task must be to free widening circle”</h5>
                                                                        <p class="fs-15 text-white-50 mt-2 mb-0">Curabitur eget nulla eget augue dignissim condintum Nunc imperdiet ligula porttitor commodo elementum Vivamus justo risus fringilla suscipit faucibus orci luctus ultrices posuere cubilia curae ultricies cursus.</p>
                                                                    </div>
                                                                </div>

                                                                <div class="carousel-item">
                                                                    <div class="testi-contain text-center">
                                                                        <h5 class="fs-20 text-white mb-0">“I've learned that people forget what you”</h5>
                                                                        <p class="fs-15 text-white-50 mt-2 mb-0">Pellentesque lacinia scelerisque arcu in aliquam augue molestie rutrum Fusce dignissim dolor id auctor accumsan vehicula dolor vivamus feugiat odio erat sed quis Donec nec scelerisque magna</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- end carousel-inner -->
                                                        </div>
                                                        <!-- end review carousel -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <!-- end card -->

                                <div class="mt-5 text-center">
                                    <p class="mb-0 text-muted">©
                                        <script>
                                            document.write(new Date().getFullYear())
                                        </script> Library. Crafted with <i class="bx bx-heart text-danger"></i> by Fronxs Solution
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end col -->
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('#primary-alert').fadeOut('slow');
            }, 2000);
        });
    </script>

    <!-- JAVASCRIPT -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/node-waves/waves.min.js"></script>
    <script src="assets/libs/feather-icons/feather.min.js"></script>
    <script src="assets/js/plugins.js"></script>

    <!-- password-addon init -->
    <script src="assets/js/pages/password-addon.init.js"></script>
</body>

</html>
