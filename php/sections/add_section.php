<?php
session_start();
include '../db_connect.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $instructor = $_SESSION['instructor_name'];
    $sectionName = $_POST['sectionName'];
    $syStart = intval($_POST['syStart']);
    $syEnd = intval($_POST['syEnd']);  
    $semester = $_POST['semester'];

    if ($syEnd <= $syStart) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'End school year cannot be less than or equal to the start school year.'
        ]);
        exit();
    }

    if (($syEnd - $syStart) > 1) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'School year range cannot exceed 1 year (e.g., 2026-2027 is valid, 2026-2028 is not).'
        ]);
        exit();
    }

    $checkSql = "SELECT section_id FROM sections WHERE instructor_id = ? AND section_name = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $instructor, $sectionName);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode([
            'status' => 'error', 
            'message' => "Error: Section '$sectionName' already exists in your list."
        ]);
        $checkStmt->close();
        exit();
    }
    $checkStmt->close();

    $sql = "INSERT INTO sections (instructor_id, section_name, sy_start, sy_end, semester) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $instructor, $sectionName, $syStart, $syEnd, $semester);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message'=> 'Section Created Successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
    $conn->close();
}
?>