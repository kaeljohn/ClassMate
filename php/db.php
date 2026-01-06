<?php
$conn = new mysqli("localhost", "root", "", "classmate_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>