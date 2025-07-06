<?php
include 'connection.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['teacher_id']) || empty($_POST['teacher_id'])) {
        echo json_encode(["status" => "error", "message" => "Teacher ID is required."]);
        exit;
    }

    $teacher_ids = json_decode($_POST['teacher_id']);

    if (!is_array($teacher_ids) || empty($teacher_ids)) {
        echo json_encode(["status" => "error", "message" => "Invalid teacher IDs provided."]);
        exit;
    }

    $successCount = 0;
    $errors = [];

    foreach ($teacher_ids as $teacher_id) {
        // 1. Fetch the profile path for the teacher
        $stmt_select = $con->prepare("SELECT profile FROM teachers WHERE teacher_id = ?");
        $stmt_select->bind_param("s", $teacher_id);
        $stmt_select->execute();
        $stmt_select->bind_result($profile);
        $stmt_select->fetch();
        $stmt_select->close();

        // 2. Delete the profile file if it exists
        if (!empty($profile)) {
            $profile_path = '../' . $profile;
            if (file_exists($profile_path)) {
                unlink($profile_path);
            }
        }

        // 3. Delete related time_monitoring entries
        $stmt_delete_time = $con->prepare("DELETE FROM time_monitoring WHERE teacher_id = ?");
        $stmt_delete_time->bind_param("s", $teacher_id);
        $stmt_delete_time->execute();
        $stmt_delete_time->close();

        // 4. Delete the teacher record from the database
        $stmt = $con->prepare("DELETE FROM teachers WHERE teacher_id = ?");
        $stmt->bind_param("s", $teacher_id);

        if ($stmt->execute()) {
            $successCount++;
        } else {
            $errors[] = "Failed to delete teacher ID: $teacher_id";
        }

        $stmt->close();
    }

    if ($successCount > 0 && empty($errors)) {
        echo json_encode(["status" => "success", "message" => "Successfully deleted $successCount teacher(s) and related records."]);
    } else if (!empty($errors)) {
        echo json_encode(["status" => "partial", "message" => implode("\n", $errors), "successCount" => $successCount]);
    } else {
        echo json_encode(["status" => "error", "message" => "No teachers were deleted."]);
    }

    $con->close();
}
?>
