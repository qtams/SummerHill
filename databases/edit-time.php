<?php
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    include "connection.php";
    date_default_timezone_set('Asia/Manila');

    $time_in = $_POST['time_in'] ?? '';
    $time_out = $_POST['time_out'] ?? '';
    $time_id = intval($_POST['time_id']);

    // Basic validation
    if (empty($time_in) || empty($time_out)) {
        echo json_encode(['status' => 'error', 'message' => 'Time In and Time Out are required.']);
        exit;
    }

    $in = new DateTime($time_in);
    $out = new DateTime($time_out);

    if ($out < $in) {
        echo json_encode(['status' => 'error', 'message' => 'Time Out cannot be earlier than Time In.']);
        exit;
    }

    $sql = "UPDATE time_monitoring 
            SET time_in = ?, time_out = ? 
            WHERE time_id = ?";

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $time_in, $time_out, $time_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Time has been updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($con)]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($con);
}
?>
