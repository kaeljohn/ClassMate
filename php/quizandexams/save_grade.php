<?php
session_start();
include '../db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['instructor_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$student_id = $_POST['student_id'] ?? null;
$section_id = $_POST['section_id'] ?? null;
$subject_id = $_POST['subject_id'] ?? null;
$type = $_POST['type'] ?? null;
$score = $_POST['score'] ?? 0;
$instructor = $_SESSION['instructor_name'];

if ($student_id && $section_id && $subject_id && $type) {
    $stmt = $conn->prepare("INSERT INTO student_grades (student_id, section_id, subject_id, instructor_id, assessment_type, score) 
                            VALUES (?, ?, ?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE score = VALUES(score)");
    $stmt->bind_param("iiisss", $student_id, $section_id, $subject_id, $instructor, $type, $score);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
}
?>