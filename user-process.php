<?php
session_start();
$_SESSION['success'] = '';
$_SESSION['error'] = '';
include "config.php";
// include "library_detact.php";
if (isset($_POST['Add_user'])) {
    $Username = mysqli_real_escape_string($conn, $_POST['Username']);
    $sql = "SELECT * FROM users WHERE Username = '{$Username}'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = 'User Already Exists.';
        header("Location: users");
        exit;
    }

    $Password = password_hash($_POST['Password'], PASSWORD_BCRYPT);
    $Role = mysqli_real_escape_string($conn, $_POST['Role']);
    if (empty($Username) || empty($Password) || empty($Role)) {
        $_SESSION['error'] = 'All Field Requird ';
        header("Location: users");
        exit;
    }

    // user registretion 
    $sql = "INSERT INTO users (Username,Password,Role)
        VALUES('{$Username}','{$Password}','{$Role}')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = 'User Successfully Register.';
        header("Location: users");
        exit;
    } else {
        $_SESSION['error'] = 'Sorry Registretion Faild.';
        header("Location: users");
        exit;
    }
}

// user edit section
if (isset($_POST['Edit_User'])) {

    $UserID = mysqli_real_escape_string($conn, $_POST['UserID']);
    $Username = mysqli_real_escape_string($conn, $_POST['Username']);
    $sql = "SELECT * FROM users WHERE Username = '{$Username}' AND UserID !='$UserID'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);


        if (mysqli_num_rows($result) > 0) {
            $_SESSION['error'] = 'User Already Exists.';
            header("Location: users");
            exit;
        }
    


    $Password = password_hash($_POST['Password'], PASSWORD_BCRYPT);
    $Role = mysqli_real_escape_string($conn, $_POST['Role']);

    if (empty($Username) || empty($Role)) {
        $_SESSION['error'] = 'All Field Requird ';
        header("Location: users");
        exit;
    }

    if (empty($password)) {
        $sql = "UPDATE users SET Username = '{$Username}',Role = '{$Role}' WHERE UserID= {$UserID}";
    } else {
        $sql = "UPDATE users SET Username = '{$Username}', Password = '{$Password}', Role = '{$Role}' WHERE UserID= {$UserID};";
    }
    // user registretion 

    if (mysqli_query($conn, $sql)) {

        $_SESSION['success'] = 'User Successfully Updated.';
        header("Location: users");
        exit;
    } else {
        $_SESSION['error'] = 'Sorry Update Faild.';
        header("Location: users");
        exit;
    }
}
if(isset($_GET['id'])){
    $id = mysqli_real_escape_string($conn,$_GET['id']);
    $sql = "UPDATE users SET is_delete = 1 WHERE UserID = $id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = 'User Successfully Deleted.';
        header("Location: users");
        exit;
    } else {
        $_SESSION['error'] = 'Sorry Delete Faild.';
        header("Location: users");
        exit;
    }
}
