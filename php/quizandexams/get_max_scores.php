<?php
session_start();
header('Content-Type: application/json');
include '../db_connect.php';

if (!isset($_SESSION['instructor_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}
$instructor_id = $_SESSION['instructor_name'];

if (isset($_GET['section_id'])) {
    $section_id = mysqli_real_escape_string($conn, $_GET['section_id']);
    $subject_id = isset($_GET['subject_id']) ? mysqli_real_escape_string($conn, $_GET['subject_id']) : 0;
    
    $sql = "SELECT assessment_type, max_score FROM assessment_settings WHERE section_id = '$section_id' AND instructor_id = '$instructor_id'";
    if($subject_id) {
        $sql .= " AND (subject_id = '$subject_id' OR subject_id = 0)";
    }

    $result = $conn->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing section_id']);
}
$conn->close();
?>