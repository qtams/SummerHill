<?php
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    date_default_timezone_set('Asia/Manila');
    include 'databases/connection.php';

    if (isset($_GET['get_data']) && $_GET['get_data'] === 'student') {
        $students = [];

        // Add custom ordering using FIELD()
        $sql = "SELECT * FROM students ";

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
    // Return JSON for student details when ?get_data=students&student_id=...
    if (isset($_GET['get_data']) && $_GET['get_data'] === 'students') {
        // Sanitize input to prevent SQL injection
        $student_id = mysqli_real_escape_string($con, $_GET['student_id']);

        $sql = "SELECT student_id, card_id, lastname, firstname, section, year_level 
            FROM students 
            WHERE student_id = '$student_id' 
            LIMIT 1";

        $result = mysqli_query($con, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $student = mysqli_fetch_assoc($result);
            header("Content-Type: application/json");
            echo json_encode($student);
        } else {
            header("Content-Type: application/json");
            echo json_encode(['error' => 'Student not found']);
        }
        exit();
    }

    if (isset($_GET['get_data']) && $_GET['get_data'] === 'sections') {
        $sections = [];

        // Get distinct section values (non-empty only), sorted ascending
        $sql = "SELECT DISTINCT section FROM students WHERE section != '' ORDER BY section ASC";
        $result = mysqli_query($con, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $sections[] = $row['section'];
            }
        }

        header("Content-Type: application/json");
        echo json_encode($sections);
        exit();
    }
    if (isset($_GET['get_data']) && $_GET['get_data'] === 'section_yearlevel') {
        $sectionYearLevels = [];

        $sql = "SELECT DISTINCT CONCAT(section, ' ', year_level) AS section_year 
            FROM students 
            WHERE section != '' AND year_level IS NOT NULL
            ORDER BY section ASC, year_level ASC";
        $result = mysqli_query($con, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $sectionYearLevels[] = $row['section_year'];
            }
        }

        header("Content-Type: application/json");
        echo json_encode($sectionYearLevels);
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

        // Get student data using card_id
        $sql = "SELECT student_id, year_level, lastname, firstname, profile, email, social 
            FROM students 
            WHERE card_id = '$card_id'";
        $result = mysqli_query($con, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $student = mysqli_fetch_assoc($result);
            $student_id = $student['student_id'];
            $response = $student;

            // Check if there's an open time_in for today (not yet timed out)
            $checkSql = "SELECT * FROM time_monitoring 
                     WHERE student_id = '$student_id' 
                     AND DATE(time_in) = CURDATE() 
                     AND time_out = '0000-00-00 00:00:00' 
                     ORDER BY time_id DESC LIMIT 1";
            $checkResult = mysqli_query($con, $checkSql);

            if ($checkResult && mysqli_num_rows($checkResult) > 0) {
                // Time-out
                $row = mysqli_fetch_assoc($checkResult);
                $time_id = $row['time_id'];
                $time_out = date('Y-m-d H:i:s');

                $updateSql = "UPDATE time_monitoring 
                          SET time_out = '$time_out' 
                          WHERE time_id = '$time_id'";
                if (!mysqli_query($con, $updateSql)) {
                    $response['time_monitoring_error'] = 'Failed to record time-out: ' . mysqli_error($con);
                } else {
                    $response['success'] = 'Time-out recorded successfully';
                }
            } else {
                // Time-in
                $currentDateTime = date('Y-m-d H:i:s');
                $insertSql = "INSERT INTO time_monitoring (student_id, time_in) 
                          VALUES ('$student_id', '$currentDateTime')";
                if (!mysqli_query($con, $insertSql)) {
                    $response['time_monitoring_error'] = 'Failed to record time-in: ' . mysqli_error($con);
                } else {
                    $response['success'] = 'Time-in recorded successfully';
                }
            }
        } else {
            $response = ['error' => 'Trainee not found for this card ID'];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    //STUDENT NUMBER MANUAL INPUT
    if (isset($_GET['get_data']) && $_GET['get_data'] === 'student_id') {
        date_default_timezone_set('Asia/Manila');
        $student_id = mysqli_real_escape_string($con, $_GET['student_id'] ?? '');
        $response = [];

        // Get student data using card_id
        $sql = "SELECT student_id, year_level, lastname, firstname, profile, social 
            FROM students 
            WHERE student_id = '$student_id'";
        $result = mysqli_query($con, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $student = mysqli_fetch_assoc($result);
            $student_id = $student['student_id'];
            $response = $student;

            // Check if there's an open time_in for today (not yet timed out)
            $checkSql = "SELECT * FROM time_monitoring 
                     WHERE student_id = '$student_id' 
                     AND DATE(time_in) = CURDATE() 
                     AND time_out = '0000-00-00 00:00:00' 
                     ORDER BY time_id DESC LIMIT 1";
            $checkResult = mysqli_query($con, $checkSql);

            if ($checkResult && mysqli_num_rows($checkResult) > 0) {
                // Time-out
                $row = mysqli_fetch_assoc($checkResult);
                $time_id = $row['time_id'];
                $time_out = date('Y-m-d H:i:s');

                $updateSql = "UPDATE time_monitoring 
                          SET time_out = '$time_out' 
                          WHERE time_id = '$time_id'";
                if (!mysqli_query($con, $updateSql)) {
                    $response['time_monitoring_error'] = 'Failed to record time-out: ' . mysqli_error($con);
                } else {
                    $response['success'] = 'Time-out recorded successfully';
                }
            } else {
                // Time-in
                $currentDateTime = date('Y-m-d H:i:s');
                $insertSql = "INSERT INTO time_monitoring (student_id, time_in) 
                          VALUES ('$student_id', '$currentDateTime')";
                if (!mysqli_query($con, $insertSql)) {
                    $response['time_monitoring_error'] = 'Failed to record time-in: ' . mysqli_error($con);
                } else {
                    $response['success'] = 'Time-in recorded successfully';
                }
            }
        } else {
            $response = ['error' => 'Trainee not found for this card ID'];
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
                s.section, 
                s.lastname, 
                s.firstname, 
                s.year_level, 
                s.position, 
                s.is_hidden 
            FROM time_monitoring t
            JOIN students s ON t.student_id = s.student_id
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
