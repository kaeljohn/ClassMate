<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == HTMLPOST) {
    $subject_id = $_POST['subject_id'];
    $section_name = $_POST['section_name'];
    $school_year = $_POST['school_year'];
    $semester = $_POST['semester'];

    $sql = "INSERT INTO sections (section_name, subject_id, school_year, semester) 
            VALUES ('$section_name', '$subject_id', '$school_year', '$semester')";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../instructor-home.php?status=section_added");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>