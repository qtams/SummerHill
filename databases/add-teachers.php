<?php
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    include "connection.php";
    date_default_timezone_set('Asia/Manila');

    $lastname = ucfirst(mb_strtolower($_POST['lastname'] ?? ''));
    $firstname = ucfirst(mb_strtolower($_POST['firstname'] ?? ''));
    $year_level = ucfirst(mb_strtolower($_POST['year_level'] ?? ''));
    $teacher_id = ucfirst(mb_strtolower($_POST['teacher_id'] ?? ''));
    $card_id = ucfirst(mb_strtolower($_POST['card_id'] ?? ''));
    $email = ucfirst(mb_strtolower($_POST['email'] ?? ''));
    $social = ucfirst(mb_strtolower($_POST['social'] ?? ''));
    $mobile = ucfirst(mb_strtolower($_POST['mobile'] ?? ''));

    $mobile = ucfirst(mb_strtolower($_POST['mobile'] ?? ''));
    $created_at = date('Y-m-d');

    $upload_dir = '../images/profile/';
    $allowed_types = ['jpg', 'jpeg', 'png'];
    $profile_path = '';

    if (isset($_FILES['profile']) && $_FILES['profile']['error'] === 0) {
        $file_tmp = $_FILES['profile']['tmp_name'];
        $file_name = basename($_FILES['profile']['name']);
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_type, $allowed_types)) {
            $new_file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
            $destination = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                $profile_path = 'images/profile/' . $new_file_name;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to upload profile picture']);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, JPEG, and PNG allowed.']);
            exit;
        }
    }

    //Insertion of Data into teachers table

    $sql = "INSERT INTO teachers (card_id, teacher_id, lastname, firstname, year_level,  updtime, profile, email, social, mobile) VALUES (?,?,?,?,?,?,?,?,?,?)";

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt,"ssssssssss", $card_id, $teacher_id, $lastname, $firstname, $year_level, $created_at, $profile_path, $email, $social, $mobile);


    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Student added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($con)]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($con);
}
