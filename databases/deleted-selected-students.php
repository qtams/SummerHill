    <?php
    include 'connection.php';
    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!isset($_POST['student_id']) || empty($_POST['student_id'])) {
            echo json_encode(["status" => "error", "message" => "Trainee ID is required."]);
            exit;
        }

        $student_ids = json_decode($_POST['student_id']);

        if (!is_array($student_ids) || empty($student_ids)) {
            echo json_encode(["status" => "error", "message" => "Invalid trainee IDs provided."]);
            exit;
        }

        $successCount = 0;
        $errors = [];

        foreach ($student_ids as $student_id) {
            // 1. Fetch the profile path for the trainee
            $stmt_select = $con->prepare("SELECT profile FROM students WHERE student_id = ?");
            $stmt_select->bind_param("s", $student_id);
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
            $stmt_delete_time = $con->prepare("DELETE FROM time_monitoring WHERE student_id = ?");
            $stmt_delete_time->bind_param("s", $student_id);
            $stmt_delete_time->execute();
            $stmt_delete_time->close();

            // 4. Delete the trainee record from the database
            $stmt = $con->prepare("DELETE FROM students WHERE student_id = ?");
            $stmt->bind_param("s", $student_id);

            if ($stmt->execute()) {
                $successCount++;
            } else {
                $errors[] = "Failed to delete student ID: $student_id";
            }

            $stmt->close();
        }

        if ($successCount > 0 && empty($errors)) {
            echo json_encode(["status" => "success", "message" => "Successfully deleted $successCount Student(s) and related records."]);
        } else if (!empty($errors)) {
            echo json_encode(["status" => "partial", "message" => implode("\n", $errors), "successCount" => $successCount]);
        } else {
            echo json_encode(["status" => "error", "message" => "No Student were deleted."]);
        }

        $con->close();
    }
    ?>
