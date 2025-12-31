<?php
header('Content-Type: application/json');
include '../../database/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sub_id = $_POST['subject_id'];
    $sec_name = trim($_POST['section_name']);
    $sy = trim($_POST['school_year']);
    $sem = $_POST['semester'];

    $stmt = $conn->prepare("INSERT INTO sections (subject_id, section_name, school_year, semester) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $sub_id, $sec_name, $sy, $sem);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Section created successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
}
?>