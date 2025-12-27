<?php
header('Content-Type: application/json'); // Tell the browser we are sending JSON
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $mi = trim($_POST['middle_name']);
    
    // 1. DUPLICATE CHECK
    $check_stmt = $conn->prepare("SELECT id FROM students WHERE last_name = ? AND first_name = ?");
    $check_stmt->bind_param("ss", $last_name, $first_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(['status' => 'exists', 'message' => 'Student already exists!']);
        exit();
    }

    // 2. Formatting
    $middle_name_final = !empty($mi) ? strtoupper($mi) . "." : null;
    
    // 3. Auto-increment Student Number logic
    $res = $conn->query("SELECT MAX(id) as max_id FROM students");
    $row = $res->fetch_assoc();
    $next_id = ($row['max_id'] ?? 0) + 1;
    $student_number = "2024-" . str_pad($next_id, 5, '0', STR_PAD_LEFT);
    
    // 4. Auto-generate Email
    $base_email = strtolower(str_replace(' ', '', $first_name) . "." . str_replace(' ', '', $last_name));
    $final_email = $base_email . "@cvsu.edu.ph";

    // 5. Insert
    $stmt = $conn->prepare("INSERT INTO students (last_name, first_name, middle_name, student_number, email) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $last_name, $first_name, $middle_name_final, $student_number, $final_email);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
}
?>