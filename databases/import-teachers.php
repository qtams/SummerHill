<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Collect debug messages for frontend
$debug_messages = [];
function debug_log($message) {
    global $debug_messages;
    $debug_messages[] = $message;
    error_log("[IMPORT DEBUG] " . $message);
}

debug_log("Import script started");

require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
    debug_log("File upload detected");
    debug_log("File name: " . $_FILES['import_file']['name']);
    debug_log("File size: " . $_FILES['import_file']['size']);
    debug_log("File type: " . $_FILES['import_file']['type']);
    
    date_default_timezone_set('Asia/Manila');
    $file = $_FILES['import_file']['tmp_name'];
    $fileType = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);

    debug_log("File extension: " . $fileType);

    include "connection.php";

    if (!$con) {
        debug_log("Database connection failed: " . mysqli_connect_error());
        echo json_encode([
            "success" => false,
            "message" => "Connection failed: " . mysqli_connect_error(),
            "debug" => $debug_messages
        ]);
        exit();
    }

    debug_log("Database connection successful");

    try {
        if (!in_array($fileType, ['xlsx', 'xls'])) {
            debug_log("Invalid file type: " . $fileType);
            echo json_encode([
                "success" => false,
                "message" => "Invalid file type. Only XLSX or XLS files are allowed.",
                "debug" => $debug_messages
            ]);
            exit();
        }

        debug_log("Loading spreadsheet file");
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        debug_log("Total rows found: " . count($rows));

        if (count($rows) < 2) {
            debug_log("No data rows found");
            echo json_encode([
                "success" => false,
                "message" => "No data found in the file.",
                "debug" => $debug_messages
            ]);
            exit();
        }

        $errors = [];
        $successfulInserts = 0;

        debug_log("Starting row processing");

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip header

            debug_log("Processing row " . ($index + 1) . " with " . count($row) . " columns");

            // Ensure at least the required columns are present
            if (count($row) >= 13) {
                $teacher_id = trim($row[0]);
                $lastname = ucfirst(mb_strtolower(trim($row[1])));
                $firstname = ucfirst(mb_strtolower(trim($row[2])));
                $card_id = trim($row[3]);
                $email = trim($row[4]);
                $social = trim($row[5]);
                $mobile = trim($row[6]);
                $profile = trim($row[7]);
                $year_level = trim($row[8]);
                $updtime = trim($row[9]) ?: date('Y-m-d');
                $status = trim($row[10]) ?: 'ABSENT';
                $is_hidden = isset($row[11]) ? (int)$row[11] : 0;
                $position = trim($row[12]) ?: 'teacher';

                debug_log("Processing teacher: " . $teacher_id . " - " . $lastname . ", " . $firstname);

                // Check for duplicate
                $check_stmt = $con->prepare("SELECT teacher_id FROM teachers WHERE teacher_id = ?");
                if (!$check_stmt) {
                    debug_log("Prepare failed for duplicate check: " . $con->error);
                    $errors[] = "Row " . ($index + 1) . ": Database prepare error for duplicate check.";
                    continue;
                }

                $check_stmt->bind_param("s", $teacher_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    debug_log("Duplicate found for teacher_id: " . $teacher_id);
                    $errors[] = "Row " . ($index + 1) . ": Teacher ID {$teacher_id} already exists.";
                    $check_stmt->close();
                    continue;
                }
                $check_stmt->close();

                // Insert query (matches your table structure)
                $stmt = $con->prepare("
                    INSERT INTO teachers 
                    (teacher_id, lastname, firstname, card_id, email, social, mobile, profile, year_level, updtime, status, is_hidden, position)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                if (!$stmt) {
                    debug_log("Prepare failed for insert: " . $con->error);
                    $errors[] = "Row " . ($index + 1) . ": Database prepare error for insert.";
                    continue;
                }

                $stmt->bind_param(
                    "sssssssssssis",
                    $teacher_id, $lastname, $firstname, $card_id, $email, $social, $mobile, $profile, $year_level, $updtime, $status, $is_hidden, $position
                );

                if ($stmt->execute()) {
                    $successfulInserts++;
                    debug_log("Successfully inserted teacher: " . $teacher_id);
                } else {
                    debug_log("Insert failed for teacher: " . $teacher_id . " - " . $stmt->error);
                    $errors[] = "Row " . ($index + 1) . ": Insert error for Teacher ID {$teacher_id} - " . $stmt->error;
                }

                $stmt->close();
            } else {
                debug_log("Row " . ($index + 1) . " skipped - only " . count($row) . " columns");
                $errors[] = "Row " . ($index + 1) . ": Skipped due to missing columns (found " . count($row) . ", need 13).";
            }
        }

        debug_log("Processing complete. Successful inserts: " . $successfulInserts);

        echo json_encode([
            "success" => $successfulInserts > 0,
            "message" => "{$successfulInserts} teacher(s) inserted successfully.",
            "errors" => $errors,
            "debug" => $debug_messages
        ]);
        exit();

    } catch (Exception $e) {
        debug_log("Exception occurred: " . $e->getMessage());
        echo json_encode([
            "success" => false,
            "message" => "Exception: " . $e->getMessage(),
            "debug" => $debug_messages
        ]);
        exit();
    }

    $con->close();
} else {
    debug_log("No file uploaded or upload error occurred");
    if (isset($_FILES['import_file'])) {
        debug_log("File upload error code: " . $_FILES['import_file']['error']);
    }
    echo json_encode([
        "success" => false,
        "message" => "No file uploaded or upload error.",
        "debug" => $debug_messages
    ]);
}
?>