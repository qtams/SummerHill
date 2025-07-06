<?php
// Include database connection
include 'connection.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if the request is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the raw POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Check if the request contains a single ID or multiple IDs
    $ids = [];
    if (isset($data['id'])) {
        // Single item deletion
        $ids[] = $data['id'];
    } elseif (isset($data['ids']) && is_array($data['ids'])) {
        // Bulk deletion
        $ids = $data['ids'];
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or missing item ID(s)'
        ]);
        exit();
    }

    try {
        $deletedCount = 0;

        foreach ($ids as $id) {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id) {
                continue; // Skip invalid IDs
            }

            // First, get the image path to delete the file later
            $query = "SELECT image_path FROM lost_items WHERE id = ?";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $image_path = $row['image_path'];

                // Delete the item from the database
                $deleteQuery = "DELETE FROM lost_items WHERE id = ?";
                $deleteStmt = mysqli_prepare($con, $deleteQuery);
                mysqli_stmt_bind_param($deleteStmt, "i", $id);
                $deleteResult = mysqli_stmt_execute($deleteStmt);

                if ($deleteResult) {
                    $deletedCount++;

                    // If the item was deleted successfully, delete its image file if it exists
                    if ($image_path && file_exists('../' . $image_path) && basename($image_path) !== 'no-image.png') {
                        unlink('../' . $image_path);
                    }
                }
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "$deletedCount item(s) deleted successfully"
        ]);
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
        'message' => 'Invalid request method'
    ]);
}
