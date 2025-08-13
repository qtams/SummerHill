<?php
include 'databases/connection.php';
// Don't include webhook.php as it has its own database connection
// We'll define the access token directly here

header('Content-Type: application/json');

// Set timezone
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$card_id = trim($_POST['card_id'] ?? '');
$social = trim($_POST['social'] ?? '');

// Debug logging
error_log("send-messenger.php called with card_id: $card_id, social: $social");

if (empty($card_id)) {
    echo json_encode(['success' => false, 'message' => 'Card ID or Student ID is required']);
    exit;
}

if (empty($social)) {
    echo json_encode(['success' => false, 'message' => 'No social account linked to this student']);
    exit;
}

try {
    // Check if database connection is working
    if (!$con) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    // Get student info - try card_id first, then student_id
    $stmt = $con->prepare("SELECT student_id, firstname, lastname, social, card_id FROM students WHERE card_id = ? OR student_id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database prepare failed: ' . $con->error]);
        exit;
    }
    
    $stmt->bind_param("ss", $card_id, $card_id);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Database execute failed: ' . $stmt->error]);
        exit;
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Student not found for ID: ' . $card_id]);
        exit;
    }
    
    $student = $result->fetch_assoc();
    $student_id = $student['student_id'];
    $actual_card_id = $student['card_id'];
    $fullname = strtoupper($student['firstname'] . ' ' . $student['lastname']);
    
    // Debug logging
    error_log("Student found: " . json_encode($student));

    // Get today's time log to determine if it's tap in or tap out
    $today = date('Y-m-d');
    $stmt = $con->prepare("
        SELECT time_in, time_out 
        FROM time_monitoring 
        WHERE student_id = ? AND DATE(time_in) = ? 
        ORDER BY time_in DESC LIMIT 1
    ");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Time monitoring prepare failed: ' . $con->error]);
        exit;
    }
    
    $stmt->bind_param("ss", $student_id, $today);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Time monitoring execute failed: ' . $stmt->error]);
        exit;
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $time_in = "No record";
        $time_out = "No record";
        $tap_type = "TAP IN";
    } else {
        $row = $result->fetch_assoc();
        $time_in = date('M d, Y : h:ia', strtotime($row['time_in']));

        if (empty($row['time_out']) || $row['time_out'] === '0000-00-00 00:00:00') {
            $time_out = "NO TIME OUT YET";
            $tap_type = "TAP OUT";
        } else {
            $time_out = date('M d, Y : h:ia', strtotime($row['time_out']));
            $tap_type = "TAP IN";
        }
    }

    // Get the most recent time record to show the current status
    $current_time = date('M d, Y : h:ia');

    // Compose the messenger message
    $message = "🏫 SUMMERHILL SCHOOL FOUNDATION, INC.\n\n";
    $message .= "👤 Student: {$fullname}\n";
    $message .= "🆔 Student ID: {$student_id}\n";
    if (!empty($actual_card_id)) {
        $message .= "🆔 Card ID: {$actual_card_id}\n";
    }
    $message .= "📅 Date: " . date('M d, Y') . "\n";
    $message .= "⏰ Time: " . date('h:ia') . "\n\n";
    
    if ($tap_type === "TAP IN") {
        $message .= "✅ {$tap_type} - Welcome to school!\n";
        $message .= "🕐 Time In: {$current_time}\n";
    } else {
        $message .= "👋 {$tap_type} - Have a great day!\n";
        $message .= "🕐 Time Out: {$current_time}\n";
    }

    // Send messenger message
    $access_token = "EAALCxdA8ZBOsBPCPi537k1gBepA2HuGlA9JR3PlSmhYwpZCLTqYYgC9eE1I2Jham9BvOSbgB6FuRBRr8xXR9gt1ZC1XZC2zmuo7Mm2CChtgUZCwp2yvpO9VxnU8pIz5zONim7NZBhhXnZCXKDBymOkM2qVJSdspfyV9PuIa4goT6TLPxtw9Ko2W2nq6nYTACe9M5qRLxUNrpwZDZD";
    
    $response = [
        'recipient' => ['id' => $social],
        'message' => ['text' => $message]
    ];
    
    // Debug logging
    error_log("Sending message to social ID: $social");
    error_log("Message content: $message");
    error_log("Full response: " . json_encode($response));

    $ch = curl_init("https://graph.facebook.com/v17.0/me/messages?access_token=$access_token");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        echo json_encode(['success' => true, 'message' => 'Messenger notification sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send messenger notification', 'response' => $result, 'http_code' => $httpCode]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
