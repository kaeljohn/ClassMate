<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_ids'])) {
    $section_id = $_POST['section_id'];
    $student_ids = $_POST['student_ids']; // This is an array of IDs

    foreach ($student_ids as $student_id) {
        // We use INSERT IGNORE or a check to prevent duplicate enrollments
        $sql = "INSERT INTO section_assignments (section_id, student_id, date_enrolled) 
                VALUES ('$section_id', '$student_id', NOW())";
        $conn->query($sql);
    }

    header("Location: ../instructor-home.php?status=enrolled");
} else {
    header("Location: ../instructor-home.php?error=no_students_selected");
}
?>