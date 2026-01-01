<?php
header('Content-Type: application/json');
session_start();
include 'db_connect.php';

// Check if instructor is logged in
if (!isset($_SESSION['instructor_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

if (isset($_GET['id'])) {
    $student_id = (int)$_GET['id'];
    
    // We use a prepared statement to prevent SQL injection
    // Note: If you later add attendance or grades, you might want to check 
    // for those records or ensure ON DELETE CASCADE is set in the DB.
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Student removed successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Student record not found.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'No Student ID provided.']);
}

$conn->close();
?>