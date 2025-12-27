<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = $_POST['course_code'];
    $name = $_POST['section_name'];
    $sem = $_POST['semester'];
    $inst = $_SESSION['instructor_name'];

    $sql = "INSERT INTO sections (course_code, section_name, semester, instructor_id) 
            VALUES ('$code', '$name', '$sem', '$inst')";

    if ($conn->query($sql)) {
        header("Location: ../instructor-home.php?success=1");
    }
}
?>