<?php
include 'db_connect.php';

// 1. Clear existing data to start fresh
$conn->query("TRUNCATE TABLE students");

// Expanded name pools to ensure we can hit 60 unique combinations easily
$last_names = ['Bautista', 'Pascual', 'Santos', 'Dela Cruz', 'Garcia', 'Reyes', 'Aquino', 'Mendoza', 'Torres', 'Lim', 'Gomez', 'Villanueva', 'Sarmiento', 'Castillo', 'Jimenez'];
$first_names = ['Juan', 'Maria', 'John', 'Sophia', 'Luke', 'Mark', 'Elena', 'Chris', 'Jane', 'Antonio', 'Isabella', 'Mateo', 'Gabriel', 'Rosa', 'Paulo', 'Lyca'];

$generated_count = 0;
$used_name_pairs = []; // Tracks only "FirstnameLastname"

echo "<h2>Generating 60 Unique Students (Unique First + Last Name)...</h2>";

while ($generated_count < 60) {
    $ln = $last_names[array_rand($last_names)];
    $fn = $first_names[array_rand($first_names)];
    
    // Create a unique key using only First and Last Name
    $namePairKey = strtolower($fn . $ln);

    // 2. Check if this First + Last combination has been used yet
    if (!in_array($namePairKey, $used_name_pairs)) {
        $used_name_pairs[] = $namePairKey;
        
        // Randomly decide if they have a Middle Initial (80% chance)
        $has_mi = (rand(1, 10) > 2);
        $mi_db = $has_mi ? chr(rand(65, 90)) . "." : " ";

        // Sequential Student Number
        $student_num = "2024-" . str_pad($generated_count + 1, 5, '0', STR_PAD_LEFT);
        
        // Auto-Email based on unique names
        $email = strtolower(str_replace(' ', '', $fn) . "." . str_replace(' ', '', $ln) . "@cvsu.edu.ph");

        // Use Prepared Statement for safe insertion with NULLs
        $stmt = $conn->prepare("INSERT INTO students (last_name, first_name, middle_name, student_number, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $ln, $fn, $mi_db, $student_num, $email);
        
        if ($stmt->execute()) {
            $generated_count++;
        }
    }
}

echo "Successfully generated $generated_count students with unique First/Last name combinations!";
?>