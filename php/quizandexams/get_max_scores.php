<?php
session_start();
include '../db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['instructor_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}
$instructor_id = $_SESSION['instructor_name'];

$section_id = $_POST['section_id'] ?? null;
$subject_id = $_POST['subject_id'] ?? null;
$assessment_type = $_POST['assessment_type'] ?? null;
$max_score = $_POST['max_score'] ?? null;

// FIX: We must check if max_score is set and not empty string, but explicitly ALLOW '0'.
// Previous code: if ($max_score) failed because 0 is false in PHP.
if ($section_id && $assessment_type && isset($max_score) && $max_score !== '') {
    
    $stmt = $conn->prepare("INSERT INTO assessment_settings (section_id, subject_id, instructor_id, assessment_type, max_score) 
                            VALUES (?, ?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE max_score = VALUES(max_score), instructor_id = VALUES(instructor_id)");
    
    $sub_id_val = $subject_id ? $subject_id : 0;
    
    $stmt->bind_param("iiisi", $section_id, $sub_id_val, $instructor_id, $assessment_type, $max_score);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data (Zero check failed)']);
}
$conn->close();
?>