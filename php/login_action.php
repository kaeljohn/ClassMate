<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inst_id = $_POST['instructor_id'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM instructors WHERE instructor_id = '$inst_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify hashed password
        if (password_verify($pass, $row['password'])) {
            $_SESSION['instructor_name'] = $inst_id;
            header("Location: instructor-home.php");
        } else {
            echo "<script>alert('Incorrect Password'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('User not found'); window.history.back();</script>";
    }
}
?>