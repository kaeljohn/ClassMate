<?php
session_start();
include '../db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['instructor_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$student_id = $_POST['student_id'] ?? null;
$subject_id = $_POST['subject_id'] ?? null;

if ($student_id && $subject_id) {
    $stmt = $conn->prepare("DELETE FROM enrollments WHERE student_id = ? AND subject_id = ?");
    $stmt->bind_param("ii", $student_id, $subject_id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
}
?>