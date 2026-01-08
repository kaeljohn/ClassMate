<?php
session_start();
include '../db_connect.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentId = $_POST['student_id'];
    $subjectId = $_POST['subject_id'];

    // 1. Check if enrollment already exists
    $check = $conn->prepare("SELECT enrollment_id FROM enrollments WHERE student_id = ? AND subject_id = ?");
    $check->bind_param("ii", $studentId, $subjectId);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Student is already enrolled in this subject.']);
        exit();
    }

    // 2. Create the enrollment record
    $stmt = $conn->prepare("INSERT INTO enrollments (student_id, subject_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $studentId, $subjectId);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Student successfully enrolled!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Enrollment failed: ' . $conn->error]);
    }
    $stmt->close();
}
$conn->close();
?>