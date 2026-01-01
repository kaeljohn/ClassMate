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
    $id = (int)$_GET['id'];
    $instructor = $_SESSION['instructor_name'];
    
    // Use a prepared statement to ensure the instructor only deletes their own sections
    $stmt = $conn->prepare("DELETE FROM sections WHERE section_id = ? AND instructor_id = ?");
    $stmt->bind_param("is", $id, $instructor);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Section deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Section not found or unauthorized.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'No Section ID provided.']);
}
$conn->close();
?>