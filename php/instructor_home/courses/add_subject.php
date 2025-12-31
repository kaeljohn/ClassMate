<?php
header('Content-Type: application/json');
session_start();
include '../../database/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST['subjectCode']);
    $name = trim($_POST['subjectName']);
    $inst = $_SESSION['instructor_name'];

    $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, instructor_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $code, $name, $inst);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Subject added successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
}
?>