<?php
include 'connection.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['card_id']) || empty($_POST['card_id'])) {
        echo json_encode(["success" => false, "message" => "Teacher ID is required."]);
        exit;
    }

    $card_id = $_POST['card_id'];

    // Get profile path
    $stmt_select = $con->prepare("SELECT profile FROM teachers WHERE card_id = ?");
    $stmt_select->bind_param("s", $card_id);
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

    
    $stmt_time = $con->prepare("DELETE FROM time_monitoring WHERE teacher_id = ?");
    $stmt_time->bind_param("s", $card_id);
    $stmt_time->execute();
    $stmt_time->close();
    

    // Delete teacher
    $stmt_teacher = $con->prepare("DELETE FROM teachers WHERE card_id = ?");
    $stmt_teacher->bind_param("s", $card_id);

    if ($stmt_teacher->execute()) {
        echo json_encode(["success" => true, "message" => "Teacher and all related records deleted successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete teacher."]);
    }

    $stmt_teacher->close();
    $con->close();
}
?>
