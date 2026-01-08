<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "classmate_db";

// 1. Ensure an instructor is logged in to assign subjects to
if (!isset($_SESSION['instructor_name'])) {
   die("No instructor logged in.");
}

$currentInstructorId = intval($_SESSION['instructor_name']);

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Hardcoded list of subjects
$subjects = [
    ["GNED 12", "DALUMAT NG / SA FILIPINO"],
    ["GNED 03", "MATHEMATICS IN THE MODERN WORLD"],
    ["NSTP 2", "NATIONAL SERVICE TRAINING PROGRAM 2"],
    ["FITT 2", "FITNESS EXERCISES"],
    ["GNED 01", "ART APPRECIATION"],
    ["DCIT 23", "COMPUTER PROGRAMMING II"],
    ["GNED 06", "SCIENCE, TECHNOLOGY AND SOCIETY"],
    ["ITEC 50B", "WEB SYSTEMS AND TECHNOLOGIES"]
];

$days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
$timeSlots = [
    ["08:00:00", "09:30:00"],
    ["09:30:00", "11:00:00"],
    ["11:00:00", "12:30:00"],
    ["13:00:00", "14:30:00"],
    ["14:30:00", "16:00:00"],
    ["16:00:00", "17:30:00"]
];

// 2. Find the last used schedule code to increment from
$res = $conn->query("
    SELECT sched_code 
    FROM subjects 
    WHERE sched_code IS NOT NULL
    ORDER BY sched_code DESC 
    LIMIT 1
");

$schedCode = ($row = $res->fetch_assoc())
    ? intval($row['sched_code']) + 1
    : 202600001;


$conn->begin_transaction();

try {

    $stmt = $conn->prepare("
        INSERT INTO subjects
        (instructor_id, sched_code, subject_code, subject_name, sched_day, start_time, end_time)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($subjects as [$code, $name]) {
        // 3. Pick random day and time slot
        $day = $days[array_rand($days)];
        [$start, $end] = $timeSlots[array_rand($timeSlots)];

        $currentSchedCode = $schedCode;

        // 4. Insert subject linked to current instructor
        $stmt->bind_param(
            "iisssss",
            $currentInstructorId,
            $currentSchedCode,
            $code,
            $name,
            $day,
            $start,
            $end
        );

        $stmt->execute();
        $schedCode++;
    }

    $stmt->close();
    $conn->commit();

    echo "Subjects seeded and assigned to instructor ID {$currentInstructorId}.";

} catch (Exception $e) {
    $conn->rollback();
    die("Error: " . $e->getMessage());
}

$conn->close();
?>
