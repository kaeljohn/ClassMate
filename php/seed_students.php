<?php
include 'db_connect.php';

// 1. Clear existing data
$conn->query("TRUNCATE TABLE students");

$last_names = ['Bautista', 'Pascual', 'Santos', 'Dela Cruz', 'Garcia', 'Reyes', 'Aquino', 'Mendoza', 'Torres', 'Lim', 'Gomez', 'Villanueva'];
$first_names = ['Juan', 'Maria', 'John', 'Sophia', 'Luke', 'Mark', 'Elena', 'Chris', 'Jane', 'Antonio', 'Isabella', 'Mateo', 'Gabriel', 'Rosa'];

$generated_count = 0;
$used_full_names = [];

echo "<h2>Generating 60 Unique Students with Initials...</h2>";

while ($generated_count < 60) {
    $ln = $last_names[array_rand($last_names)];
    $fn = $first_names[array_rand($first_names)];
    
    // Generate a random letter A-Z for the initial
    $initial = chr(rand(65, 90)) . ".";
    
    // Unique check based on Last, First, and Initial
    $fullNameKey = "$ln $fn $initial";

    if (!in_array($fullNameKey, $used_full_names)) {
        $used_full_names[] = $fullNameKey;
        
        $student_num = "2024-" . str_pad($generated_count + 1, 5, '0', STR_PAD_LEFT);
        $email = strtolower($fn . "." . $ln . $generated_count . "@cvsu.edu.ph");

        // Insert into the new 'id' and 'middle_name' columns
        $sql = "INSERT INTO students (last_name, first_name, middle_name, student_number, email) 
                VALUES ('$ln', '$fn', '$initial', '$student_num', '$email')";
        
        if ($conn->query($sql)) {
            $generated_count++;
        }
    }
}

echo "Successfully generated $generated_count unique students!";
?>