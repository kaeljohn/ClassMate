<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['instructor_id'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT password FROM instructors WHERE instructor_id = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['password'])) {
            $_SESSION['instructor'] = $user;
            header("Location: instructor-home.html");
        } else {
            echo "<script>alert('Wrong password'); history.back();</script>";
        }
    } else {
        echo "<script>alert('User not found'); history.back();</script>";
    }
}
?>