<?php
header('Content-Type: application/json');
include '../db_connect.php';

if (isset($_GET['section_id']) && isset($_GET['subject_id'])) {
    $section_id = mysqli_real_escape_string($conn, $_GET['section_id']);
    $subject_id = mysqli_real_escape_string($conn, $_GET['subject_id']);
    
    // 1. Fetch grades matching the criteria
    $sql = "SELECT student_id, assessment_type, score 
            FROM student_grades 
            WHERE section_id = '$section_id' AND subject_id = '$subject_id'";
            
    $result = $conn->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) $data[] = $row;
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing section or subject ID']);
}
$conn->close();
?>
