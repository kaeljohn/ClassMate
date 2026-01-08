<?php
header('Content-Type: application/json'); // Set response to JSON
include 'db_connect.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Validate that Instructor ID exists and is numeric
    if (!isset($_POST['instructor_id']) || !ctype_digit($_POST['instructor_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Instructor ID must be strictly numerical.']);
        exit();
    }

    $inst_id = mysqli_real_escape_string($conn, $_POST['instructor_id']);
    
    // 2. Check if the Instructor ID is already taken
    $check = $conn->query("SELECT instructor_id FROM instructors WHERE instructor_id = '$inst_id'");
    if($check->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username/ID is already taken.']);
        exit();
    }
    
    // 3. Sanitize personal information inputs
    $fname = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lname = mysqli_real_escape_string($conn, $_POST['lastName']);
    $mi    = mysqli_real_escape_string($conn, $_POST['middleInitial']);
    $sex   = mysqli_real_escape_string($conn, $_POST['sex']);

    // 4. Securely hash the password (never store plain text passwords)
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 5. Insert the new instructor into the database
    $sql = "INSERT INTO instructors (instructor_id, password, first_name, last_name, middle_initial, sex) 
            VALUES ('$inst_id', '$pass', '$fname', '$lname', '$mi', '$sex')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success', 'message' => 'Account created successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
}
?>
