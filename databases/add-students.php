<?php
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    include "connection.php";
    date_default_timezone_set('Asia/Manila');

    // Normalize input
    $lastname = ucfirst(mb_strtolower($_POST['lastname'] ?? ''));
    $firstname = ucfirst(mb_strtolower($_POST['firstname'] ?? ''));
    $student_id = $_POST['student_id'] ?? '';
    $card_id = $_POST['card_id'] ?? '';
    $email = strtolower(trim($_POST['email'] ?? ''));
    $social = trim($_POST['social'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $created_at = date('Y-m-d');

    // Handle section
    $section = $_POST['section'] ?? '';
    $other_section = $_POST['other_section'] ?? '';
    $final_section = ($section === 'OTHER') ? $other_section : $section;

    // Handle year level
    $year_level_raw = $_POST['year_level'] ?? '';
    $other_year_level = $_POST['other_year_level'] ?? '';
    $year_level = ($year_level_raw === 'OTHER')
        ? (int) filter_var($other_year_level, FILTER_SANITIZE_NUMBER_INT)
        : (int) $year_level_raw;

    // Profile picture logic (no default)
    $upload_dir = '../images/profile/';
    $allowed_types = ['jpg', 'jpeg', 'png'];
    $profile_path = ''; // Empty by default

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

    // Insert student into database
    $sql = "INSERT INTO students 
            (card_id, student_id, lastname, firstname, year_level, section, updtime, profile, email, social, mobile) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ssssissssss", 
        $card_id, $student_id, $lastname, $firstname, 
        $year_level, $final_section, $created_at, 
        $profile_path, $email, $social, $mobile
    );

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Student added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($con)]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($con);
}
?>
