<?php
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    include "connection.php";
    date_default_timezone_set('Asia/Manila');

    $time_in = $_POST['time_in'] ?? '';
    $time_out = $_POST['time_out'] ?? '';
    $trainee_id = $_POST['trainee_id'];

    // Convert to DateTime objects
    $in = new DateTime($time_in);
    $out = new DateTime($time_out);

    // Calculate the interval in seconds
    $interval = $in->diff($out);
    $hours = $interval->h + ($interval->i / 60) + ($interval->s / 3600);

    // Add days if interval spans multiple days
    if ($interval->d > 0) {
        $hours += $interval->d * 24;
    }

    // Format to 2 decimal places
    $total_hours = number_format($hours, 2);

    $sql = "INSERT INTO time_monitoring (trainee_id, time_in, time_out, hours) 
            VALUES (?, ?, ?, ?)";

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $trainee_id, $time_in, $time_out, $total_hours);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Time has been added successfully', 'hours' => $total_hours]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($con)]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($con);
}
?>
