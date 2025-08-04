<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';
include 'databases/connection.php'; // DB connection

header('Content-Type: application/json');

// ✅ Always set timezone
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$card_id = trim($_POST['card_id'] ?? '');
$email = trim($_POST['email'] ?? '');

if (empty($card_id)) {
    echo json_encode(['success' => false, 'message' => 'Card ID is required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

try {
    // ✅ Get student info, using 'mobile' column
    $stmt = $con->prepare("SELECT student_id, firstname, lastname, mobile FROM students WHERE card_id = ?");
    $stmt->bind_param("s", $card_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }
    $student = $result->fetch_assoc();
    $student_id = $student['student_id'];
    $fullname = strtoupper($student['firstname'] . ' ' . $student['lastname']);
    $phone_number = $student['mobile'];

    if (empty($phone_number)) {
        echo json_encode(['success' => false, 'message' => 'No mobile number found for this student']);
        exit;
    }

    // ✅ Get today's time log
    $today = date('Y-m-d');
    $stmt = $con->prepare("
        SELECT time_in, time_out 
        FROM time_monitoring 
        WHERE student_id = ? AND DATE(time_in) = ? 
        ORDER BY time_in DESC LIMIT 1
    ");
    $stmt->bind_param("ss", $student_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $time_in = "No record";
        $time_out = "No record";
    } else {
        $row = $result->fetch_assoc();
        $time_in = date('M d, Y : h:ia', strtotime($row['time_in']));

        if (empty($row['time_out']) || $row['time_out'] === '0000-00-00 00:00:00') {
            $time_out = "NO TIME OUT YET";
        } else {
            $time_out = date('M d, Y : h:ia', strtotime($row['time_out']));
        }
    }

    // ✅ Compose the email
    $subject = "Trainee Monitoring Notification";
    $body = "
    <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;'>
        <h2 style='color: #007BFF;'>Trainee Entry Notification</h2>
        <p><strong>Card ID:</strong> {$card_id}</p>
        <p><strong>Student ID:</strong> {$student_id}</p>
        <p><strong>Name:</strong> {$fullname}</p>
        <hr>
        <p><strong>Entering School:</strong><br>{$time_in}</p>
        <p><strong>Out of School:</strong><br>{$time_out}</p>
        <footer style='margin-top: 30px; text-align: center; font-size: 12px; color: #777;'>
            This is an automated email. Please do not reply.
        </footer>
    </div>";

    // ✅ Send email with PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'buendia.tamahome@ue.edu.ph';
    $mail->Password = 'nclz ryfw futr zoos';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('buendia.tamahome@ue.edu.ph', 'TAMAHOME');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $body;

    $mail->send();

    // ✅ Send SMS with Semaphore
    $api_key = 'd2ffcf85ca9e39ff4444afd10497c436'; // ✅ your Semaphore API Key
    $sms_number = $phone_number; // ✅ mobile from DB, format 09XXXXXXXXX
    $sms_message = "Hello {$student['firstname']}! Card ID: {$card_id} | IN: {$time_in} | OUT: {$time_out}";

    $ch = curl_init();

    $sms_parameters = array(
        'apikey' => $api_key,
        'number' => $sms_number,
        'message' => $sms_message,
        'sendername' => 'SEMAPHORE'
    );

    curl_setopt($ch, CURLOPT_URL, 'https://api.semaphore.co/api/v4/messages');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sms_parameters));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $sms_output = curl_exec($ch);
    curl_close($ch);

    echo json_encode(['success' => true, 'sms_response' => $sms_output]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Mailer Error: ' . $e->getMessage()]);
}
