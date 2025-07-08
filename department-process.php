<?php
session_start();
$_SESSION['success'] = '';
$_SESSION['error'] = '';
include "config.php";
// include "library_detact.php";
if (isset($_POST['Add_Department'])) {
    $DepartmentName = mysqli_real_escape_string($conn, $_POST['DepartmentName']);
    $sql = "SELECT * FROM departments WHERE DepartmentName = '{$DepartmentName}'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = 'Department Already Exists.';
        header("Location: departments");
        exit;
    }


    if (empty($DepartmentName)) {
        $_SESSION['error'] = 'Department Name Requird ';
        header("Location: departments");
        exit;
    }

    // user registretion 
    $sql = "INSERT INTO departments (DepartmentName)
        VALUE('{$DepartmentName}')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = 'Department Successfully Register.';
        header("Location: departments");
        exit;
    } else {
        $_SESSION['error'] = 'Sorry Registretion Faild.';
        header("Location: departments");
        exit;
    }
}

// user edit section
if (isset($_POST['Edit_Department'])) {

    $DepartmentID = mysqli_real_escape_string($conn, $_POST['DepartmentID']);
    $DepartmentName = mysqli_real_escape_string($conn, $_POST['DepartmentName']);
    $sql = "SELECT * FROM departments WHERE DepartmentName = '{$DepartmentName}' AND DepartmentID !='{$DepartmentID}'";
    $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $_SESSION['error'] = 'Department Already Exists.';
            header("Location: departments");
            exit;
        }
    $row = mysqli_fetch_assoc($result);


    if (empty($DepartmentName)) {
        $_SESSION['error'] = 'Department Name Requird ';
        header("Location: departments");
        exit;
    }
    $sql = "UPDATE departments SET DepartmentName = '{$DepartmentName}' WHERE DepartmentID= {$DepartmentID};";

    // user registretion 

    if (mysqli_query($conn, $sql)) {

        $_SESSION['success'] = 'Department Successfully Updated.';
        header("Location: departments");
        exit;
    } else {
        $_SESSION['error'] = 'Sorry Update Faild.';
        header("Location: departments");
        exit;
    }
}
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    $sql = "UPDATE departments SET is_delete = 1 WHERE DepartmentID = $id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = 'Department Successfully Deleted.';
        header("Location: departments");
        exit;
    } else {
        $_SESSION['error'] = 'Sorry Delete Faild.';
        header("Location: departments");
        exit;
    }
}
