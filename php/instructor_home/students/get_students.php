<?php
include '../../database/db_connect.php';

if (isset($_GET['subject_id'])) {
    $subject_id = intval($_GET['subject_id']);
    
    $query = "SELECT student_number, full_name, email FROM students WHERE enrolled_subject_id = $subject_id";
    $result = $conn->query($query);
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($students);
}
?>