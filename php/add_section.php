<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['instructor_name'])) {
    header("Location: ../instructor-login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = $_POST['course_code'];
    $name = $_POST['section_name'];
    $sy   = $_POST['school_year']; // New variable
    $sem  = $_POST['semester'];
    $inst = $_SESSION['instructor_name'];

    // Prepared statement for security (bonus marks!)
    $stmt = $conn->prepare("INSERT INTO sections (course_code, section_name, semester, instructor_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $code, $name, $sem, $inst);

    if ($stmt->execute()) {
        header("Location: ../instructor-home.php?status=success");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>