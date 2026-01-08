<?php
header('Content-Type: application/json');
include '../db_connect.php';

$section_id = $_GET['section_id'] ?? null;
$subject_id = $_GET['subject_id'] ?? null;

if ($section_id) {
    if ($subject_id) {
        // 1. Fetch students enrolled in specific Subject AND Section
        $sql = "SELECT s.* FROM students s
                INNER JOIN enrollments e ON s.student_id = e.student_id
                WHERE s.section_id = ? AND e.subject_id = ?
                ORDER BY s.last_name ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $section_id, $subject_id);
    } else {
        // 2. Fetch all students in Section (regardless of enrollment)
        $sql = "SELECT * FROM students WHERE section_id = ? ORDER BY last_name ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $section_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing section_id']);
}
?>
