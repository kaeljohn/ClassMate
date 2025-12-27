<?php
/**
 * add_new_student.php
 * Handles AJAX registration for new students with:
 * 1. Duplicate name checking
 * 2. Automatic ID generation (2024-0000X)
 * 3. Automatic unique email generation
 * 4. JSON responses for Modal feedback
 */

// Set header to JSON so JavaScript can parse the response
header('Content-Type: application/json');

// Database connection
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and capture inputs
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $mi = trim($_POST['middle_name']);
    
    // 1. DUPLICATE CHECK
    // Check if a student with the exact same Last and First name exists
    $check_stmt = $conn->prepare("SELECT id FROM students WHERE last_name = ? AND first_name = ?");
    $check_stmt->bind_param("ss", $last_name, $first_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Stop and inform the JavaScript that student already exists
        echo json_encode(['status' => 'exists', 'message' => 'Student already exists!']);
        exit();
    }

    // 2. FORMAT MIDDLE INITIAL
    // Only add the dot if the user provided an initial
    $middle_name_final = !empty($mi) ? strtoupper($mi) . "." : null;
    
    // 3. GENERATE AUTOMATIC STUDENT NUMBER
    // Get the current max ID to create the next sequential number
    $res = $conn->query("SELECT MAX(id) as max_id FROM students");
    $row = $res->fetch_assoc();
    $next_id = ($row['max_id'] ?? 0) + 1;
    $student_number = "2024-" . str_pad($next_id, 5, '0', STR_PAD_LEFT);
    
    // 4. GENERATE AUTOMATIC EMAIL
    // Format: firstname.lastname@cvsu.edu.ph
    $clean_fn = strtolower(str_replace(' ', '', $first_name));
    $clean_ln = strtolower(str_replace(' ', '', $last_name));
    $base_email = $clean_fn . "." . $clean_ln;
    $final_email = $base_email . "@cvsu.edu.ph";

    // Double-check if email exists (in case of different people with same name components)
    $email_check = $conn->prepare("SELECT id FROM students WHERE email = ?");
    $email_check->bind_param("s", $final_email);
    $email_check->execute();
    if ($email_check->get_result()->num_rows > 0) {
        // If email exists, append the ID to make it unique
        $final_email = $base_email . $next_id . "@cvsu.edu.ph";
    }

    // 5. INSERT INTO DATABASE
    // Using Prepared Statements to prevent SQL Injection
    $stmt = $conn->prepare("INSERT INTO students (last_name, first_name, middle_name, student_number, email) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $last_name, $first_name, $middle_name_final, $student_number, $final_email);

    if ($stmt->execute()) {
        // Return success to the AJAX caller
        echo json_encode([
            'status' => 'success', 
            'student_number' => $student_number,
            'email' => $final_email
        ]);
    } else {
        // Return database error
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
} else {
    // If someone tries to access this file directly via URL
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>