<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $mi = trim($_POST['middle_name']);
    
    // 1. Automatically add the dot to the initial
    $middle_name_with_dot = !empty($mi) ? strtoupper($mi) . "." : "";
    
    // 2. Generate Automatic Unique Email
    // Format: firstname.lastname@cvsu.edu.ph
    $base_email = strtolower(str_replace(' ', '', $first_name) . "." . str_replace(' ', '', $last_name));
    $email_domain = "@cvsu.edu.ph";
    $final_email = $base_email . $email_domain;

    // Check for existing email to ensure uniqueness
    $check_email = $conn->query("SELECT email FROM students WHERE email LIKE '$base_email%'");
    $count = $check_email->num_rows;
    if ($count > 0) {
        $final_email = $base_email . $count . $email_domain;
    }

    $student_number = $_POST['student_number'];

    $sql = "INSERT INTO students (last_name, first_name, middle_name, student_number, email) 
            VALUES ('$last_name', '$first_name', '$middle_name_with_dot', '$student_number', '$final_email')";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../instructor-home.php?status=success");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>