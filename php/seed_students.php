<?php
include 'db_connect.php';

$conn->query("TRUNCATE TABLE students");

$last_names = ['Bautista', 'Pascual', 'Santos', 'Dela Cruz', 'Garcia', 'Reyes', 'Aquino', 'Mendoza', 'Torres', 'Lim'];
$first_names = ['Juan', 'Maria', 'John', 'Sophia', 'Luke', 'Mark', 'Elena', 'Chris', 'Jane', 'Antonio'];

$generated_count = 0;
$used_emails = [];

while ($generated_count < 60) {
    $ln = $last_names[array_rand($last_names)];
    $fn = $first_names[array_rand($first_names)];
    // Inside the generator loop:
    $has_mi = (rand(1, 10) > 2); // 80% chance to have an initial
    $mi = $has_mi ? chr(rand(65, 90)) . "." : NULL;

    // Student number logic remains sequential
    $student_num = "2024-" . str_pad($generated_count + 1, 5, '0', STR_PAD_LEFT);

    // Auto-generate Unique Email
    $base_email = strtolower($fn . "." . $ln);
    $temp_email = $base_email . "@cvsu.edu.ph";

    $suffix = 1;
    while (in_array($temp_email, $used_emails)) {
        $temp_email = $base_email . $suffix . "@cvsu.edu.ph";
        $suffix++;
    }

    $used_emails[] = $temp_email;
    $student_num = "2024-" . str_pad($generated_count + 1, 5, '0', STR_PAD_LEFT);

    $sql = "INSERT INTO students (last_name, first_name, middle_name, student_number, email) 
            VALUES ('$ln', '$fn', '$mi', '$student_num', '$temp_email')";

    if ($conn->query($sql)) {
        $generated_count++;
    }
}
echo "Successfully generated 60 students with auto-emails and dotted initials!";
?>