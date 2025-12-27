<?php
include 'db_connect.php';

// 1. Get all available subject IDs from your database
$subject_query = $conn->query("SELECT subject_id FROM subjects");
$subject_ids = [];
while($row = $subject_query->fetch_assoc()) {
    $subject_ids[] = $row['subject_id'];
}

if (empty($subject_ids)) {
    die("Error: Please add at least one subject first before seeding students.");
}

// 2. Arrays for random name generation
$first_names = ['John', 'Jane', 'Mark', 'Maria', 'Luke', 'Sophia', 'Chris', 'Elena'];
$last_names = ['Garcia', 'Dela Cruz', 'Reyes', 'Santos', 'Bautista', 'Pascual'];

for ($i = 1; $i <= 60; $i++) {
    $name = $first_names[array_rand($first_names)] . ' ' . $last_names[array_rand($last_names)];
    $s_num = "2024-" . str_pad($i, 5, '0', STR_PAD_LEFT);
    $email = strtolower(str_replace(' ', '.', $name)) . "@cvsu.edu.ph";
    $rand_subject = $subject_ids[array_rand($subject_ids)];

    $conn->query("INSERT INTO students (full_name, student_number, email, enrolled_subject_id) 
                  VALUES ('$name', '$s_num', '$email', '$rand_subject')");
}

echo "60 random students created and enrolled!";
?>