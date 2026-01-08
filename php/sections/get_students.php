<?php
session_start();
include '../db_connect.php';

header('Content-Type: application/json');

if (isset($_GET['section_id'])) {
    $sectionId = (int)$_GET['section_id'];

    // 1. Select all students in the requested section
    $sql = "SELECT * FROM students WHERE section_id = ? ORDER BY last_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sectionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // 2. Fetch rows into an array
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    
    // 3. Return data as JSON
    echo json_encode(['status' => 'success', 'data' => $students]);
    $stmt->close();
}
$conn->close();
?>
