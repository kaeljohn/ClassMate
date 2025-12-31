<?php
$host = "localhost";
$user = "root";
$pass = ""; // Default WAMP password is empty
$db   = "classmate_db";

$conn = new mysqli($host, $user, $pass, $db);

// Check if connection works
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>