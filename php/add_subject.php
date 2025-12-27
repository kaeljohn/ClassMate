<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['instructor_name'])) {
    header("Location: ../instructor-login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = $_POST['subject_code'];
    $name = $_POST['subject_name'];
    $inst = $_SESSION['instructor_name'];

    // Prepared statement for security
    $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, instructor_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $code, $name, $inst);

    if ($stmt->execute()) {
        // Redirect back to dashboard with a success message
        header("Location: ../instructor-home.php?msg=subject_added");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>