<?php
include 'connection.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['student_id']) || empty($_POST['student_id'])) {
        echo json_encode(["success" => false, "message" => "Student ID is required."]);
        exit;
    }

    $student_id = $_POST['student_id'];

    // Get profile path
    $stmt_select = $con->prepare("SELECT profile FROM students WHERE student_id = ?");
    $stmt_select->bind_param("s", $student_id);
    $stmt_select->execute();
    $stmt_select->bind_result($profile);
    $stmt_select->fetch();
    $stmt_select->close();

    if (!empty($profile)) {
        $profile_path = '../' . $profile;
        if (file_exists($profile_path)) {
            unlink($profile_path);
        }
    }

    // Delete related time_monitoring records
    $stmt_time = $con->prepare("DELETE FROM time_monitoring WHERE student_id = ?");
    $stmt_time->bind_param("s", $student_id);
    $stmt_time->execute();
    $stmt_time->close();

    // Delete student
    $stmt_student = $con->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt_student->bind_param("s", $student_id);

    if ($stmt_student->execute()) {
        echo json_encode(["success" => true, "message" => "Student and all related records deleted successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete student."]);
    }

    $stmt_student->close();
    $con->close();
}
?>
