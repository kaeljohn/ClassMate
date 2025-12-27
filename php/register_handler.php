<?php
// register_handler.php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inst_id = $_POST['instructor_id'];
    $pass = $_POST['password'];

    // Securely hash the password
    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

    // Prepare and bind to prevent SQL Injection
    $stmt = $conn->prepare("INSERT INTO instructors (instructor_id, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $inst_id, $hashed_pass);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful!'); window.location.href='instructor-login.html';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>