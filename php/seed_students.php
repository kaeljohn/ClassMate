<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "classmate_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Arrays of sample names, courses, etc. used for random generation
$firstNames = [
    "Aaron","Abigail","Adrian","Aileen","Albert","Alex","Alyssa","Andres","Angela","Antonio",
    "Bea","Benjamin","Bernard","Bianca","Bryan",
    "Carla","Carlos","Catherine","Christian","Christine",
    "Daniel","Danica","David","Diana","Dominic",
    "Eduardo","Elaine","Emmanuel","Erika","Esteban",
    "Felix","Francis","Franco","Fiona","Faith",
    "Gabriel","Grace","Gian","Glenn","Gretchen",
    "Hannah","Harold","Hazel","Henry","Hope",
    "Ian","Irene","Isabel","Ivan",
    "Jasmine","Jasper","Jean","Jerome","Joanna","John","Joseph","Joshua","Joyce",
    "Karl","Karen","Keith","Kristine",
    "Lance","Laura","Leo","Lea","Luis","Liza",
    "Marco","Maria","Mark","Marvin","Matthew","Megan","Michael","Miguel",
    "Nathan","Nicole","Noel","Nina",
    "Oliver","Olivia","Oscar",
    "Paolo","Patricia","Patrick","Paul","Peter","Princess",
    "Queenie",
    "Rafael","Reina","Richard","Rizal","Roxanne",
    "Samuel","Sandra","Sofia","Stephen","Sean",
    "Theresa","Thomas","Trisha",
    "Ulysses",
    "Vanessa","Victor","Vincent",
    "Warren","Wilfred","William",
    "Xander",
    "Yvette","Yvonne",
    "Zachary","Zara"
];

$lastNames = [
    "Abad","Aguilar","Alcantara","Alonzo","Aquino",
    "Bautista","Benitez","Bernardo","Blanco","Borja",
    "Castillo","Cruz","Calderon","Camacho","Cortez",
    "Dela Cruz","Domingo","Del Rosario","Diaz","Durano",
    "Espiritu","Estrada","Evangelista",
    "Flores","Fernandez","Francisco","Fuentes",
    "Garcia","Gomez","Gonzales","Guerrero",
    "Hernandez","Hilario","Herrera",
    "Ibarra","Ignacio",
    "Jimenez","Juarez",
    "Labrador","Lazaro","Lopez","Luna",
    "Magsaysay","Marquez","Martinez","Mendoza","Morales",
    "Navarro","Nolasco",
    "Ortega","Ocampo",
    "Pascual","Perez","Pineda","Reyes","Rivera",
    "Ramos","Robles","Rosales","Roxas",
    "Santos","San Juan","Soriano","Suarez",
    "Tan","Torres","Tolentino",
    "Uy",
    "Valdez","Villanueva","Villar",
    "Yap",
    "Zamora"
];

$middleInitials = range('A', 'Z');

$courses = [
    // Graduate School
    "PhD in Agriculture","PhD in Education","PhD in Management",
    "Master in Business Administration","Master of Agriculture",
    "Master of Arts in Education","Master of Engineering",
    "Master of Management","Master of Professional Studies",
    "MS Agriculture","MS Biology","MS Food Science",
    "Master in Information Technology",

    // Undergraduate
    "Bachelor of Agricultural Entrepreneurship","BS Agriculture","BS Environmental Science","BS Food Technology",
    "BA English Language Studies","BA Journalism","BA Political Science","BS Applied Mathematics",
    "BS Biology","BS Psychology","BS Social Work",
    "BS Criminology","BS Industrial Security Management",
    "BS Accountancy","BS Business Management","BS Development Management",
    "BS Economics","BS International Studies","BS Office Administration",
    "Bachelor of Early Childhood Education","Bachelor of Elementary Education",
    "Bachelor of Secondary Education","Bachelor of Special Needs Education",
    "Bachelor of Technology and Livelihood Education","BS Hospitality Management",
    "BS Tourism Management","Teacher Certificate Program",
    "BS Agricultural and Biosystems Engineering","BS Architecture","BS Civil Engineering",
    "BS Computer Engineering","BS Computer Science","BS Electrical Engineering",
    "BS Electronics Engineering","BS Industrial Engineering",
    "BS Information Technology",
    "BS Medical Technology","BS Midwifery","BS Nursing","Diploma in Midwifery",
    "Bachelor of Physical Education","Bachelor of Exercise and Sports Sciences",
    "Doctor of Veterinary Medicine"
];

$sexes = ["Male", "Female"];

// 1. Fetch existing sections and subjects to link students to
$sections = [];
$res = $conn->query("SELECT section_id FROM sections");
while ($row = $res->fetch_assoc()) {
    $sections[] = $row['section_id'];
}

$subjects = [];
$res = $conn->query("SELECT subject_id FROM subjects");
while ($row = $res->fetch_assoc()) {
    $subjects[] = $row['subject_id'];
}

// 2. Determine the next Student ID Number based on the current year
$year = date('Y');
$res = $conn->query("
    SELECT student_id_number
    FROM students
    WHERE student_id_number LIKE '{$year}%'
    ORDER BY student_id_number DESC
    LIMIT 1
");
$counter = ($row = $res->fetch_assoc())
    ? intval(substr($row['student_id_number'], 4)) + 1
    : 1;

$conn->begin_transaction();

try {

    foreach ($sections as $section_id) {
        // 3. Create 40 students per section
        for ($i = 0; $i < 40; $i++) {
            // Randomize attributes
            $first  = $firstNames[array_rand($firstNames)];
            $last   = $lastNames[array_rand($lastNames)];
            $middle = (rand(1,100) <= 20) ? $middleInitials[array_rand($middleInitials)] : null;
            $sex    = $sexes[array_rand($sexes)];
            $course = $courses[array_rand($courses)];

            $studentNumber = $year . str_pad($counter++, 5, "0", STR_PAD_LEFT);

            // 4. Insert Student
            $stmt = $conn->prepare("
                INSERT INTO students
                (section_id, student_id_number, first_name, last_name, middle_initial, sex, course, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')
            ");

            $stmt->bind_param(
                "issssss",
                $section_id,
                $studentNumber,
                $first,
                $last,
                $middle,
                $sex,
                $course
            );

            $stmt->execute();
            $student_id = $stmt->insert_id;
            $stmt->close();

            // 5. Enroll student in all available subjects
            $enroll = $conn->prepare("
                INSERT IGNORE INTO enrollments (student_id, subject_id)
                VALUES (?, ?)
            ");
            foreach ($subjects as $subject_id) {
                $enroll->bind_param("ii", $student_id, $subject_id);
                $enroll->execute();
            }
            $enroll->close();
        }
    }

    $conn->commit();
    echo "Filipino students seeded successfully with courses & sex.";

} catch (Exception $e) {
    $conn->rollback();
    die("Error: " . $e->getMessage());
}

$conn->close();
?>
