<?php
header('Content-Type: application/json');
include 'db_connect.php';

if (!isset($_GET['student_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing Student ID']);
    exit();
}

$student_id = mysqli_real_escape_string($conn, $_GET['student_id']);

/**
 * We join 'enrollments' with 'subjects' to get the descriptive data 
 * like subject_name and subject_code.
 */
$query = "SELECT s.subject_id, s.subject_code, s.subject_name, s.sched_day, s.start_time, s.end_time 
          FROM enrollments e
          JOIN subjects s ON e.subject_id = s.subject_id
          WHERE e.student_id = '$student_id'";

$result = $conn->query($query);

$subjects = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Format times for better display in the profile
        $row['start_time'] = date("g:i A", strtotime($row['start_time']));
        $row['end_time'] = date("g:i A", strtotime($row['end_time']));
        $subjects[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $subjects]);
} else {
    // Return empty array instead of error if they just haven't enrolled yet
    echo json_encode(['status' => 'success', 'data' => []]);
}

$conn->close();
?>