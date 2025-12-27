<?php
session_start();
include 'db_connect.php';

if (isset($_GET['id']) && isset($_SESSION['instructor_name'])) {
    $id = $_GET['id'];
    $inst = $_SESSION['instructor_name'];

    // Security check: only delete if the subject belongs to this instructor
    $stmt = $conn->prepare("DELETE FROM subjects WHERE subject_id = ? AND instructor_id = ?");
    $stmt->bind_param("is", $id, $inst);

    if ($stmt->execute()) {
        header("Location: ../instructor-home.php?deleted=1");
    } else {
        echo "Error deleting record: " . $conn->error;
    }
    $stmt->close();
}
?>