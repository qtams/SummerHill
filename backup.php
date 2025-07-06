if (isset($_GET['get_data']) && $_GET['get_data'] === 'trainee') {
    $trainee = [];

    $sql = "SELECT t.*, IFNULL(SUM(tm.hours), 0) AS total_hours 
            FROM trainee t
            LEFT JOIN time_monitoring tm ON t.trainee_id = tm.trainee_id
            GROUP BY t.trainee_id
            ORDER BY FIELD(t.status, 'On Going', 'Complete', 'Passed', 'Failed')";

    $result = mysqli_query($con, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row['remaining_hours'] = max(0, $row['set_hours'] - $row['total_hours']);

            // ✅ Only auto-update if status is still "On Going" AND hours met
            if ($row['status'] === 'On Going' && $row['total_hours'] >= $row['set_hours']) {
                $trainee_id = $row['trainee_id'];
                $updateSql = "UPDATE trainee SET status = 'Complete', is_hidden = 1 WHERE trainee_id = '$trainee_id'";
                mysqli_query($con, $updateSql);

                // Update status and hidden locally for response
                $row['status'] = 'Complete';
                $row['is_hidden'] = '1';
            }

            $trainee[] = $row;
        }
    }

    header("Content-Type: application/json");
    echo json_encode($trainee);
    exit();
}