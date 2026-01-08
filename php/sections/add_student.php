<?php
session_start();
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Security Check: Ensure user is logged in
    if (!isset($_SESSION['instructor_name'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
        exit;
    }

    $instructor_id = $_SESSION['instructor_name'];
    
    // 2. Retrieve inputs using null coalescing operator for safety
    $section_id = $_POST['section_id'] ?? null;
    $student_id_number = $_POST['studentIdNumber'] ?? '';
    $first_name = $_POST['firstName'] ?? '';
    $last_name = $_POST['lastName'] ?? '';
    $middle_initial = isset($_POST['middleInitial']) ? trim($_POST['middleInitial']) : '';
    $sex = $_POST['sex'] ?? '';
    $course = $_POST['course'] ?? '';
    $status = $_POST['status'] ?? 'Regular';

    // 3. Validate required fields
    if (empty($section_id) || empty($student_id_number) || empty($first_name) || empty($last_name) || empty($course)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
        exit;
    }

    // 4. Duplicate Check: Prevent adding the same Student ID twice under the same instructor
    $check_sql = "SELECT s.student_id 
                  FROM students s
                  INNER JOIN sections sec ON s.section_id = sec.section_id
                  WHERE s.student_id_number = ? AND sec.instructor_id = ?";
                  
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $student_id_number, $instructor_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'This Student ID Number is already registered in one of your sections.']);
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();

    // 5. Insert the new student
    $sql = "INSERT INTO students (
                section_id, 
                student_id_number, 
                first_name, 
                last_name, 
                middle_initial, 
                sex, 
                course, 
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    
    $stmt->bind_param(
        "isssssss", 
        $section_id, 
        $student_id_number, 
        $first_name, 
        $last_name, 
        $middle_initial, 
        $sex, 
        $course, 
        $status
    );

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success', 
            'message' => "Student {$first_name} {$last_name} has been successfully added."
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Database execution failed: ' . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
