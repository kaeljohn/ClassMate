<?php
include 'db_connect.php';
$section_id = $_GET['section_id'];

$sql = "SELECT s.*, sa.assignment_id 
        FROM students s 
        JOIN section_assignments sa ON s.student_id = sa.student_id 
        WHERE sa.section_id = '$section_id'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['student_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['last_name'] . ", " . $row['first_name']) . "</td>";
        echo "<td><button class='btn btn-sm btn-danger'>Remove</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='3' style='text-align:center;'>No students enrolled in this section.</td></tr>";
}
?>