<?php
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    date_default_timezone_set('Asia/Manila');
    include 'databases/connection.php';

    if (isset($_GET['get_data']) && $_GET['get_data'] === 'student') {
        $students = [];

        // Add custom ordering using FIELD()
        $sql = "SELECT * FROM students 
                ORDER BY FIELD(status,'ABSENT', 'PRESENT')";

        $result = mysqli_query($con, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $students[] = $row;
            }
        }

        header("Content-Type: application/json");
        echo json_encode($students);
        exit();
    }
     // Return counts
    if (isset($_GET['get_data']) && $_GET['get_data'] === 'CountAll') {
        $response = [];

        // Trainee count (excluding hidden)
        $traineeSql = "SELECT COUNT(*) AS total_trainees FROM trainee WHERE is_hidden = 0";
        $traineeResult = mysqli_query($con, $traineeSql);
        $response['TraineeCount'] = ($traineeResult && $row = mysqli_fetch_assoc($traineeResult)) ? $row['total_trainees'] : 0;

        // Equipment count (no is_hidden used)
        $equipSql = "SELECT COUNT(*) AS total_equipments FROM equipments";
        $equipResult = mysqli_query($con, $equipSql);
        $response['EquipmentsCount'] = ($equipResult && $row = mysqli_fetch_assoc($equipResult)) ? $row['total_equipments'] : 0;

        // Lost items count (no is_hidden used)
        $lostSql = "SELECT COUNT(*) AS total_lost_items FROM lost_items";
        $lostResult = mysqli_query($con, $lostSql);
        $response['ItemsCount'] = ($lostResult && $row = mysqli_fetch_assoc($lostResult)) ? $row['total_lost_items'] : 0;

        header("Content-Type: application/json");
        echo json_encode($response);
        exit();
    }


    if (isset($_GET['get_data']) && $_GET['get_data'] === 'trainee_id') {
        $traineeList = [];

        $sql = "SELECT t.firstname, t.lastname, t.profile, t.trainee_id, 
                       t.set_hours, t.status, t.is_hidden,
                       IFNULL(SUM(tm.hours), 0) as total_hours 
                FROM trainee t
                
                LEFT JOIN time_monitoring tm ON t.trainee_id = tm.trainee_id
                GROUP BY t.trainee_id, t.set_hours, t.status, t.firstname, t.lastname, t.profile, t.is_hidden
                ORDER BY FIELD(t.status, 'On Going', 'Complete', 'Passed', 'Failed')";

        $result = mysqli_query($con, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $row['remaining_hours'] = max(0, $row['set_hours'] - $row['total_hours']);
                $traineeList[] = $row;
            }
        }

        header("Content-Type: application/json");
        echo json_encode($traineeList);
        exit();
    }
    if (isset($_GET['get_data']) && $_GET['get_data'] === 'trainee_selection') {
        $traineeList = [];
    
        $sql = "SELECT t.firstname, t.lastname, t.profile, t.trainee_id, 
                       t.set_hours, t.status, t.is_hidden,
                       IFNULL(SUM(tm.hours), 0) as total_hours 
                FROM trainee t
                LEFT JOIN time_monitoring tm ON t.trainee_id = tm.trainee_id
                WHERE t.is_hidden = 0
                GROUP BY t.trainee_id, t.set_hours, t.status, t.firstname, t.lastname, t.profile, t.is_hidden
                ORDER BY FIELD(t.status, 'On Going', 'Complete', 'Passed', 'Failed')";
    
        $result = mysqli_query($con, $sql);
    
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $row['remaining_hours'] = max(0, $row['set_hours'] - $row['total_hours']);
                $traineeList[] = $row;
            }
        }
    
        header("Content-Type: application/json");
        echo json_encode($traineeList);
        exit();
    }

    if (isset($_GET['get_data']) && $_GET['get_data'] === 'trainee_details') {
        date_default_timezone_set('Asia/Manila');
        $card_id = mysqli_real_escape_string($con, $_GET['card_id'] ?? '');
        $response = [];

        // Removed is_hidden and status
        $sql = "SELECT year_level, student_id, lastname, firstname, profile 
                FROM students 
                WHERE card_id = '$card_id'";
        $result = mysqli_query($con, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $response = mysqli_fetch_assoc($result);

            // Time monitoring logic (still kept, just removed hours part)
            $checkSql = "SELECT * FROM time_monitoring 
                        WHERE card_id = '$card_id' 
                        AND DATE(time_in) = CURDATE() 
                        AND time_out = '0000-00-00 00:00:00' 
                        ORDER BY time_id DESC LIMIT 1";
            $checkResult = mysqli_query($con, $checkSql);

            if ($checkResult && mysqli_num_rows($checkResult) > 0) {
                // Clock-out
                $row = mysqli_fetch_assoc($checkResult);
                $time_id = $row['time_id'];
                $time_out = date('Y-m-d H:i:s');

                // Remove hours logic if not needed
                $updateSql = "UPDATE time_monitoring 
                            SET time_out = '$time_out' 
                            WHERE time_id = '$time_id'";
                if (!mysqli_query($con, $updateSql)) {
                    $response['time_monitoring_error'] = 'Failed to record time-out: ' . mysqli_error($con);
                } else {
                    $response['success'] = 'Time-out recorded successfully';
                }
            } else {
                // Clock-in
                $currentDateTime = date('Y-m-d H:i:s');

                // You need to get trainee_id, since it's not selected above anymore
                $card_id_sql = "SELECT card_id FROM students WHERE card_id = '$card_id'";
                $students_result = mysqli_query($con, $card_id_sql);
                if ($students_result && mysqli_num_rows($students_result) > 0) {
                    $students_row = mysqli_fetch_assoc($students_result);
                    $card_id = $students_row['card_id'];

                    $insertSql = "INSERT INTO time_monitoring (card_id, time_in) 
                                VALUES ('$card_id', '$currentDateTime')";
                    if (!mysqli_query($con, $insertSql)) {
                        $response['time_monitoring_error'] = 'Failed to record time-in: ' . mysqli_error($con);
                    } else {
                        $response['success'] = 'Time-in recorded successfully';
                    }
                } else {
                    $response['error'] = 'Trainee ID not found for card ID.';
                }
            }
        } else {
            $response = ['error' => 'Trainee not found or inactive'];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    if (isset($_GET['get_data'], $_GET['user_id']) && $_GET['get_data'] === 'Information') {
        // Remove this line as it's redundant (connection is already included at the top)
        // require_once 'db_connection.php';
    
        $user_id = mysqli_real_escape_string($con, $_GET['user_id']);
    
        $query = "SELECT user_id, username, password, role FROM admin_user WHERE user_id = '$user_id'";
        $result = mysqli_query($con, $query);
    
        $userInfo = [];
    
        if ($result && mysqli_num_rows($result) > 0) {
            $userInfo = mysqli_fetch_assoc($result);
        } else {
            // Return empty values if no user found
            $userInfo = [
                'password' => 'No password found',
                'role' => 'Unknown',
                'username' => ''
            ];
        }
    
        header("Content-Type: application/json");
        echo json_encode($userInfo);
        exit();
    }

    if (isset($_GET['get_data']) && $_GET['get_data'] === 'time') {
        $time = [];
    
    
       $sql = "SELECT 
            t.*, 
            s.student_id, 
            s.lastname, 
            s.firstname, 
            s.year_level, 
            s.is_hidden 
        FROM time_monitoring t
        JOIN students s ON t.card_id = s.card_id
        ORDER BY s.is_hidden ASC";

        $result = mysqli_query($con, $sql);
    
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $time[] = $row;
            }
        }
    
        header("Content-Type: application/json");
        echo json_encode($time);
        exit();
    }
    

   if (isset($_GET['get_data']) && $_GET['get_data'] === 'teacher') {
    $teacherList = [];

    $sql = "SELECT * FROM teachers t";
    $result = mysqli_query($con, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $teacherList[] = $row; // ← Fix: append, not overwrite
        }
    }

    header('Content-Type: application/json');
    echo json_encode($teacherList);
    exit; // ← Important to stop further output
}



}
