<?php
header('Content-Type: application/json');
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['instructor_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and collect inputs
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $student_id_number = mysqli_real_escape_string($conn, $_POST['studentIdNumber']);
    $first_name = mysqli_real_escape_string($conn, $_POST['firstName']);
    $last_name = mysqli_real_escape_string($conn, $_POST['lastName']);
    $sex = mysqli_real_escape_string($conn, $_POST['sex']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);

    // Check if ID Number is already taken by another student
    $check_sql = "SELECT student_id FROM students WHERE student_id_number = '$student_id_number' AND student_id != '$student_id'";
    $check_res = $conn->query($check_sql);

    if ($check_res && $check_res->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'The Student ID Number is already assigned to someone else.']);
        exit();
    }

    // Perform Update
    $update_sql = "UPDATE students SET 
                    student_id_number = '$student_id_number',
                    first_name = '$first_name',
                    last_name = '$last_name',
                    sex = '$sex',
                    status = '$status',
                    course = '$course'
                   WHERE student_id = '$student_id'";

    if ($conn->query($update_sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Student profile updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update database: ' . $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>