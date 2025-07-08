<?php

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
// use PHPMailer\PHPMailer\Exception;

// require 'phpmailer/src/Exception.php';
// require 'phpmailer/src/PHPMailer.php';
// require 'phpmailer/src/SMTP.php';

//Create an instance; passing `true` enables exceptions
// $mail = new PHPMailer(true);

session_start();
include "config.php";
$_SESSION['success'] = '';
$_SESSION['error'] = '';

if (isset($_POST['Log_In'])) {
    // $recaptchaSecret = '6Lcn9SIqAAAAACendoQjglRI2QL6vWaCIblaEkvV';
    // $recaptchaResponse = $_POST['g-recaptcha-response'];

    // // Verify the reCAPTCHA response with Google
    // $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaResponse}");
    // $responseKeys = json_decode($response, true);

    // if ($responseKeys["success"]) {
    //     // reCAPTCHA was successfully validated
    //     // $_SESSION['success'] = "reCAPTCHA verified successfully.";
    // } else {
    //     // reCAPTCHA validation failed
    //     $_SESSION['error'] = 'reCAPTCHA verification failed. Please try again.';
    //     header('Location: login');
    //     exit;
    // }

    $Username = mysqli_real_escape_string($conn, $_POST['Username']);
    $Password = mysqli_real_escape_string($conn, $_POST['Password']);
    // $emil_code = base64_encode($email);

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE Username = ?");
    $stmt->bind_param("s", $Username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hash = $row['Password'];

        if ($hash === "kishor") {
            // if ($row['user_verify'] == 1) {
            $_SESSION['Username'] = $row['Username'];
            $_SESSION['UserID'] = $row['UserID'];
            $_SESSION['Role'] = $row['Role'];
            header("Location: dashboard");
            exit;
            // } else {
            //     $_SESSION['error'] = 'Sorry, your account is not verified. Please verify your account. Check the Email Inbox.';
            //     header("Location: signin");
            //     exit;
            // }
        } else {
            $_SESSION['error'] = 'Invalid Password';
            header("Location: login");
            exit;
        }
    } else {
        $_SESSION['error'] = 'Invalid Username';
        header("Location: login");
        exit;
    }
}
// login for admin this side
if (isset($_POST['admin_login'])) {

    $recaptchaSecret = '6Lcn9SIqAAAAACendoQjglRI2QL6vWaCIblaEkvV';
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    // Verify the reCAPTCHA response with Google
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaResponse}");
    $responseKeys = json_decode($response, true);

    if ($responseKeys["success"]) {
        // reCAPTCHA was successfully validated
        // $_SESSION['success'] = "reCAPTCHA verified successfully.";
    } else {
        // reCAPTCHA validation failed
        $_SESSION['error'] = 'reCAPTCHA verification failed. Please try again.';
        header('Location: admin-login');
        exit;
    }

    $Username = mysqli_real_escape_string($conn, $_POST['Username']);
    $Password = mysqli_real_escape_string($conn, $_POST['Password']);


    if ($Username == "admin123" && $Password == "admin123") {
        $_SESSION['Username'] = 'admin123';
        $_SESSION['Role'] = 'Admin';
        header("Location: dashboard");
        exit;
    } else {
        $_SESSION['error'] = 'Invalid Username or Password';
        header("Location: admin-login");
        exit;
    }
}
// Reset Password
if (isset($_POST['reset_pass'])) {
    // function generateNumericVerificationCode($length = 6) {
    //     $digits = '0123456789';
    //     $digitsLength = strlen($digits);
    //     $verificationCode = '';

    //     for ($i = 0; $i < $length; $i++) {
    //         $verificationCode .= $digits[rand(0, $digitsLength - 1)];
    //     }

    //     return $verificationCode;
    // }

    // $verificationCode = generateNumericVerificationCode();
    $host = $_SERVER['HTTP_HOST'];
    $uri = rtrim(dirname($_SERVER['REQUEST_URI']), '/\\');
    $webside = $host . $uri;
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $emil_code = base64_encode($email);

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $user_name = $row['user_name'];

        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ahmedshahid14aug@gmail.com';
            $mail->Password = 'huuz ssjb xlvp ffsf';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            //Recipients
            $mail->setFrom('ahmedshahid14aug@gmail.com', 'Library');
            $mail->addAddress($email, $user_name);
            $mail->addReplyTo('ahmedshahid14aug@gmail.com', 'Information');

            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Reset Password';
            $mail->Body = '
            <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <title>Email</title>
    <style>
        body, table, td, a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            font-family: Arial, sans-serif;
        }
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }
        table {
            border-collapse: collapse !important;
        }
        body {
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background-color: #f5f5f5;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            padding: 30px;
            text-align: center;
            background-color: #007bff;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
            color: #333333;
            font-size: 16px;
            line-height: 1.5;
        }
        .content h1 {
            font-size: 22px;
            margin-bottom: 20px;
        }
        .content p {
            margin: 0 0 10px;
        }
        .btn-primary {
            display: inline-block;
            padding: 12px 24px;
            color: #ffffff !important;
            background-color: #007bff;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .footer {
            padding: 20px;
            text-align: center;
            background-color: #f8f9fa;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding: 10px;">
                <div class="email-container">
                    <!-- Header Section -->
                    <div class="header">
                        <h1>Library</h1>
                    </div>
                    <!-- Content Section -->
                    <div class="content">
                        <h1>Welcome, ' . $user_name . '!</h1>
                        <p>Hello,</p>
                        <p>Click the button below to verify your email address and change your password.</p>
                        <a class="btn btn-primary" href="' . $webside . '/new_password?key=' . $emil_code . '">Change Password</a>
                    </div>
                    <!-- Footer Section -->
                    <div class="footer">
                        <p>&copy; 2024 Library. All rights reserved.</p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
        $_SESSION['success'] = 'Email Successfully Sent.';
        header("Location: reset_password");
        exit;
    } else {
        $_SESSION['error'] = 'Invalid Email';
        header("Location: reset_password");
        exit;
    }
}

// New password work here
if (isset($_POST['set_new_password'])) {
    $email_decode = mysqli_real_escape_string($conn, $_POST['email_decode']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $newpassword = mysqli_real_escape_string($conn, $_POST['newpassword']);
    $confirmpassword = mysqli_real_escape_string($conn, $_POST['confirmpassword']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        if (empty($newpassword && $confirmpassword)) {
            $_SESSION['error'] = 'All Password fields are required.';
            header('Location: new_password?key=' . $email_decode . '');
            exit;
        }
        if ($newpassword == $confirmpassword) {
            $password = password_hash($newpassword, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $password, $email);

            if ($stmt->execute()) {
                $_SESSION['success'] = 'Password Successfully Changed.';
                header('Location: signin');
                exit;
            } else {
                $_SESSION['error'] = 'Password Update Failed. Please Try Again';
                header('Location: new_password?key=' . $email_decode . '');
                exit;
            }
        } else {
            $_SESSION['error'] = 'Confirm Password does not match.';
            header('Location: new_password?key=' . $email_decode . '');
            exit;
        }
    } else {
        $_SESSION['error'] = 'No Record Fonud.';
        header('Location: new_password?key=' . $email_decode . '');
        exit;
    }
}
