<?php
header('Content-Type: application/json');
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Validate strictly numerical ID immediately
    if (!isset($_POST['instructor_id']) || !ctype_digit($_POST['instructor_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Instructor ID must be strictly numerical.']);
        exit();
    }

    $inst_id = mysqli_real_escape_string($conn, $_POST['instructor_id']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM instructors WHERE instructor_id = '$inst_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['instructor_name'] = $inst_id;
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid password.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Instructor ID not found.']);
    }
}
?>