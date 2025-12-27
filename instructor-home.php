<?php
session_start();
include 'php/db_connect.php';

// Authentication Check
if (!isset($_SESSION['instructor_name'])) {
    header("Location: instructor-login.php");
    exit();
}

$current_instructor = $_SESSION['instructor_name'];
$inst = $current_instructor;

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
        /* Table and UI Enhancements */
        .table-container { overflow-x: auto; margin-top: 20px; }
        .student-checkbox { cursor: pointer; transform: scale(1.2); }
        .search-box input { padding: 8px 35px 8px 15px; border-radius: 20px; border: 1px solid #ddd; }
        
        /* Name Row Flex for Modals */
        .name-row { display: flex; gap: 10px; }
        .name-row .form-group { flex: 2; }
        .name-row .form-group.mi { flex: 0.5; }

        /* Modern Feedback Backdrop */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }

        .feedback-card {
            background: #ffffff;
            width: 90%;
            max-width: 400px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            text-align: center;
            padding: 30px;
            animation: modalPop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .feedback-icon { font-size: 3rem; margin-bottom: 15px; }
        .error .feedback-icon { color: #ef4444; }
        .success .feedback-icon { color: #10b981; }
        
        .feedback-btn {
            width: 100%; padding: 12px; border: none; border-radius: 12px;
            background: #1e293b; color: white; font-weight: 600; cursor: pointer; margin-top: 20px;
        }

        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>

<body>
    <div id="universalModal" class="modal-overlay" style="display: none;">
        <div id="feedbackCard" class="feedback-card">
            <div id="feedbackIcon" class="feedback-icon"></div>
            <h2 id="modalTitle" style="margin-bottom: 10px; color: #1e293b;"></h2>
            <p id="modalMsg" style="color: #64748b;"></p>
            <button class="feedback-btn" onclick="closeFeedback()">Acknowledge</button>
        </div>
    </div>

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

    <section class="main-dashboard">
        <div class="dashboard-box">
            <nav class="sidebar">
                <ul class="nav-links">
                    <li><a href="#" class="nav-btn active" data-target="courses"><i class="fa-solid fa-book-open"></i> Courses</a></li>
                    <li><a href="#" class="nav-btn" data-target="students"><i class="fa-solid fa-user-graduate"></i> Sections</a></li>
                    <li><a href="#" class="nav-btn" data-target="attendance"><i class="fa-solid fa-calendar-check"></i> Attendance</a></li>
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
                                            <td><button class="btn btn-sm btn-danger">Delete</button></td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                        <tr class="no-data"><td colspan="3">No subjects found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section id="enrollment" class="content-section" style="display: none;">
                    <div class="dashboard-display">
                        <div class="table-controls">
                            <button class="btn btn-primary" onclick="openAddStudentModal()"><i class="fa-solid fa-user-plus"></i> Quick Register</button>
                            <div class="search-box">
                                <i class="fa-solid fa-search"></i>
                                <input type="text" id="enrollmentSearch" onkeyup="filterEnrollmentTable()" placeholder="Search names...">
                            </div>
                        </div>
                        <div class="table-container">
                            <table class="subjects-table" id="mainEnrollmentTable">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAllStudents" onclick="toggleSelectAll(this)"> Select All</th>
                                        <th>ID Number</th>
                                        <th>Last Name</th>
                                        <th>First Name</th>
                                        <th>M.I.</th>
                                        <th>Institutional Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $allStudents = $conn->query("SELECT * FROM students ORDER BY last_name ASC");
                                    if ($allStudents && $allStudents->num_rows > 0): while ($st = $allStudents->fetch_assoc()): ?>
                                        <tr>
                                            <td><input type="checkbox" class="student-checkbox" value="<?php echo $st['id']; ?>"></td>
                                            <td><?php echo htmlspecialchars($st['student_number']); ?></td>
                                            <td><?php echo htmlspecialchars($st['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($st['first_name']); ?></td>
                                            <td><?php echo htmlspecialchars($st['middle_name']); ?></td>
                                            <td><?php echo htmlspecialchars($st['email']); ?></td>
                                        </tr>
                                    <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <div id="addStudentModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2><i class="fa-solid fa-user-plus"></i> New Student</h2>
                            <button class="close-btn" onclick="closeAddStudentModal()">&times;</button>
                        </div>

                        <div id="modalError" style="display:none; background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #ef4444; font-size: 0.9rem;">
                            <i class="fa-solid fa-circle-exclamation"></i> <span id="errorText"></span>
                        </div>

                        <form id="quickRegisterForm">
                            <div class="name-row">
                                <div class="form-group">
                                    <label>Last Name *</label>
                                    <input type="text" name="last_name" placeholder="Dela Cruz" required>
                                </div>
                                <div class="form-group">
                                    <label>First Name *</label>
                                    <input type="text" name="first_name" placeholder="Juan" required>
                                </div>
                                <div class="form-group mi">
                                    <label>M.I.</label>
                                    <input type="text" name="middle_name" maxlength="1" placeholder="A">
                                </div>
                            </div>
                            <div class="info-banner" style="background: #f0f7ff; padding: 10px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #007bff;">
                                <p style="font-size: 0.8rem; color: #555; margin: 0;">
                                    <i class="fa-solid fa-circle-info"></i> ID and Email are generated automatically.
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" id="regBtn" class="btn btn-primary">Register Student</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </section>

    <script>
        // 1. Sidebar Navigation
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

        // 2. Feedback Modal Logic
        function showFeedback(type, title, message) {
            const modal = document.getElementById('universalModal');
            const card = document.getElementById('feedbackCard');
            card.className = 'feedback-card ' + type;
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalMsg').innerText = message;
            document.getElementById('feedbackIcon').innerHTML = (type === 'error') 
                ? '<i class="fa-solid fa-triangle-exclamation"></i>' 
                : '<i class="fa-solid fa-circle-check"></i>';
            modal.style.display = 'flex';
        }

        function closeFeedback() {
            document.getElementById('universalModal').style.display = 'none';
        }

        // 3. AJAX Registration
        document.getElementById('quickRegisterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('regBtn');
            const errorDiv = document.getElementById('modalError');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

            fetch('php/add_new_student.php', { method: 'POST', body: new FormData(this) })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'exists') {
                        document.getElementById('errorText').innerText = data.message;
                        errorDiv.style.display = 'block';
                        btn.disabled = false;
                        btn.innerText = 'Register Student';
                    } else if (data.status === 'success') {
                        closeAddStudentModal();
                        showFeedback('success', 'Success!', 'Student added to database.');
                        setTimeout(() => location.reload(), 2000);
                    }
                })
                .catch(() => showFeedback('error', 'System Error', 'Could not connect to server.'));
        });

        // 4. Enrollment Table Functions
        function filterEnrollmentTable() {
            let input = document.getElementById("enrollmentSearch").value.toLowerCase();
            let rows = document.querySelectorAll("#mainEnrollmentTable tbody tr");
            rows.forEach(row => { row.style.display = row.innerText.toLowerCase().includes(input) ? "" : "none"; });
        }

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