<?php
header('Content-Type: application/json');
session_start();
include '../db_connect.php';

// 1. Authorization check
if (!isset($_SESSION['instructor_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $instructor = $_SESSION['instructor_name'];
    
    // 2. Prepare DELETE statement
    // Crucial: We verify 'instructor_id' matches the session to prevent deleting others' data
    $stmt = $conn->prepare("DELETE FROM sections WHERE section_id = ? AND instructor_id = ?");
    $stmt->bind_param("is", $id, $instructor);

    if ($stmt->execute()) {
        // 3. Check if any row was actually deleted
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
