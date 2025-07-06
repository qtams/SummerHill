<?php
// Always set this header FIRST
header('Content-Type: application/json');

// For debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php'; // PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
    date_default_timezone_set('Asia/Manila');
    $file = $_FILES['import_file']['tmp_name'];
    $fileType = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);

    include "connection.php";

    if (!$con) {
        echo json_encode([
            "success" => false,
            "message" => "Connection failed: " . mysqli_connect_error()
        ]);
        exit();
    }

    try {
        if (!in_array($fileType, ['xlsx', 'xls'])) {
            echo json_encode([
                "success" => false,
                "message" => "Invalid file type. Only XLSX or XLS files are allowed."
            ]);
            exit();
        }

        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) < 2) {
            echo json_encode([
                "success" => false,
                "message" => "No data found in the file."
            ]);
            exit();
        }

        $errors = [];
        $successfulInserts = 0;

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip header row

            if (count($row) >= 5) {
                // Clean data
                $student_id = trim($row[0]);
                $lastname = ucfirst(mb_strtolower(trim($row[1])));
                $firstname = ucfirst(mb_strtolower(trim($row[2])));
                $section = trim($row[3]);
                $year_level = (int) filter_var($row[4], FILTER_SANITIZE_NUMBER_INT);
                $created_at = date('Y-m-d');

                // Defaults as empty strings
                $card_id = '';
                $email = '';
                $social = '';
                $mobile = '';
                $profile = '';
                $position = 'student';
                $is_hidden = 0;

                // Check for duplicate
                $check_stmt = $con->prepare("SELECT student_id FROM students WHERE student_id = ?");
                $check_stmt->bind_param("s", $student_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $errors[] = "Row " . ($index + 1) . ": Student ID {$student_id} already exists.";
                    $check_stmt->close();
                    continue;
                }
                $check_stmt->close();

                // Insert
                $stmt = $con->prepare("
                    INSERT INTO students 
                    (card_id, student_id, lastname, firstname, section, year_level, profile, email, social, mobile, position, is_hidden, updtime)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param(
                    "sssssisssssis",
                    $card_id, $student_id, $lastname, $firstname, $section, $year_level,
                    $profile, $email, $social, $mobile, $position, $is_hidden, $created_at
                );

                if ($stmt->execute()) {
                    $successfulInserts++;
                } else {
                    $errors[] = "Row " . ($index + 1) . ": Insert error for Student ID {$student_id} - " . $stmt->error;
                }

                $stmt->close();
            } else {
                $errors[] = "Row " . ($index + 1) . " skipped: Missing columns.";
            }
        }

        echo json_encode([
            "success" => $successfulInserts > 0,
            "message" => "{$successfulInserts} students inserted successfully.",
            "errors" => $errors
        ]);
        exit();
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => "Exception: " . $e->getMessage()
        ]);
        exit();
    }

    $con->close();
} else {
    echo json_encode([
        "success" => false,
        "message" => "No file uploaded or file error occurred."
    ]);
}
