<?php
session_start();
include '../db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject_id = $_POST['subject_id'];
    $schedCode = $_POST['schedCode'];
    $subjectCode = $_POST['subjectCode'];
    $subjectName = $_POST['subjectName'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    $schedDay = $_POST['schedDay'];

    $startTs = strtotime($startTime);
    $endTs = strtotime($endTime);

    // 1. Time Validation
    if ($endTs <= $startTs) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'End time must be after start time.'
        ]);
        exit();
    }

    // 2. Duration Limit (Note: This file limits to 2 hours/7200s, slightly different from add_subject)
    $duration = $endTs - $startTs;
    if ($duration > 7200) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Subject duration cannot exceed 2 hours.'
        ]);
        exit();
    }

    // 3. Update Record
    $sql = "UPDATE subjects SET 
            sched_code = ?, 
            subject_code = ?, 
            subject_name = ?, 
            start_time = ?, 
            end_time = ?, 
            sched_day = ? 
            WHERE subject_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $schedCode, $subjectCode, $subjectName, $startTime, $endTime, $schedDay, $subject_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message'=> 'Subject Edited Successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update: ' . $conn->error]);
    }
    $stmt->close();
    $conn->close();
}
?>
