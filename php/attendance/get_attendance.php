<?php
header('Content-Type: application/json');
include '../db_connect.php';

if (isset($_GET['section_id']) && isset($_GET['subject_id'])) {
    $section_id = mysqli_real_escape_string($conn, $_GET['section_id']);
    $subject_id = mysqli_real_escape_string($conn, $_GET['subject_id']);

    // 1. Select attendance status for the specific class context
    $sql = "SELECT student_id, week_number, status 
            FROM attendance_records 
            WHERE section_id = '$section_id' AND subject_id = '$subject_id'";
            
    $result = $conn->query($sql);

    $attendance_data = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $attendance_data[] = [
                'student_id'  => (int)$row['student_id'],
                'week_number' => (int)$row['week_number'],
                'status'      => $row['status']
            ];
        }
        echo json_encode(['status' => 'success', 'data' => $attendance_data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing section_id or subject_id']);
}
$conn->close();
?>