<?php
session_start();
$_SESSION['success'] = '';
$_SESSION['error'] = '';
include "config.php";
// include "library_detact.php";
if (isset($_POST['Add_Artisan'])) {                 
    $ArtisanName = mysqli_real_escape_string($conn, $_POST['ArtisanName']);
    $Specialization = mysqli_real_escape_string($conn, $_POST['Specialization']);
    $JoinDate = mysqli_real_escape_string($conn, $_POST['JoinDate']);
    $DepartmentID = mysqli_real_escape_string($conn, $_POST['DepartmentID']);
    // $sql = "SELECT * FROM departments WHERE DepartmentName = '{$DepartmentName}'";
    // $result = mysqli_query($conn, $sql);
    // if (mysqli_num_rows($result) > 0) {
    //     $_SESSION['error'] = 'Department Already Exists.';
    //     header("Location: departments");
    //     exit;
    // }


    if (empty($ArtisanName) || empty($Specialization) || empty($JoinDate) || empty($DepartmentID) ) {
        $_SESSION['error'] = 'All Failed Requird.';
        header("Location: artisans");
        exit;
    }

    // user registretion 
    $sql = "INSERT INTO artisans (ArtisanName,Specialization,JoinDate,DepartmentID)
        VALUE('{$ArtisanName}','{$Specialization}','{$JoinDate}',{$DepartmentID})";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = 'Artisans Successfully Register.';
        header("Location: artisans");
        exit;
    } else {
        $_SESSION['error'] = 'Sorry Registretion Faild.';
        header("Location: artisans");
        exit;
    }
}

// user edit section
if (isset($_POST['Edit_Artisan'])) {
    
    $ArtisanID = mysqli_real_escape_string($conn, $_POST['ArtisanID']);
    $ArtisanName = mysqli_real_escape_string($conn, $_POST['ArtisanName']);
    $Specialization = mysqli_real_escape_string($conn, $_POST['Specialization']);
    $JoinDate = mysqli_real_escape_string($conn, $_POST['JoinDate']);
    $DepartmentID = mysqli_real_escape_string($conn, $_POST['DepartmentID']);
   

    if (empty($ArtisanName) || empty($Specialization) || empty($JoinDate) || empty($DepartmentID) ) {
        $_SESSION['error'] = 'All Failed Requird.';
        header("Location: artisans");
        exit;
    }
    $sql = "UPDATE artisans SET ArtisanName = '{$ArtisanName}',Specialization = '{$Specialization}',JoinDate = '{$JoinDate}',DepartmentID = {$DepartmentID} WHERE ArtisanID= {$ArtisanID};";

    // user registretion 

    if (mysqli_query($conn, $sql)) {

        $_SESSION['success'] = 'Department Successfully Updated.';
        header("Location: artisans");
        exit;
    } else {
        $_SESSION['error'] = 'Sorry Update Faild.';
        header("Location: artisans");
        exit;
    }
}
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    $sql = "UPDATE artisans SET is_delete = 1 WHERE ArtisanID = $id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = 'Artisans Successfully Deleted.';
        header("Location: artisans");
        exit;
    } else {
        $_SESSION['error'] = 'Sorry Delete Faild.';
        header("Location: artisans");
        exit;
    }
}
