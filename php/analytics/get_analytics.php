<?php
session_start();
include '../db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['instructor_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}
$instructor_id = $_SESSION['instructor_name'];

$section_id = $_GET['section_id'] ?? null;
$type = $_GET['type'] ?? '';
$subject_id = $_GET['subject_id'] ?? null;
$student_id = $_GET['student_id'] ?? null;

if (!$section_id) {
    echo json_encode(['status' => 'error', 'message' => 'No section selected']);
    exit();
}

$response = ['status' => 'success', 'data' => []];

// --- CONSTANTS ---
$WEIGHT_ATTENDANCE = 0.20;
$WEIGHT_QUIZZES    = 0.20;
$WEIGHT_EXAMS      = 0.60;
$TOTAL_WEEKS       = 18; 

// --- HELPER: Compute Grade for a Single Subject ---
function calculateSubjectGrade($conn, $student_id, $section_id, $subject_id, $instructor_id, $WEIGHT_ATTENDANCE, $WEIGHT_QUIZZES, $WEIGHT_EXAMS, $TOTAL_WEEKS) {
    // 1. Get Max Scores for this Subject
    $sql_max = "SELECT assessment_type, max_score FROM assessment_settings WHERE section_id = ? AND instructor_id = ? AND subject_id = ?";
    $stmt_max = $conn->prepare($sql_max);
    $stmt_max->bind_param("iii", $section_id, $instructor_id, $subject_id);
    $stmt_max->execute();
    $res_max = $stmt_max->get_result();
    
    $max_scores = [];
    while($row = $res_max->fetch_assoc()) {
        $max_scores[$row['assessment_type']] = (int)$row['max_score'];
    }

    // 2. Attendance
    $stmt_att = $conn->prepare("SELECT COUNT(*) as c FROM attendance_records WHERE student_id = ? AND section_id = ? AND subject_id = ? AND status IN ('P', 'L')");
    $stmt_att->bind_param("iii", $student_id, $section_id, $subject_id);
    $stmt_att->execute();
    $att_count = $stmt_att->get_result()->fetch_assoc()['c'];
    
    $att_grade = ($att_count / $TOTAL_WEEKS) * 100;
    if($att_grade > 100) $att_grade = 100;

    // 3. Quizzes & Exams
    $stmt_grades = $conn->prepare("SELECT assessment_type, score FROM student_grades WHERE student_id = ? AND section_id = ? AND subject_id = ?");
    $stmt_grades->bind_param("iii", $student_id, $section_id, $subject_id);
    $stmt_grades->execute();
    $res_grades = $stmt_grades->get_result();

    $quiz_total = 0; $quiz_items = 0;
    $exam_total = 0; $exam_items = 0;

    while($g = $res_grades->fetch_assoc()) {
        $g_type = $g['assessment_type'];
        $score = (float)$g['score'];
        $max = $max_scores[$g_type] ?? 100;

        $percentage = ($score / $max) * 100;
        if ($percentage > 100) $percentage = 100;

        if (strpos($g_type, 'Quiz') !== false) {
            $quiz_total += $percentage;
            $quiz_items++;
        } else if ($g_type == 'Midterm' || $g_type == 'Finals') {
            $exam_total += $percentage;
            $exam_items++;
        }
    }

    $quiz_grade = ($quiz_items > 0) ? ($quiz_total / $quiz_items) : 0;
    $exam_grade = ($exam_items > 0) ? ($exam_total / $exam_items) : 0;

    $final_raw = ($att_grade * $WEIGHT_ATTENDANCE) + ($quiz_grade * $WEIGHT_QUIZZES) + ($exam_grade * $WEIGHT_EXAMS);
    
    return [
        'raw' => $final_raw,
        'college' => getCollegeGrade($final_raw),
        'status' => (getCollegeGrade($final_raw) == '5.00' || getCollegeGrade($final_raw) > 3.00) ? 'Failed' : 'Passed'
    ];
}

function getCollegeGrade($score) {
    if ($score > 100) $score = 100;
    if ($score >= 97) return "1.00";
    if ($score >= 94) return "1.25";
    if ($score >= 91) return "1.50";
    if ($score >= 88) return "1.75";
    if ($score >= 85) return "2.00";
    if ($score >= 82) return "2.25";
    if ($score >= 79) return "2.50";
    if ($score >= 76) return "2.75";
    if ($score >= 75) return "3.00";
    return "5.00";
}

switch ($type) {
    case 'ranking':
        // 1. Get All Students in Section
        $students = $conn->query("SELECT student_id, first_name, last_name FROM students WHERE section_id = '$section_id'")->fetch_all(MYSQLI_ASSOC);
        $data = [];

        foreach($students as $s) {
            $sid = $s['student_id'];
            
            // 2. Get Enrolled Subjects
            $sql_subs = "SELECT s.subject_id 
                         FROM subjects s 
                         JOIN enrollments e ON s.subject_id = e.subject_id 
                         WHERE e.student_id = '$sid' AND s.instructor_id = '$instructor_id'";
            $res_subs = $conn->query($sql_subs);
            
            if ($res_subs && $res_subs->num_rows > 0) {
                $total_grade = 0;
                $count = 0;
                
                while($sub = $res_subs->fetch_assoc()) {
                    $gradeInfo = calculateSubjectGrade($conn, $sid, $section_id, $sub['subject_id'], $instructor_id, $WEIGHT_ATTENDANCE, $WEIGHT_QUIZZES, $WEIGHT_EXAMS, $TOTAL_WEEKS);
                    $total_grade += $gradeInfo['raw'];
                    $count++;
                }
                
                $avg_raw = $count > 0 ? ($total_grade / $count) : 0;
                
                $data[] = [
                    'student_name' => $s['last_name'] . ', ' . $s['first_name'],
                    'average_grade' => getCollegeGrade($avg_raw),
                    'raw_avg' => $avg_raw
                ];
            }
        }
        
        usort($data, function($a, $b) { return $b['raw_avg'] <=> $a['raw_avg']; });
        $response['data'] = $data;
        break;

    case 'student_averages':
        $sql = "SELECT student_id, first_name, last_name FROM students WHERE section_id = ? ORDER BY last_name ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $list = [];
        while($row = $result->fetch_assoc()) {
            $list[] = [
                'student_id' => $row['student_id'],
                'student_name' => $row['last_name'] . ', ' . $row['first_name']
            ];
        }
        $response['data'] = $list;
        break;

    case 'student_breakdown':
        if (!$student_id) {
            $response = ['status' => 'error', 'message' => 'Missing student ID'];
            break;
        }

        $sql_subs = "SELECT s.subject_id, s.subject_code, s.subject_name 
                     FROM subjects s 
                     JOIN enrollments e ON s.subject_id = e.subject_id 
                     WHERE e.student_id = '$student_id' AND s.instructor_id = '$instructor_id'";
        $res_subs = $conn->query($sql_subs);
        
        $subjects_data = [];
        $total_raw = 0;
        $count = 0;

        while($sub = $res_subs->fetch_assoc()) {
            $g = calculateSubjectGrade($conn, $student_id, $section_id, $sub['subject_id'], $instructor_id, $WEIGHT_ATTENDANCE, $WEIGHT_QUIZZES, $WEIGHT_EXAMS, $TOTAL_WEEKS);
            $subjects_data[] = [
                'subject_code' => $sub['subject_code'],
                'subject_name' => $sub['subject_name'],
                'grade' => $g['college'],
                'status' => $g['status']
            ];
            $total_raw += $g['raw'];
            $count++;
        }
        
        $gwa = $count > 0 ? getCollegeGrade($total_raw / $count) : 'N/A';
        
        $response['data'] = [
            'subjects' => $subjects_data,
            'gwa' => $gwa
        ];
        break;

    case 'low_attendance':
        if (!$subject_id) {
            $response = ['status' => 'error', 'message' => 'Subject ID required'];
            break;
        }
        
        $sql = "SELECT s.first_name, s.last_name, 
                (COUNT(CASE WHEN a.status IN ('P', 'L') THEN 1 END) / ? * 100) as percentage,
                COUNT(CASE WHEN a.status IN ('P', 'L') THEN 1 END) as present_count
                FROM students s
                JOIN enrollments e ON s.student_id = e.student_id
                LEFT JOIN attendance_records a ON s.student_id = a.student_id AND a.subject_id = ?
                WHERE s.section_id = ? AND e.subject_id = ?
                GROUP BY s.student_id
                HAVING percentage < 75";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $TOTAL_WEEKS, $subject_id, $section_id, $subject_id);
        $stmt->execute();
        $response['data'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        break;

    case 'attendance_chart':
        if (!$subject_id) {
            $response = ['status' => 'error', 'message' => 'Subject ID required'];
            break;
        }
        $sql = "SELECT status, COUNT(*) as count 
                FROM attendance_records 
                WHERE section_id = ? AND subject_id = ? AND status IN ('P', 'L', 'E', 'A')
                GROUP BY status";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $section_id, $subject_id);
        $stmt->execute();
        $response['data'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        break;
        
    case 'late_absent':
        if (!$subject_id) {
            $response = ['status' => 'error', 'message' => 'Subject ID required'];
            break;
        }
        
        $sql = "SELECT s.first_name, s.last_name,
                COUNT(CASE WHEN a.status = 'L' THEN 1 END) as late_count,
                COUNT(CASE WHEN a.status = 'A' THEN 1 END) as absent_count
                FROM students s
                JOIN enrollments e ON s.student_id = e.student_id
                JOIN attendance_records a ON s.student_id = a.student_id
                WHERE s.section_id = ? AND a.subject_id = ? AND e.subject_id = ?
                GROUP BY s.student_id
                HAVING late_count > 0 OR absent_count > 0
                ORDER BY absent_count DESC, late_count DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $section_id, $subject_id, $subject_id);
        $stmt->execute();
        $response['data'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        break;

    default:
        $response = ['status' => 'error', 'message' => 'Invalid analytics type'];
}

echo json_encode($response);
$conn->close();
?>