<?php
// Include database connection
include 'connection.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get basic item information
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $item_name = mysqli_real_escape_string($con, $_POST['item_name']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $room_found = mysqli_real_escape_string($con, $_POST['room_found']);
    $date_found = mysqli_real_escape_string($con, $_POST['date_found']);
    $status = mysqli_real_escape_string($con, $_POST['status']);

    // Validate required fields
    if (!$id || empty($item_name) || empty($description) || empty($room_found) || empty($date_found) || empty($status)) {
        echo json_encode([
            'success' => false,
            'message' => 'All required fields must be filled out.'
        ]);
        exit();
    }

    // Initialize variables
    $image_path = null;
    $old_image_path = null;

    // First, get the current image path from the database
    $query = "SELECT image_path FROM lost_items WHERE id = $id";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $old_image_path = $row['image_path'];
    }

    // Handle image upload if a new image was provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_dir = '../images/lost-items/';

        // Ensure upload directory exists
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_path = $upload_dir . $filename;
        $relative_path = 'images/lost-items/' . $filename;

        // Check file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        $file_type = $_FILES['image']['type'];

        if (!in_array($file_type, $allowed_types)) {
            echo json_encode([
                'success' => false,
                'message' => 'Only JPG, JPEG, PNG and GIF files are allowed.'
            ]);
            exit();
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_path = $relative_path;

            // Delete the old image if it exists and isn't the default no-image.png
            if (
                $old_image_path &&
                file_exists('../' . $old_image_path) &&
                basename($old_image_path) !== 'no-image.png'
            ) {
                unlink('../' . $old_image_path);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to upload image.'
            ]);
            exit();
        }
    }

    try {
        // Start building the SQL query
        $sql = "UPDATE lost_items SET 
                item_name = '$item_name', 
                description = '$description', 
                room_found = '$room_found', 
                date_found = '$date_found', 
                status = '$status'";

        // Handle claimed status fields
        if ($status === 'claimed') {
            $claimed_by_student_number = mysqli_real_escape_string($con, $_POST['claimed_by_student_number']);
            $claimed_by_name = mysqli_real_escape_string($con, $_POST['claimed_by_name']);
            $date_claimed = mysqli_real_escape_string($con, $_POST['date_claimed']);

            // Validate claimed fields
            if (empty($claimed_by_student_number) || empty($claimed_by_name) || empty($date_claimed)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'All claim information must be provided when status is set to claimed.'
                ]);
                exit();
            }

            // Add claimed fields to SQL
            $sql .= ", claimed_by_student_number = '$claimed_by_student_number', 
                     claimed_by_name = '$claimed_by_name', 
                     date_claimed = '$date_claimed'";
        } else {
            // If status is unclaimed, reset claimed fields
            $sql .= ", claimed_by_student_number = NULL, 
                     claimed_by_name = NULL, 
                     date_claimed = NULL";
        }

        // Add image path to SQL if a new image was uploaded
        if ($image_path) {
            $sql .= ", image_path = '$image_path'";
        }

        // Complete the SQL query
        $sql .= " WHERE id = $id";

        // Execute the query
        $result = mysqli_query($con, $sql);

        // Check if the update was successful
        if ($result) {
            if (mysqli_affected_rows($con) > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Item updated successfully.'
                ]);
            } else {
                echo json_encode([
                    'success' => true, // Still consider it a success if no changes were needed
                    'message' => 'No changes were made to the item.'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . mysqli_error($con)
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    // Not a POST request
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
