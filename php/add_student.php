<?php
include 'db_connect.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and collect input data
    $section_id = $_POST['section_id'] ?? null;
    $student_id_number = $_POST['studentIdNumber'] ?? '';
    $first_name = $_POST['firstName'] ?? '';
    $last_name = $_POST['lastName'] ?? '';
    $middle_initial = $_POST['middleInitial'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $course = $_POST['course'] ?? '';
    $status = $_POST['status'] ?? 'Regular';

    // Basic Validation
    if (empty($section_id) || empty($student_id_number) || empty($first_name) || empty($last_name) || empty($course)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
        exit;
    }

    // Check if Student ID already exists globally to prevent duplicates
    $check_stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id_number = ?");
    $check_stmt->bind_param("s", $student_id_number);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'A student with this ID Number already exists.']);
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();

    // Prepare SQL to include the new detailed fields
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
    
    // "isssssss" mapping: 
    // i = integer (section_id), 
    // s = string (everything else)
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
            'message' => "Student {$first_name} {$last_name} has been successfully added to the section."
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