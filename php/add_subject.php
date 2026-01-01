<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $instructor = $_SESSION['instructor_name'];
    $schedCode = $_POST['schedCode'];
    $subjectCode = $_POST['subjectCode'];
    $subjectName = $_POST['subjectName'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    $schedDay = $_POST['schedDay'];

    $sql = "INSERT INTO subjects (instructor_id, sched_code, subject_code, subject_name, start_time, end_time, sched_day) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $instructor, $schedCode, $subjectCode, $subjectName, $startTime, $endTime, $schedDay);

    if ($stmt->execute()) {
        echo "<script>alert('Subject added successfully!'); window.location.href='../instructor-home.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
    }
    $stmt->close();
    $conn->close();
}
?>