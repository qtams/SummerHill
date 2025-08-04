<?php
include 'connection.php';

header('Content-Type: application/json'); // Important for fetch()

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = mysqli_real_escape_string($con, $_POST['student_id']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $social = mysqli_real_escape_string($con, $_POST['social']);

    if (empty($student_id) || empty($email) || empty($social)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
        exit();
    }

    $sql = "UPDATE students 
            SET email = '$email',
                social = '$social'
            WHERE student_id = '$student_id'";

    if (mysqli_query($con, $sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Parent form saved! Email + PSID updated.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($con)]);
    }

    mysqli_close($con);
}
?>
