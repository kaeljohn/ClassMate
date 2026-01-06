<?php
session_start();
include '../db_connect.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $instructor = $_SESSION['instructor_name'];
    $schedCode = $_POST['schedCode'];
    $subjectCode = $_POST['subjectCode'];
    $subjectName = $_POST['subjectName'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    $schedDay = $_POST['schedDay'];

    $startTs = strtotime($startTime);
    $endTs = strtotime($endTime);

    if ($endTs <= $startTs) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'End time must be after start time.'
        ]);
        exit();
    }

    $duration = $endTs - $startTs;
    if ($duration > 7200) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Subject duration cannot exceed 2 hours.'
        ]);
        exit();
    }

    $checkSql = "SELECT subject_id FROM subjects WHERE instructor_id = ? AND subject_code = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $instructor, $subjectCode);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode([
            'status' => 'error', 
            'message' => "The subject code '$subjectCode' is already in your dashboard."
        ]);
        $checkStmt->close();
        exit();
    }
    $checkStmt->close();

    $sql = "INSERT INTO subjects (instructor_id, sched_code, subject_code, subject_name, start_time, end_time, sched_day) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $instructor, $schedCode, $subjectCode, $subjectName, $startTime, $endTime, $schedDay);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message'=> 'Subject Created Successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
    $conn->close();
}
?>