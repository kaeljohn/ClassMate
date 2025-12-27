<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $student_number = $conn->real_escape_string($_POST['student_number']);
    $email = $conn->real_escape_string($_POST['email']);

    $sql = "INSERT INTO students (last_name, first_name, middle_name, student_number, email) 
        VALUES ('$last_name', '$first_name', '$middle_name', '$student_number', '$email')";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../instructor-home.php?status=student_created");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>