<?php
session_start();
include '../db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['instructor_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$student_id = intval($_POST['student_id'] ?? 0);
$section_id = intval($_POST['section_id'] ?? 0);
$subject_id = intval($_POST['subject_id'] ?? 0);
$type       = trim($_POST['type'] ?? '');
$score      = isset($_POST['score']) ? floatval($_POST['score']) : null;
$instructor = $_SESSION['instructor_name'];

if (!$student_id || !$section_id || !$subject_id || !$type || $score === null) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit();
}

$check = $conn->prepare(
    "SELECT id FROM student_grades
     WHERE student_id = ?
       AND section_id = ?
       AND subject_id = ?
       AND assessment_type = ?
       AND instructor_id = ?"
);
$check->bind_param(
    "iiiss",
    $student_id,
    $section_id,
    $subject_id,
    $type,
    $instructor
);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {

    $update = $conn->prepare(
        "UPDATE student_grades
         SET score = ?
         WHERE student_id = ?
           AND section_id = ?
           AND subject_id = ?
           AND assessment_type = ?
           AND instructor_id = ?"
    );

    $update->bind_param(
        "diiiss",
        $score,
        $student_id,
        $section_id,
        $subject_id,
        $type,
        $instructor
    );

    $ok = $update->execute();
    $update->close();

} else {

    $insert = $conn->prepare(
        "INSERT INTO student_grades
            (student_id, section_id, subject_id, instructor_id, assessment_type, score)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    $insert->bind_param(
        "iiissd",
        $student_id,
        $section_id,
        $subject_id,
        $instructor,
        $type,
        $score
    );

    $ok = $insert->execute();
    $insert->close();
}

$check->close();

echo json_encode([
    'status' => $ok ? 'success' : 'error',
    'message' => $ok ? 'Grade saved' : $conn->error
]);

$conn->close();
