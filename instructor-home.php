<?php
session_start();
include 'php/db_connect.php';

// Authentication Check
if (!isset($_SESSION['instructor_name'])) {
    header("Location: instructor-login.php");
    exit();
}

$inst = $_SESSION['instructor_name'];
$current_instructor = $inst;

// Fetch Subjects for the current instructor
$sql = "SELECT * FROM subjects WHERE instructor_id = '$inst'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ClassMate - Instructor Dashboard</title>

    <link rel="icon" type="image/svg+xml" href="SVG/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/dashboard.css">

    <style>
        .table-container { overflow-x: auto; margin-top: 20px; }
        .student-checkbox { cursor: pointer; transform: scale(1.2); }
        .search-box input { padding: 8px 35px 8px 15px; border-radius: 20px; border: 1px solid #ddd; }
        /* Flex styling for the name row in the modal */
        .name-row { display: flex; gap: 10px; }
        .name-row .form-group { flex: 2; }
        .name-row .form-group.mi { flex: 0.5; }
    </style>
</head>

<body>
    <header>
        <h1>
            <a href="index.html" class="logo-link">
                <i class="fa-solid fa-graduation-cap"></i> ClassMate
            </a>
        </h1>
        <div class="header-user-info">
            <span class="welcome-text">Welcome, <strong><?php echo htmlspecialchars($current_instructor); ?>!</strong></span>
            <a href="logout.php" class="account-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </header>

    <div class="custom-shape-divider-top-1766060304">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" class="shape-fill"></path>
            <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" class="shape-fill"></path>
            <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" class="shape-fill"></path>
        </svg>
    </div>

    <section class="main-dashboard">
        <div class="dashboard-box">
            <nav class="sidebar">
                <ul class="nav-links">
                    <li><a href="#" class="nav-btn active" data-target="courses"><i class="fa-solid fa-book-open"></i> Courses</a></li>
                    <li><a href="#" class="nav-btn" data-target="students"><i class="fa-solid fa-user-graduate"></i> Sections</a></li>
                    <li><a href="#" class="nav-btn" data-target="attendance"><i class="fa-solid fa-calendar-check"></i> Attendance</a></li>
                    <li><a href="#" class="nav-btn" data-target="evaluation"><i class="fa-solid fa-file-invoice"></i> Evaluation</a></li>
                    <li><a href="#" class="nav-btn" data-target="analytics"><i class="fa-solid fa-chart-line"></i> Analytics</a></li>
                </ul>
                <div class="sidebar-bottom">
                    <a href="#" class="nav-btn" data-target="enrollment"><i class="fa-solid fa-circle-plus"></i> Enroll a Student</a>
                </div>
            </nav>

            <main class="content-area">
                <section id="courses" class="content-section">
                    <div class="dashboard-display">
                        <div class="table-controls">
                            <button class="btn btn-primary" onclick="openAddSubjectModal()"><i class="fa-solid fa-plus"></i> Add Subject</button>
                            <div class="search-box">
                                <i class="fa-solid fa-search"></i>
                                <input type="text" id="searchSubjects" placeholder="Search subjects...">
                            </div>
                        </div>
                        <div class="table-container">
                            <table class="subjects-table">
                                <thead>
                                    <tr>
                                        <th>Subject Code</th>
                                        <th>Subject Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['subject_code']); ?></td>
                                            <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                            <td>
                                                <a href="php/delete_subject.php?id=<?php echo $row['subject_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this subject?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                        <tr class="no-data"><td colspan="3">No subjects found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section id="students" class="content-section" style="display: none;">
                    <div class="dashboard-display">
                        <div class="table-controls">
                            <button class="btn btn-primary" onclick="openAddSectionModal()"><i class="fa-solid fa-plus"></i> Add Section</button>
                        </div>
                        <div class="table-container">
                            <table class="subjects-table">
                                <thead>
                                    <tr>
                                        <th>Section Name</th>
                                        <th>Subject</th>
                                        <th>School Year</th>
                                        <th>Semester</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sec_sql = "SELECT s.*, sub.subject_code FROM sections s JOIN subjects sub ON s.subject_id = sub.subject_id WHERE sub.instructor_id = '$current_instructor'";
                                    $sec_result = $conn->query($sec_sql);
                                    if ($sec_result->num_rows > 0): while ($sec = $sec_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($sec['section_name']); ?></td>
                                            <td><?php echo htmlspecialchars($sec['subject_code']); ?></td>
                                            <td><?php echo htmlspecialchars($sec['school_year']); ?></td>
                                            <td><?php echo htmlspecialchars($sec['semester']); ?></td>
                                            <td><button class="btn btn-sm btn-info">View Students</button></td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                        <tr class="no-data"><td colspan="5">No sections created yet.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section id="enrollment" class="content-section" style="display: none;">
                    <div class="dashboard-display">
                        <div class="table-controls">
                            <button class="btn btn-primary" onclick="openAddStudentModal()">
                                <i class="fa-solid fa-user-plus"></i> Add New Student
                            </button>
                            <div class="search-box">
                                <i class="fa-solid fa-search"></i>
                                <input type="text" id="enrollmentSearch" onkeyup="filterEnrollmentTable()" placeholder="Search name or ID...">
                            </div>
                        </div>
                        <div class="table-container">
                            <table class="subjects-table" id="mainEnrollmentTable">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAllStudents" onclick="toggleSelectAll(this)"> Select All</th>
                                        <th>Student Number</th>
                                        <th>Last Name</th>
                                        <th>First Name</th>
                                        <th>M.I.</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Modified Query to order by Last Name
                                    $allStudents = $conn->query("SELECT * FROM students ORDER BY last_name ASC");
                                    if ($allStudents->num_rows > 0): while ($st = $allStudents->fetch_assoc()): ?>
                                        <tr>
                                            <td><input type="checkbox" class="student-checkbox" value="<?php echo $st['id']; ?>"></td>
                                            <td><?php echo htmlspecialchars($st['student_number']); ?></td>
                                            <td><?php echo htmlspecialchars($st['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($st['first_name']); ?></td>
                                            <td><?php echo htmlspecialchars($st['middle_initial']); ?></td>
                                            <td><?php echo htmlspecialchars($st['email']); ?></td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                        <tr class="no-data"><td colspan="6">No students in database.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <div id="addSubjectModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Add New Subject</h2>
                            <button class="close-btn" onclick="closeAddSubjectModal()">&times;</button>
                        </div>
                        <form action="php/add_subject.php" method="POST">
                            <div class="form-group"><label>Subject Code</label><input type="text" name="subjectCode" required></div>
                            <div class="form-group"><label>Subject Name</label><input type="text" name="subjectName" required></div>
                            <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Subject</button></div>
                        </form>
                    </div>
                </div>

                <div id="addSectionModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Create New Section</h2>
                            <button class="close-btn" onclick="closeAddSectionModal()">&times;</button>
                        </div>
                        <form action="php/add_section.php" method="POST">
                            <div class="form-group">
                                <label>Select Subject</label>
                                <select name="subject_id" required>
                                    <?php $sub_drop = $conn->query("SELECT * FROM subjects WHERE instructor_id = '$current_instructor'");
                                    while($s = $sub_drop->fetch_assoc()) echo "<option value='{$s['subject_id']}'>{$s['subject_code']}</option>"; ?>
                                </select>
                            </div>
                            <div class="form-group"><label>Section Name</label><input type="text" name="section_name" required></div>
                            <div class="form-group"><label>School Year</label><input type="text" name="school_year" required></div>
                            <div class="form-group">
                                <label>Semester</label>
                                <select name="semester"><option>1st Semester</option><option>2nd Semester</option></select>
                            </div>
                            <div class="modal-footer"><button type="submit" class="btn btn-primary">Create Section</button></div>
                        </form>
                    </div>
                </div>

                <div id="addStudentModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2><i class="fa-solid fa-user-plus"></i> Register New Student</h2>
                            <button class="close-btn" onclick="closeAddStudentModal()">&times;</button>
                        </div>
                        <form action="php/add_new_student.php" method="POST">
                            <div class="name-row">
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" name="last_name" placeholder="e.g. Dela Cruz" required>
                                </div>
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" name="first_name" placeholder="e.g. Juan" required>
                                </div>
                                <div class="form-group mi">
                                    <label>M.I.</label>
                                    <input type="text" name="middle_initial" maxlength="2" placeholder="P.">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Student Number</label>
                                <input type="text" name="student_number" placeholder="2024-00000" required>
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" placeholder="student@cvsu.edu.ph" required>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Register Student</button>
                            </div>
                        </form>
                    </div>
                </div>

            </main>
        </div>
    </section>

    <script>
        // Sidebar Navigation Logic
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('data-target');
                document.querySelectorAll('.content-section').forEach(s => s.style.display = 'none');
                document.getElementById(target).style.display = 'block';
                document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Enrollment Table Search
        function filterEnrollmentTable() {
            let input = document.getElementById("enrollmentSearch").value.toLowerCase();
            let rows = document.querySelectorAll("#mainEnrollmentTable tbody tr");
            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(input) ? "" : "none";
            });
        }

        // Select All Checkboxes
        function toggleSelectAll(source) {
            document.querySelectorAll('.student-checkbox').forEach(cb => {
                if (cb.closest('tr').style.display !== 'none') cb.checked = source.checked;
            });
        }

        // Modal Controls
        function openAddSubjectModal() { document.getElementById('addSubjectModal').style.display = 'block'; }
        function closeAddSubjectModal() { document.getElementById('addSubjectModal').style.display = 'none'; }
        function openAddSectionModal() { document.getElementById('addSectionModal').style.display = 'block'; }
        function closeAddSectionModal() { document.getElementById('addSectionModal').style.display = 'none'; }
        function openAddStudentModal() { document.getElementById('addStudentModal').style.display = 'block'; }
        function closeAddStudentModal() { document.getElementById('addStudentModal').style.display = 'none'; }
    </script>
</body>
</html>