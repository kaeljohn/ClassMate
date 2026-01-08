<?php
header('Content-Type: application/json');
session_start();
include 'db_connect.php';

// 1. Authentication Check: Stop if user is not logged in
if (!isset($_SESSION['instructor_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Identify the user via Session (not POST data, for security)
    $current_id = $_SESSION['instructor_name'];
    
    // 3. Sanitize inputs
    $fname = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lname = mysqli_real_escape_string($conn, $_POST['lastName']);
    $mi    = mysqli_real_escape_string($conn, $_POST['middleInitial']);
    $sex   = mysqli_real_escape_string($conn, $_POST['sex']);

    // 4. Update the record
    $sql = "UPDATE instructors SET 
            first_name = '$fname', 
            last_name = '$lname', 
            middle_initial = '$mi',
            sex = '$sex' 
            WHERE instructor_id = '$current_id'";

    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Profile updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
}
?>
