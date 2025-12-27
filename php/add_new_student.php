<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $mi = trim($_POST['middle_name']);
    
    // 1. Handle Middle Initial (NULL if empty, add dot if present)
    $middle_name_final = !empty($mi) ? strtoupper($mi) . "." : " ";
    
    // 2. Generate Automatic Incrementing Student Number
    // Fetches the latest ID to create the next number
    $res = $conn->query("SELECT MAX(id) as max_id FROM students");
    $row = $res->fetch_assoc();
    $next_id = $row['max_id'] + 1;
    $student_number = "2024-" . str_pad($next_id, 5, '0', STR_PAD_LEFT);
    
    // 3. Generate Automatic Unique Email
    $base_email = strtolower(str_replace(' ', '', $first_name) . "." . str_replace(' ', '', $last_name));
    $email_domain = "@cvsu.edu.ph";
    $final_email = $base_email . $email_domain;

    $check_email = $conn->query("SELECT email FROM students WHERE email = '$final_email'");
    if ($check_email->num_rows > 0) {
        $final_email = $base_email . $next_id . $email_domain;
    }

    // 4. Prepared Statement for NULL handling
    $stmt = $conn->prepare("INSERT INTO students (last_name, first_name, middle_name, student_number, email) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $last_name, $first_name, $middle_name_final, $student_number, $final_email);

    if ($stmt->execute()) {
        header("Location: ../instructor-home.php?status=success");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>