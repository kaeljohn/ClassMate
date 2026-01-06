<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "classmate_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* =========================
   CONFIG
   ========================= */
$weeks = 18;

// Attendance weights (single-letter codes)
$attendanceWeights = [
    'P' => 70, // Present
    'L' => 15, // Late
    'A' => 10, // Absent
    'E' => 5   // Excused
];

$assessments = array_merge(
    array_map(fn($i) => "Quiz$i", range(1, 10)),
    ["Midterm", "Finals"]
);


function weightedRandom(array $weights) {
    $total = array_sum($weights);
    $rand = rand(1, $total);
    foreach ($weights as $key => $weight) {
        if (($rand -= $weight) <= 0) {
            return $key;
        }
    }
    return array_key_first($weights);
}


$sections = [];
$res = $conn->query("SELECT section_id FROM sections");
while ($row = $res->fetch_assoc()) {
    $sections[] = $row['section_id'];
}
if (empty($sections)) {
    die("No sections found.");
}


$conn->begin_transaction();

try {

    foreach ($sections as $section_id) {

        $students = [];
        $res = $conn->query("
            SELECT student_id 
            FROM students 
            WHERE section_id = $section_id
        ");
        while ($r = $res->fetch_assoc()) {
            $students[] = $r['student_id'];
        }
        if (empty($students)) continue;

        $subjects = [];
        $res = $conn->query("
            SELECT subject_id, instructor_id
            FROM subjects
        ");
        while ($r = $res->fetch_assoc()) {
            $subjects[$r['subject_id']] = $r['instructor_id'];
        }
        if (empty($subjects)) continue;

        $attStmt = $conn->prepare("
            INSERT IGNORE INTO attendance_records
            (student_id, section_id, subject_id, instructor_id, week_number, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($students as $student_id) {
            foreach ($subjects as $subject_id => $instructor_id) {
                for ($week = 1; $week <= $weeks; $week++) {
                    $status = weightedRandom($attendanceWeights);
                    $attStmt->bind_param(
                        "iiiiis",
                        $student_id,
                        $section_id,
                        $subject_id,
                        $instructor_id,
                        $week,
                        $status
                    );
                    $attStmt->execute();
                }
            }
        }
        $attStmt->close();

        $gradeStmt = $conn->prepare("
            INSERT IGNORE INTO student_grades
            (student_id, section_id, subject_id, instructor_id, assessment_type, score)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($students as $student_id) {
            foreach ($subjects as $subject_id => $instructor_id) {
                foreach ($assessments as $type) {

                    $score = rand(70, 100);

                    $gradeStmt->bind_param(
                        "iiiisd",
                        $student_id,
                        $section_id,
                        $subject_id,
                        $instructor_id,
                        $type,
                        $score
                    );
                    $gradeStmt->execute();
                }
            }
        }
        $gradeStmt->close();
    }

    $conn->commit();
    echo "Attendance (18 weeks) and grades generated successfully.";

} catch (Exception $e) {
    $conn->rollback();
    die("Error: " . $e->getMessage());
}

$conn->close();
?>
