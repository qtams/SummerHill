<?php
include 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$teacher_id = $_POST['teacher_id'] ?? '';
$lastname = ucfirst(mb_strtolower($_POST['lastname'] ?? ''));
$firstname = ucfirst(mb_strtolower($_POST['firstname'] ?? ''));
$email = ucfirst(mb_strtolower($_POST['email'] ?? ''));
$social = ucfirst(mb_strtolower($_POST['social'] ?? ''));
$mobile = ucfirst(mb_strtolower($_POST['mobile'] ?? ''));


if (empty($teacher_id) || empty($lastname) || empty($firstname)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

$upload_dir = '../images/profile/';
$allowed_types = ['jpg', 'jpeg', 'png'];
$profile_path = '';

// Get existing profile path
$stmt = $con->prepare("SELECT profile FROM teachers WHERE teacher_id = ?");
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$stmt->bind_result($existing_profile);
$stmt->fetch();
$stmt->close();

// Handle file upload if a new profile image was submitted
if (isset($_FILES['profile']) && $_FILES['profile']['error'] === 0) {
    $file_tmp = $_FILES['profile']['tmp_name'];
    $file_name = basename($_FILES['profile']['name']);
    $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (in_array($file_type, $allowed_types)) {
        // Delete old image
        if (!empty($existing_profile)) {
            $old_file_path = '../' . $existing_profile;
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }
        }

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
} else {
    // No new image: keep the old one
    $profile_path = $existing_profile;
}

// Update teacher data in the database
$stmt = $con->prepare("UPDATE teachers SET teacher_id = ?, lastname = ?, firstname = ?, email = ?, social = ?, mobile = ?, profile = ? WHERE teacher_id = ?");
$stmt->bind_param("ssssssss", $teacher_id, $lastname, $firstname, $email, $social, $mobile, $profile_path, $teacher_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Teacher updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update teacher']);
}

$stmt->close();
$con->close();
?>
