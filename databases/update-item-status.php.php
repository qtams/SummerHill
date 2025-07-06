<?php
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $studentNumber = $_POST['student_number'] ?? null;
    $studentName = $_POST['student_name'] ?? null;

    // Escape inputs
    $id = mysqli_real_escape_string($con, $id);
    $status = mysqli_real_escape_string($con, $status);
    $studentNumber = mysqli_real_escape_string($con, $studentNumber);
    $studentName = mysqli_real_escape_string($con, $studentName);

    if ($status === 'claimed') {
        $query = "UPDATE lost_items SET 
                 status = '$status',
                 date_claimed = CURDATE(),
                 claimed_by_student_number = '$studentNumber',
                 claimed_by_name = '$studentName'
                 WHERE id = '$id'";
    } else {
        $query = "UPDATE lost_items SET 
                 status = '$status',
                 date_claimed = NULL,
                 claimed_by_student_number = NULL,
                 claimed_by_name = NULL
                 WHERE id = '$id'";
    }
    
    if (mysqli_query($con, $query)) {
        header("Location:../dashboard.php");
        exit;
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}
?>