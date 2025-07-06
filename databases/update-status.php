<?php
include 'connection.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents("php://input"));

    if (!$data) {
        throw new Exception('Invalid input data');
    }

    $trainee_id = $data->trainee_id ?? null;
    $status = $data->status ?? null;

    if (!$trainee_id || !$status) {
        throw new Exception('Missing required fields');
    }

    // Allowed statuses
    $allowedStatuses = ['Complete', 'Passed', 'Failed', 'On Going'];
    if (!in_array($status, $allowedStatuses)) {
        throw new Exception('Invalid status value');
    }

    // Determine is_hidden value based on status
    $is_hidden = in_array($status, ['Complete', 'Passed', 'Failed']) ? 1 : 0;

    // Update both status and is_hidden
    $stmt = $con->prepare("UPDATE trainee SET status = ?, is_hidden = ? WHERE trainee_id = ?");
    $stmt->bind_param("sis", $status, $is_hidden, $trainee_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Status and visibility updated']);
    } else {
        throw new Exception('Database update failed');
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
