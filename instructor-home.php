<?php
session_start();

include 'php/db_connect.php';
if (!isset($_SESSION['instructor_name'])) {
    header("Location: instructor-login.php");
    exit();
}

$inst = $_SESSION['instructor_name'];
$current_instructor = $inst;

$sql = "SELECT * FROM subjects WHERE instructor_id = '$inst'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ClassMate</title>

    <link rel="icon" type="image/svg+xml" href="SVG/favicon.svg">

    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/dashboard.css">

    <script src="js/modals.js"></script>
</head>

<body>
    <header>
        <h1>
            <a href="index.html" class="logo-link">
                <i class="fa-solid fa-graduation-cap"></i>
                ClassMate
            </a>
        </h1>

        <div class="header-user-info">
            <span class="welcome-text">Welcome,
                <strong><?php echo htmlspecialchars($current_instructor); ?>!</strong></span>
            <a href="logout.php" class="account-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </header>
    <!-- wave part sa header  -->
    <div class="custom-shape-divider-top-1766060304">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path
                d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z"
                opacity=".25" class="shape-fill"></path>
            <path
                d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z"
                opacity=".5" class="shape-fill"></path>
            <path
                d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z"
                class="shape-fill"></path>
        </svg>
    </div>
    <section class="main-dashboard">
        <div class="dashboard-box">
            <nav class="sidebar">
                <ul class="nav-links">
                    <li><a href="#" class="nav-btn active" data-target="courses"><i class="fa-solid fa-book-open"></i>
                            Courses</a></li>
                    <li><a href="#" class="nav-btn" data-target="students"><i class="fa-solid fa-user-graduate"></i>
                            Students</a></li>
                    <li><a href="#" class="nav-btn" data-target="attendance"><i class="fa-solid fa-calendar-check"></i>
                            Attendance</a></li>
                    <li><a href="#" class="nav-btn" data-target="evaluation"><i class="fa-solid fa-file-invoice"></i>
                            Evaluation</a></li>
                    <li><a href="#" class="nav-btn" data-target="analytics"><i class="fa-solid fa-chart-line"></i>
                            Analytics</a></li>

                </ul>
                <div class="sidebar-bottom">
                    <a href="#" class="nav-btn" data-target="enrollment"><i class="fa-solid fa-circle-plus"></i> Enroll
                        a Student</a>
                </div>
            </nav>

            <main class="content-area">
                <section id="courses" class="content-section">
                    <div class="dashboard-display">
                        <div class="table-controls">
                            <button class="btn btn-primary" onclick="openAddSubjectModal()">
                                <i class="fa-solid fa-plus"></i> Add Subject
                            </button>
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
                                        <th>Enrolled Students</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="subjectsTableBody">
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['subject_code']); ?></td>

                                                <td><?php echo htmlspecialchars($row['subject_name']); ?></td>

                                                <td>0</td>

                                                <td>
                                                    <button class="btn btn-sm btn-info"
                                                        onclick="viewStudentsBySubject(<?php echo $row['subject_id']; ?>)">
                                                        View Students
                                                    </button>
                                                    <a href="php/delete_subject.php?id=<?php echo $row['subject_id']; ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this subject?')">
                                                        Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr class="no-data">
                                            <td colspan="4" style="text-align: center; padding: 2rem; color: #999;">
                                                <i class="fa-solid fa-book"
                                                    style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                                                No subjects found. Click "Add Subject" to get started.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <div id="addSubjectModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2><i class="fa-solid fa-book"></i> Add New Subject</h2>
                            <button class="close-btn" onclick="closeAddSubjectModal()">&times;</button>
                        </div>
                        <form action="php/add_subject.php" method="POST">
                            <div class="form-group">
                                <label>Subject Code</label>
                                <input type="text" name="subjectCode" placeholder="e.g., DCIT 24" required>
                            </div>
                            <div class="form-group">
                                <label>Subject Name</label>
                                <input type="text" name="subjectName" placeholder="e.g., Information Management"
                                    required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    onclick="closeAddSubjectModal()">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Subject</button>
                            </div>
                        </form>
                    </div>
                </div>

                <section id="students" class="content-section" style="display: none;">
                    <div class="dashboard-display">
                        <div class="table-controls">
                            <button class="btn btn-primary" onclick="openAddSectionModal()">
                                <i class="fa-solid fa-plus"></i> Add Section
                            </button>
                            <div class="search-box">
                                <i class="fa-solid fa-search"></i>
                                <input type="text" id="searchStudents" placeholder="Search students...">
                            </div>
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
                                <tbody id="sectionsTableBody">
                                    <?php
                                    $sec_sql = "SELECT s.*, sub.subject_code 
                                    FROM sections s 
                                    JOIN subjects sub ON s.subject_id = sub.subject_id 
                                    WHERE sub.instructor_id = '$current_instructor'";
                                    $sec_result = $conn->query($sec_sql);

                                    if ($sec_result->num_rows > 0):
                                        while ($sec = $sec_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($sec['section_name']); ?></td>
                                                <td><?php echo htmlspecialchars($sec['subject_code']); ?></td>
                                                <td><?php echo htmlspecialchars($sec['school_year']); ?></td>
                                                <td><?php echo htmlspecialchars($sec['semester']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info"
                                                        onclick="viewStudentsInSection(<?php echo $sec['section_id']; ?>)">
                                                        View Students
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile;
                                    else: ?>
                                        <tr class="no-data">
                                            <td colspan="6" style="text-align: center; padding: 2rem; color: #999;">
                                                <i class="fa-solid fa-book"
                                                    style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                                                No sections created yet.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <div id="addSectionModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2><i class="fa-solid fa-users"></i> Create New Section</h2>
                            <button class="close-btn" onclick="closeAddSectionModal()">&times;</button>
                        </div>
                        <form action="php/add_section.php" method="POST">
                            <div class="form-group">
                                <label>Select Subject</label>
                                <select name="subject_id" required>
                                    <option value="">-- Choose a Subject --</option>
                                    <?php
                                    // Re-run query to populate dropdown
                                    $subjects_dropdown = $conn->query("SELECT * FROM subjects WHERE instructor_id = '$current_instructor'");
                                    while ($sub = $subjects_dropdown->fetch_assoc()): ?>
                                        <option value="<?php echo $sub['subject_id']; ?>">
                                            <?php echo htmlspecialchars($sub['subject_code'] . " - " . $sub['subject_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Section Name</label>
                                <input type="text" name="section_name" placeholder="e.g., BSIT 2-1" required>
                            </div>
                            <div class="form-group">
                                <label>School Year</label>
                                <input type="text" name="school_year" placeholder="2024-2025" required>
                            </div>
                            <div class="form-group">
                                <label>Semester</label>
                                <select name="semester" required>
                                    <option value="1st Semester">1st Semester</option>
                                    <option value="2nd Semester">2nd Semester</option>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    onclick="closeAddSectionModal()">Cancel</button>
                                <button type="submit" class="btn btn-primary">Create Section</button>
                            </div>
                        </form>
                    </div>
                </div>

                <section id="attendance" class="content-section" style="display: none;">
                    <div class="dashboard-display">
                        <div class="table-controls"></div>
                        <div class="table-container">
                            <table class="subjects-table">
                                <thead>
                                    <tr>
                                        <th>TEST</th>
                                        <th>TEST</th>
                                        <th>TEST</th>
                                        <th>TEST</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceTableBody">
                                    <tr class="no-data">
                                        <td colspan="6" style="text-align: center; padding: 2rem; color: #999;">
                                            <i class="fa-solid fa-book"
                                                style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                                            No Attendance found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section id="evaluation" class="content-section" style="display: none;">
                    <div class="dashboard-display">
                        <div class="table-controls"></div>
                        <div class="table-container">
                            <table class="subjects-table">
                                <thead>
                                    <tr>
                                        <th>TEST</th>
                                        <th>TEST</th>
                                        <th>TEST</th>
                                        <th>TEST</th>
                                    </tr>
                                </thead>
                                <tbody id="subjectsTableBody">
                                    <tr class="no-data">
                                        <td colspan="6" style="text-align: center; padding: 2rem; color: #999;">
                                            <i class="fa-solid fa-book"
                                                style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                                            No Evaluation found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section id="analytics" class="content-section" style="display: none;">
                    <div class="dashboard-display">
                        <div class="table-controls">
                        </div>

                        <div class="table-container">
                            <table class="subjects-table">
                                <thead>
                                    <tr>
                                        <th>TEST</th>
                                        <th>TEST</th>
                                        <th>TEST</th>
                                        <th>TEST</th>
                                    </tr>
                                </thead>
                                <tbody id="subjectsTableBody">
                                    <tr class="no-data">
                                        <td colspan="6" style="text-align: center; padding: 2rem; color: #999;">
                                            <i class="fa-solid fa-book"
                                                style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                                            No Analytics found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>


                <section id="attendance" class="content-section" style="display: none;">
                </section>

                <section id="evaluation" class="content-section" style="display: none;">
                    <div class="dashboard-display">
                    </div>
                </section>

                <section id="analytics" class="content-section" style="display: none;">
                    <div class="dashboard-display">
                    </div>
                </section>

                <section id="enrollment" class="content-section" style="display: none;">
                    <div class="dashboard-display">
                    </div>
                </section>
            </main>
        </div>
    </section>

    <div id="viewStudentsModal" class="modal">
        <div class="modal-content" style="width: 80%; max-width: 800px;">
            <div class="modal-header">
                <h2 id="modalSectionTitle"><i class="fa-solid fa-user-graduate"></i> Section Students</h2>
                <button class="close-btn" onclick="closeViewStudentsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <h4>Student List</h4>
                    <form action="php/enroll_to_section.php" method="POST" style="display: flex; gap: 10px;">
                        <input type="hidden" name="section_id" id="hidden_section_id">
                        <select name="student_id" required style="padding: 8px; border-radius: 5px;">
                            <option value="">-- Select Student to Enroll --</option>
                            <?php
                            // Fetch all students NOT yet in this specific section (conceptually)
                            // For simplicity, we fetch all students here
                            $all_students = $conn->query("SELECT student_id, first_name, last_name FROM students ORDER BY last_name");
                            while ($st = $all_students->fetch_assoc()): ?>
                                <option value="<?php echo $st['student_id']; ?>">
                                    <?php echo htmlspecialchars($st['last_name'] . ", " . $st['first_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Add to Section</button>
                    </form>
                </div>

                <div class="table-container">
                    <table class="subjects-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="sectionStudentsList">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>

</html>