<?php
session_start();
include '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['instructor_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$student_id  = $_POST['student_id']  ?? null;
$section_id  = $_POST['section_id']  ?? null;
$subject_id  = $_POST['subject_id']  ?? null;
$week        = $_POST['week']        ?? null;
$status      = $_POST['status']      ?? 'NONE';
$instructor_id = $_SESSION['instructor_name']; 

if ($student_id && $section_id && $week && $subject_id) {
    $stmt = $conn->prepare("INSERT INTO attendance_records (student_id, section_id, subject_id, instructor_id, week_number, status) 
                            VALUES (?, ?, ?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE status = VALUES(status), instructor_id = VALUES(instructor_id)");
    
    $stmt->bind_param("iiiiis", $student_id, $section_id, $subject_id, $instructor_id, $week, $status);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data provided']);
}
?>