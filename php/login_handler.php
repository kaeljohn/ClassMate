<?php
// login_handler.php
session_start();
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inst_id = $_POST['instructor_id'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT password FROM instructors WHERE instructor_id = ?");
    $stmt->bind_param("s", $inst_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Verify password against the hash in the DB
        if (password_verify($pass, $user['password'])) {
            $_SESSION['instructor_id'] = $inst_id;
            header("Location: instructor-home.html");
            exit();
        } else {
            echo "<script>alert('Invalid Password'); window.location.href='instructor-login.html';</script>";
        }
    } else {
        echo "<script>alert('Instructor ID not found'); window.location.href='instructor-login.html';</script>";
    }
    $stmt->close();
    $conn->close();
}
?>