<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "classmate_db";

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`");
$conn->select_db($dbname);

$sql_instructors = "CREATE TABLE IF NOT EXISTS instructors (
    instructor_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    middle_initial VARCHAR(10),
    sex VARCHAR(20),
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB";

$sql_sections = "CREATE TABLE IF NOT EXISTS sections (
    section_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instructor_id INT UNSIGNED NULL,
    section_name VARCHAR(100) NOT NULL,
    sy_start YEAR,
    sy_end YEAR,
    semester VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sections_instructor
        FOREIGN KEY (instructor_id)
        REFERENCES instructors(instructor_id)
        ON DELETE SET NULL
) ENGINE=InnoDB";

$sql_subjects = "CREATE TABLE IF NOT EXISTS subjects (
    subject_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instructor_id INT UNSIGNED NULL,
    sched_code VARCHAR(50),
    subject_code VARCHAR(50) NOT NULL,
    subject_name VARCHAR(150),
    start_time TIME,
    end_time TIME,
    sched_day VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_subjects_instructor
        FOREIGN KEY (instructor_id)
        REFERENCES instructors(instructor_id)
        ON DELETE SET NULL
) ENGINE=InnoDB";

$sql_students = "CREATE TABLE IF NOT EXISTS students (
    student_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_id INT UNSIGNED NULL,
    student_id_number VARCHAR(50) UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    middle_initial VARCHAR(10),
    sex VARCHAR(20),
    course VARCHAR(100),
    status VARCHAR(50) DEFAULT 'Active',
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_students_section
        FOREIGN KEY (section_id)
        REFERENCES sections(section_id)
        ON DELETE CASCADE
) ENGINE=InnoDB";

$sql_enrollments = "CREATE TABLE IF NOT EXISTS enrollments (
    enrollment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    subject_id INT UNSIGNED NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_enrollments_student
        FOREIGN KEY (student_id)
        REFERENCES students(student_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_enrollments_subject
        FOREIGN KEY (subject_id)
        REFERENCES subjects(subject_id)
        ON DELETE CASCADE
) ENGINE=InnoDB";

$sql_attendance = "CREATE TABLE IF NOT EXISTS attendance_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    section_id INT UNSIGNED NULL,
    subject_id INT UNSIGNED NULL,
    instructor_id INT UNSIGNED NULL,
    week_number INT(3),
    status VARCHAR(50) NOT NULL DEFAULT 'Present',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_attendance_student
        FOREIGN KEY (student_id)
        REFERENCES students(student_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_attendance_section
        FOREIGN KEY (section_id)
        REFERENCES sections(section_id)
        ON DELETE SET NULL,

    CONSTRAINT fk_attendance_subject
        FOREIGN KEY (subject_id)
        REFERENCES subjects(subject_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_attendance_instructor
        FOREIGN KEY (instructor_id)
        REFERENCES instructors(instructor_id)
        ON DELETE SET NULL
) ENGINE=InnoDB";

$sql_grades = "CREATE TABLE IF NOT EXISTS student_grades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    section_id INT UNSIGNED NULL,
    subject_id INT UNSIGNED NOT NULL,
    instructor_id INT UNSIGNED NULL,
    assessment_type VARCHAR(100),
    score DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_grade (student_id, subject_id, assessment_type),

    CONSTRAINT fk_grades_student
        FOREIGN KEY (student_id)
        REFERENCES students(student_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_grades_subject
        FOREIGN KEY (subject_id)
        REFERENCES subjects(subject_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_grades_instructor
        FOREIGN KEY (instructor_id)
        REFERENCES instructors(instructor_id)
        ON DELETE SET NULL
) ENGINE=InnoDB";

$sql_assessment_settings = "CREATE TABLE IF NOT EXISTS assessment_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_id INT UNSIGNED NOT NULL,
    subject_id INT UNSIGNED NOT NULL,
    instructor_id INT UNSIGNED NULL,
    assessment_type VARCHAR(50) NOT NULL,
    max_score INT NOT NULL,

    UNIQUE KEY unique_assessment (section_id, subject_id, assessment_type),

    CONSTRAINT fk_as_section
        FOREIGN KEY (section_id)
        REFERENCES sections(section_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_as_subject
        FOREIGN KEY (subject_id)
        REFERENCES subjects(subject_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_as_instructor
        FOREIGN KEY (instructor_id)
        REFERENCES instructors(instructor_id)
        ON DELETE SET NULL
) ENGINE=InnoDB";

$tables = [
    "Instructors" => $sql_instructors,
    "Sections" => $sql_sections,
    "Subjects" => $sql_subjects,
    "Students" => $sql_students,
    "Enrollments" => $sql_enrollments,
    "Attendance" => $sql_attendance,
    "Grades" => $sql_grades,
    "Assessment Settings" => $sql_assessment_settings
];

echo "<h3>Database Setup Status</h3>";

foreach ($tables as $name => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Table '$name': OK<br>";
    } else {
        echo "Error creating '$name': " . $conn->error . "<br>";
    }
}

$conn->close();
?>
