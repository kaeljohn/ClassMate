<?php
header('Content-Type: application/json');
session_start();
include '../db_connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // 1. Delete the subject record
    $stmt = $conn->prepare("DELETE FROM subjects WHERE subject_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete: ' . $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No ID provided.']);
}
?>
