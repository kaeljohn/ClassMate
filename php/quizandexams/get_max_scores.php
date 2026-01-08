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

if ($section_id && $assessment_type && isset($max_score) && $max_score !== '') {
    
    // 1. Perform Insert/Update (Identical logic to save_max_score.php)
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