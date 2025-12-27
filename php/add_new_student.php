<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $mi = trim($_POST['middle_name']);

    // 1. DUPLICATE CHECK
    // We check if a student with the exact same First and Last name already exists
    $check_stmt = $conn->prepare("SELECT id FROM students WHERE last_name = ? AND first_name = ?");
    $check_stmt->bind_param("ss", $last_name, $first_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'exists']);
        exit();
    }

    // 2. Handle Middle Initial (NULL if empty, add dot if present)
    $middle_name_final = !empty($mi) ? strtoupper($mi) . "." : " ";

    // 3. Generate Automatic Incrementing Student Number
    $res = $conn->query("SELECT MAX(id) as max_id FROM students");
    $row = $res->fetch_assoc();
    $next_id = ($row['max_id'] ?? 0) + 1;
    $student_number = "2024-" . str_pad($next_id, 5, '0', STR_PAD_LEFT);

    // 4. Generate Automatic Unique Email
    $base_email = strtolower(str_replace(' ', '', $first_name) . "." . str_replace(' ', '', $last_name));
    $final_email = $base_email . "@cvsu.edu.ph";

    // Double check email uniqueness (in case of very similar names)
    $email_check = $conn->prepare("SELECT id FROM students WHERE email = ?");
    $email_check->bind_param("s", $final_email);
    $email_check->execute();
    if ($email_check->get_result()->num_rows > 0) {
        $final_email = $base_email . $next_id . "@cvsu.edu.ph";
    }

    // 5. Final Insertion
    $stmt = $conn->prepare("INSERT INTO students (last_name, first_name, middle_name, student_number, email) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $last_name, $first_name, $middle_name_final, $student_number, $final_email);

    if ($stmt->execute()) {
        header("Location: ../instructor-home.php?status=success");
    } else {
        header("Location: ../instructor-home.php?status=error");
    }
    exit();
}
?>