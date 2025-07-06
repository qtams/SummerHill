<?php
include 'connection.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['time_id']) || empty($_POST['time_id'])) {
        echo json_encode(["success" => false, "message" => "Time ID is required."]);
        exit;
    }

    $time_id = $_POST['time_id'];

    $stmt = $con->prepare("DELETE FROM time_monitoring WHERE time_id = ?");
    $stmt->bind_param("i", $time_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Time deleted successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete time record."]);
    }

    $stmt->close();
    $con->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
