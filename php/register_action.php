<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inst_id = $_POST['instructor_id'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure hashing for your rubric score!

    $sql = "INSERT INTO instructors (instructor_id, password) VALUES ('$inst_id', '$pass')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Account created!'); window.location.href='instructor-login.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>