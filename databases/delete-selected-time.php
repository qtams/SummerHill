<?php
include 'connection.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['time_id']) || empty($_POST['time_id'])) {
        echo json_encode(["status" => "error", "message" => "Time IDs are required."]);
        exit;
    }

    $time_ids = json_decode($_POST['time_id'], true);

    if (!is_array($time_ids) || empty($time_ids)) {
        echo json_encode(["status" => "error", "message" => "Invalid Time IDs provided."]);
        exit;
    }

    $successCount = 0;
    $errors = [];

    $stmt = $con->prepare("DELETE FROM time_monitoring WHERE time_id = ?");

    foreach ($time_ids as $id) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $successCount++;
        } else {
            $errors[] = "Failed to delete time ID: $id";
        }
    }

    $stmt->close();
    $con->close();

    if ($successCount > 0 && empty($errors)) {
        echo json_encode(["status" => "success", "message" => "Successfully deleted $successCount record(s)."]);
    } elseif (!empty($errors)) {
        echo json_encode([
            "status" => "partial",
            "message" => implode("\n", $errors),
            "successCount" => $successCount
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "No records were deleted."]);
    }
}
?>
