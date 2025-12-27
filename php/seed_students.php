<?php
include 'php/db_connect.php';

// 1. Clear existing data
$conn->query("TRUNCATE TABLE students");

$last_names = ['Bautista', 'Pascual', 'Santos', 'Dela Cruz', 'Garcia', 'Reyes', 'Aquino', 'Mendoza', 'Torres', 'Lim'];
$first_names = ['Juan', 'Maria', 'John', 'Sophia', 'Luke', 'Mark', 'Elena', 'Chris', 'Jane', 'Antonio', 'Isabella', 'Mateo'];
$middle_names = ['Pineda', 'Soriano', 'Villanueva', 'Dizon', 'Mercado', 'Ramos'];

$generated_count = 0;
$used_full_names = [];

echo "<h2>Generating 60 Unique Students...</h2>";

while ($generated_count < 60) {
    $ln = $last_names[array_rand($last_names)];
    $fn = $first_names[array_rand($first_names)];
    $mn = $middle_names[array_rand($middle_names)];
    
    $fullNameKey = "$ln $fn $mn";

    // Ensure uniqueness
    if (!in_array($fullNameKey, $used_full_names)) {
        $used_full_names[] = $fullNameKey;
        
        $student_num = "2024-" . str_pad($generated_count + 1, 5, '0', STR_PAD_LEFT);
        $email = strtolower($fn . "." . $ln . $generated_count . "@cvsu.edu.ph");

        $sql = "INSERT INTO students (last_name, first_name, middle_name, student_number, email) 
                VALUES ('$ln', '$fn', '$mn', '$student_num', '$email')";
        
        if ($conn->query($sql)) {
            $generated_count++;
        }
    }
}

echo "Successfully generated $generated_count unique students!";
?>