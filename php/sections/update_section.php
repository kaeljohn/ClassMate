<?php
session_start();
include '../db_connect.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sectionId = $_POST['section_id'];
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
            'message' => 'School year range cannot exceed 1 year.'
        ]);
        exit();
    }

    $instructor = $_SESSION['instructor_name'];
    $checkSql = "SELECT section_id FROM sections WHERE instructor_id = ? AND section_name = ? AND section_id != ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ssi", $instructor, $sectionName, $sectionId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode([
            'status' => 'error', 
            'message' => "Error: Another section with the name '$sectionName' already exists."
        ]);
        $checkStmt->close();
        exit();
    }
    $checkStmt->close();

    $sql = "UPDATE sections SET 
            section_name = ?, 
            sy_start = ?, 
            sy_end = ?, 
            semester = ? 
            WHERE section_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siisi", $sectionName, $syStart, $syEnd, $semester, $sectionId);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message'=> 'Section Updated Successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
    $conn->close();
}
?>