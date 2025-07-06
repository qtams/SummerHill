<?php
include 'connection.php';

// Set content type to JSON for AJAX responses
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemName = $_POST['item_name'];
    $description = $_POST['description'];
    $roomFound = $_POST['room_found'] ?? '';
    $dateFound = $_POST['date_found'] ?? '';
    $status = $_POST['status'];
    $studentNumber = $_POST['claimed_by_student_number'] ?? null;
    $studentName = $_POST['claimed_by_name'] ?? null;
    
    // File upload
    $image_path = '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        // Use the existing images/lost-items directory
        $target_dir = "../images/lost-items/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Generate unique filename to prevent overwriting
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $unique_filename = uniqid() . "." . $file_extension;
        $image_path = "images/lost-items/" . $unique_filename; // Store relative path in DB
        $full_path = $target_dir . $unique_filename; // Full path for move_uploaded_file

        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            echo json_encode(['success' => false, 'message' => 'Error uploading file']);
            exit;
        }
    }
    
    $query = "INSERT INTO lost_items (
        item_name, description, image_path, status, 
        date_found, room_found, date_claimed, 
        claimed_by_student_number, claimed_by_name
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($con, $query);
    
    $dateClaimed = $status === 'claimed' ? date('Y-m-d') : null;
    
    mysqli_stmt_bind_param($stmt, "sssssssss", 
        $itemName, $description, $image_path, $status,
        $dateFound, $roomFound, $dateClaimed,
        $studentNumber, $studentName
    );
    
    if (mysqli_stmt_execute($stmt)) {
        // Return JSON success response for AJAX
        echo json_encode([
            'success' => true, 
            'message' => 'Item added successfully',
            'id' => mysqli_insert_id($con)
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Database error: ' . mysqli_error($con)
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>