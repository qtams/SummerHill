<?php
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    include "connection.php";
    date_default_timezone_set('Asia/Manila');

    // Normalize input
    $student_id = $_POST['student_id'] ?? '';
    $lastname = ucfirst(mb_strtolower($_POST['lastname'] ?? ''));
    $firstname = ucfirst(mb_strtolower($_POST['firstname'] ?? ''));
    $card_id = $_POST['card_id'] ?? '';
    $email = strtolower(trim($_POST['email'] ?? ''));
    $social = trim($_POST['social'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $updated_at = date('Y-m-d');

    $section = $_POST['section'] ?? '';
    $year_level = (int) ($_POST['year_level'] ?? 0);

    $upload_dir = '../images/profile/';
    $allowed_types = ['jpg', 'jpeg', 'png'];
    $profile_path = '';

    // 1. Get existing profile from database
    $oldProfile = '';
    $fetch_sql = "SELECT profile FROM students WHERE student_id = ?";
    $fetch_stmt = mysqli_prepare($con, $fetch_sql);
    mysqli_stmt_bind_param($fetch_stmt, "s", $student_id);
    mysqli_stmt_execute($fetch_stmt);
    mysqli_stmt_bind_result($fetch_stmt, $oldProfile);
    mysqli_stmt_fetch($fetch_stmt);
    mysqli_stmt_close($fetch_stmt);

    // 2. Upload new profile and delete old one if needed
    if (isset($_FILES['profile']) && $_FILES['profile']['error'] === 0) {
        $file_tmp = $_FILES['profile']['tmp_name'];
        $file_name = basename($_FILES['profile']['name']);
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_type, $allowed_types)) {
            $new_file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
            $destination = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                $profile_path = 'images/profile/' . $new_file_name;

                // 3. Delete old profile if not default and exists
                $old_file_path = '../' . $oldProfile;
                if ($oldProfile && $oldProfile !== 'images/profile/noImage.jpg' && file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to upload new profile picture.']);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, JPEG, and PNG allowed.']);
            exit;
        }
    }

    // 4. Update query with or without profile
    if ($profile_path !== '') {
        $sql = "UPDATE students SET
                    card_id = ?, lastname = ?, firstname = ?, year_level = ?,
                    section = ?, profile = ?, email = ?, social = ?, mobile = ?, updtime = ?
                WHERE student_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "sssisssssss",
            $card_id, $lastname, $firstname, $year_level,
            $section, $profile_path, $email, $social, $mobile, $updated_at, $student_id
        );
    } else {
        $sql = "UPDATE students SET
                    card_id = ?, lastname = ?, firstname = ?, year_level = ?,
                    section = ?, email = ?, social = ?, mobile = ?, updtime = ?
                WHERE student_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "sssissssss",
            $card_id, $lastname, $firstname, $year_level,
            $section, $email, $social, $mobile, $updated_at, $student_id
        );
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Student updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($con)]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($con);
}
?>
