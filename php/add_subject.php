<?php
session_start();
include 'db_connect.php'; // Ensure this path is correct

if (!isset($_SESSION['instructor_name'])) {
    header("Location: ../instructor-login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Get data from the form (matching 'name' attributes in HTML)
    $code = $_POST['subjectCode']; 
    $name = $_POST['subjectName'];
    $inst = $_SESSION['instructor_name'];

    // 2. Prepare the SQL using your exact column names from the screenshot
    $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, instructor_id) VALUES (?, ?, ?)");
    
    // "sss" means 3 strings
    $stmt->bind_param("sss", $code, $name, $inst);

    if ($stmt->execute()) {
        // Redirect back with a success flag
        header("Location: ../instructor-home.php?success=1");
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>