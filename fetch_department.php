<?php
include 'config.php'; // Include your database connection file

if(isset($_POST['ArtisanID'])) {
    $artisanID = $_POST['ArtisanID'];

    // Fetch Department ID based on Artisan ID
    $sql = "SELECT DepartmentID FROM artisans WHERE ArtisanID = '$artisanID' AND is_delete = 0";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $departmentID = $row['DepartmentID'];

    // Fetch Department Name based on Department ID
    $sql_department = "SELECT * FROM departments WHERE DepartmentID = '$departmentID' AND is_delete = 0";
    $result_department = mysqli_query($conn, $sql_department);

    if(mysqli_num_rows($result_department) > 0) {
        while($row_department = mysqli_fetch_assoc($result_department)) {
            echo '<option value="' . $row_department['DepartmentID'] . '" selected>' . $row_department['DepartmentName'] . '</option>';
        }
    } else {
        echo '<option value="">No Department found</option>';
    }
}
