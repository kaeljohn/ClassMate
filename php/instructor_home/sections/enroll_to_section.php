<?php
session_start();
include '../../database/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $section_id = $_POST['section_id'];
    $student_id = $_POST['student_id'];

    // Check if student is already in this section
    $check = $conn->query("SELECT * FROM section_assignments WHERE section_id = '$section_id' AND student_id = '$student_id'");
    
    if ($check->num_rows == 0) {
        $sql = "INSERT INTO section_assignments (section_id, student_id) VALUES ('$section_id', '$student_id')";
        $conn->query($sql);
    }
    
    header("Location: ../../../instructor-home.php?status=student_enrolled");
}
?>