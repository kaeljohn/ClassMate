<?php
session_start();
include 'php/db_connect.php';

if (!isset($_SESSION['instructor_name'])) {
    header("Location: instructor-login.php");
    exit();
}

$current_instructor_id = $_SESSION['instructor_name'];
$inst = $current_instructor_id;

// Fetch Instructor Details for the Profile Section
$sql_inst = "SELECT * FROM instructors WHERE instructor_id = '$inst'";
$res_inst = $conn->query($sql_inst);
$instructor_data = $res_inst->fetch_assoc();

// Fallback if data is missing
$my_fname = $instructor_data['first_name'] ?? $inst;
$my_lname = $instructor_data['last_name'] ?? '';
$my_mi = $instructor_data['middle_initial'] ?? '';
$my_sex = $instructor_data['sex'] ?? 'Male';

// Construct Display Name
$display_name = $my_lname . ', ' . $my_fname . ' ' . $my_mi . '.';
if (empty($instructor_data['last_name'])) {
    $display_name = $inst; // Fallback to ID if no name set
}

// Generate unique Sched Code
$currentYear = date("Y");
$sql_last_code = "SELECT sched_code FROM subjects WHERE sched_code LIKE '$currentYear%' ORDER BY sched_code DESC LIMIT 1";
$res_last = $conn->query($sql_last_code);

if ($res_last && $res_last->num_rows > 0) {
    $last_row = $res_last->fetch_assoc();
    $last_seq = (int) substr($last_row['sched_code'], 4);
    $new_seq = str_pad($last_seq + 1, 5, '0', STR_PAD_LEFT);
} else {
    $new_seq = '00001';
}
$generatedSchedCode = $currentYear . $new_seq;

// Data Fetching
$res_subs = $conn->query("SELECT * FROM subjects WHERE instructor_id = '$inst' ORDER BY created_at DESC");
$subjects_list = [];
if ($res_subs && $res_subs->num_rows > 0) {
    while ($row = $res_subs->fetch_assoc()) {
        $subjects_list[] = $row;
    }
}
$res_sections = $conn->query("SELECT * FROM sections WHERE instructor_id = '$inst' ORDER BY created_at DESC");
$sections_arr = [];
while ($row = $res_sections->fetch_assoc()) {
    $sections_arr[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/svg+xml" href="svg/favicon.svg">
    <title>ClassMate | Instructor Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css/instructor-home.css">
    <script src="js/charts.js"></script>

    <style>
        /* Z-Index Fixes for Modals */
        .modal {
            z-index: 1000;
            /* Standard Modal Layer */
        }

        /* Higher Priority Modals (Alerts, Confirmations) */
        #universalModal,
        #deleteModal,
        #logoutModal,
        #courseSelectionModal,
        #maxScoreModal,
        #studentGradesModal,
        #studentProfileGradesModal,
        #selectSubjectModal {
            z-index: 9999 !important;
        }

        /* Course Modal Styles */
        .course-cat-btn {
            text-align: left;
            padding: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 5px;
            width: 100%;
            transition: all 0.2s;
        }

        .course-cat-btn:hover {
            background: #e0f2fe;
            border-color: #38bdf8;
        }

        .course-list-item {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .course-list-item:hover {
            background: #f0f9ff;
            color: var(--primary);
            font-weight: 600;
        }

        /* Max Score Input in Header */
        .max-score-display {
            font-size: 0.7rem;
            color: #64748b;
            display: block;
            font-weight: 400;
        }
    </style>
</head>

<body>

    <!-- WAVE ANIMATION BACKGROUND -->
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

    <!-- Attendance Picker -->
    <div id="attOptionsPicker" class="att-picker">
        <div class="att-opt" onclick="selectAttStatus('P')"><span class="dot"
                style="background:var(--att-present)"></span> Present</div>
        <div class="att-opt" onclick="selectAttStatus('L')"><span class="dot" style="background:var(--att-late)"></span>
            Late</div>
        <div class="att-opt" onclick="selectAttStatus('E')"><span class="dot"
                style="background:var(--att-excused)"></span> Excused</div>
        <div class="att-opt" onclick="selectAttStatus('A')"><span class="dot"
                style="background:var(--att-absent)"></span> Absent</div>
        <div class="att-opt" onclick="selectAttStatus('NONE')"
            style="border-top:1px solid #eee; margin-top:5px; color:#94a3b8;"><i class="fa-solid fa-eraser"></i> Clear
        </div>
    </div>

    <!-- Mass Attendance Picker -->
    <div id="massAttPicker" class="att-picker">
        <div
            style="padding:8px 12px; font-size:0.7rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">
            Mark Column As:</div>
        <div class="att-opt" onclick="massMark('P')"><span class="dot" style="background:var(--att-present)"></span>
            Present</div>
        <div class="att-opt" onclick="massMark('L')"><span class="dot" style="background:var(--att-late)"></span> Late
        </div>
        <div class="att-opt" onclick="massMark('E')"><span class="dot" style="background:var(--att-excused)"></span>
            Excused</div>
        <div class="att-opt" onclick="massMark('A')"><span class="dot" style="background:var(--att-absent)"></span>
            Absent</div>
    </div>

    <!-- Mass Row Picker -->
    <div id="massRowPicker" class="att-picker">
        <div
            style="padding:8px 12px; font-size:0.7rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">
            Mark Row As:</div>
        <div class="att-opt" onclick="massMarkRow('P')"><span class="dot" style="background:var(--att-present)"></span>
            Present</div>
        <div class="att-opt" onclick="massMarkRow('L')"><span class="dot" style="background:var(--att-late)"></span>
            Late</div>
        <div class="att-opt" onclick="massMarkRow('E')"><span class="dot" style="background:var(--att-excused)"></span>
            Excused</div>
        <div class="att-opt" onclick="massMarkRow('A')"><span class="dot" style="background:var(--att-absent)"></span>
            Absent</div>
    </div>

    <!-- COURSE SELECTION MODAL -->
    <div id="courseSelectionModal" class="modal">
        <div class="modal-content"
            style="width: 100%; max-width: 600px; height: 80vh; display: flex; flex-direction: column;">
            <div class="modal-banner">
                <h3>Select Program</h3>
                <button class="modal-close" onclick="toggleModal('courseSelectionModal', false)">&times;</button>
            </div>

            <div style="padding: 15px; border-bottom: 1px solid #eee;">
                <div class="search-container" style="width: 100%;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="courseSearchInput" placeholder="Search for a program..."
                        onkeyup="filterCourses()">
                </div>
            </div>

            <div id="courseModalContent" style="padding: 15px; overflow-y: auto; flex-grow: 1;">
                <div id="mainCategoryView"></div>
                <div id="subCategoryView" class="hidden"></div>
                <div id="courseListView" class="hidden"></div>
                <div id="searchResultsView" class="hidden"></div>
            </div>

            <div id="courseModalFooter"
                style="padding: 15px; border-top: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                <button class="btn btn-outline" id="courseBackBtn" onclick="courseNavBack()" disabled>Back</button>
                <span id="courseBreadcrumb" style="font-size: 0.8rem; color: var(--text-muted);">Main Categories</span>
            </div>
        </div>
    </div>

    <!-- STUDENT GRADES BREAKDOWN MODAL (Old Single Subject) -->
    <div id="studentGradesModal" class="modal">
        <div class="modal-content" style="width:100%; max-width:600px;">
            <div class="modal-banner">
                <h3 id="sgmTitle">Student Grades</h3>
                <button class="modal-close" onclick="toggleModal('studentGradesModal', false)">&times;</button>
            </div>
            <div class="table-wrap" style="padding:20px;">
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Score</th>
                            <th>Final Grade</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="sgmBody"></tbody>
                </table>
            </div>
            <div style="padding:15px; border-top:1px solid #eee; text-align:right;">
                <button class="btn btn-primary" onclick="toggleModal('studentGradesModal', false)">Close</button>
            </div>
        </div>
    </div>

    <!-- NEW: STUDENT PROFILE GRADE BREAKDOWN MODAL (All Subjects) -->
    <div id="studentProfileGradesModal" class="modal">
        <div class="modal-content" style="width:100%; max-width:700px;">
            <div class="modal-banner">
                <h3 id="spgmTitle">Student Grade Profile</h3>
                <button class="modal-close" onclick="toggleModal('studentProfileGradesModal', false)">&times;</button>
            </div>
            <div class="table-wrap" style="padding:20px; max-height: 60vh; overflow-y: auto;">
                <table style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background:#f8fafc; text-align:left;">
                            <th style="padding:10px;">Subject Code</th>
                            <th style="padding:10px;">Subject Name</th>
                            <th style="padding:10px;">Grade</th>
                            <th style="padding:10px;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="spgmBody">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
            <div
                style="padding:15px 20px; background:#f1f5f9; display:flex; justify-content:space-between; align-items:center; border-radius:0 0 12px 12px;">
                <div style="font-size:0.9rem; color:var(--text-muted);">General Weighted Average</div>
                <div style="font-size:1.5rem; font-weight:800; color:var(--primary-dark);" id="spgmTotal">0.00</div>
            </div>
        </div>
    </div>

    <!-- MAX SCORE MODAL -->
    <div id="maxScoreModal" class="modal">
        <div class="modal-content" style="max-width: 350px;">
            <div class="modal-banner">
                <h3>Set Max Score</h3>
                <button class="modal-close" onclick="toggleModal('maxScoreModal', false)">&times;</button>
            </div>
            <form onsubmit="saveMaxScoreFromModal(event)">
                <div style="padding: 20px;">
                    <p id="maxScoreModalLabel" style="margin-bottom: 10px; font-weight: 500;"></p>
                    <input type="number" id="maxScoreInput" name="maxScore" min="1" required
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1.1rem;">
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 8px;">Note: Grades higher than
                        this value will be capped.</p>
                </div>
                <div
                    style="padding: 15px 20px; text-align: right; background: #f8fafc; border-top: 1px solid #eee; border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-outline"
                        onclick="toggleModal('maxScoreModal', false)">Cancel</button>
                    <button class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Feedback Modal -->
    <div id="universalModal" class="modal">
        <div class="modal-content" style="max-width:400px; text-align:center; padding:30px;">
            <div id="feedbackIcon" style="font-size:3rem; margin-bottom:15px;"></div>
            <h3 id="modalTitle" style="margin-bottom:10px;"></h3>
            <p id="modalMsg" style="color:var(--text-muted); margin-bottom:20px;"></p>
            <div id="universalModalFooter"></div>
        </div>
    </div>

    <!-- LOGOUT CONFIRMATION MODAL -->
    <div id="logoutModal" class="modal">
        <div class="modal-content" style="max-width:400px; text-align:center; padding:30px;">
            <div style="font-size:3rem; margin-bottom:15px; color:#ef4444;"><i
                    class="fa-solid fa-right-from-bracket"></i></div>
            <h3 style="margin-bottom:10px;">Log Out?</h3>
            <p style="color:var(--text-muted); margin-bottom:25px;">Are you sure you want to end your session?</p>
            <div style="display:flex; gap:10px; justify-content:center;">
                <button class="btn btn-outline" onclick="toggleModal('logoutModal', false)">Cancel</button>
                <button class="btn btn-danger" onclick="confirmLogout()">Log Out</button>
            </div>
        </div>
    </div>

    <!-- SUBJECT PICKER MODAL -->
    <div id="selectSubjectModal" class="modal">
        <div class="modal-content" style="max-width:400px;">
            <div class="modal-banner">
                <h3>Select Subject</h3><button class="modal-close"
                    onclick="toggleModal('selectSubjectModal', false)">&times;</button>
            </div>
            <div style="padding:20px; display:flex; flex-direction:column; gap:10px;">
                <p style="font-size:0.85rem; color:var(--text-muted);">Select a subject to proceed:</p>
                <?php if (empty($subjects_list)): ?>
                    <p style="text-align:center; color:var(--text-muted);">No subjects found.</p>
                <?php else: ?>
                    <?php foreach ($subjects_list as $sl): ?>
                        <button class="btn btn-outline" style="width:100%; justify-content:space-between;"
                            onclick='handleUniversalSubjectSelect(<?php echo json_encode($sl); ?>)'>
                            <span><?php echo $sl['subject_code']; ?></span> <small><?php echo $sl['sched_day']; ?></small>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- INSTRUCTOR CUSTOMIZE MODAL -->
    <div id="customizeInstructorModal" class="modal">
        <div class="modal-content">
            <div class="modal-banner">
                <h3>Edit Profile</h3>
                <button class="modal-close" onclick="toggleModal('customizeInstructorModal', false)">&times;</button>
            </div>
            <form onsubmit="handleFormSubmit(event, 'php/update_instructor.php', 'customizeInstructorModal')">
                <div class="form-grid">
                    <div class="form-group"><label>Last Name</label><input type="text" name="lastName"
                            value="<?php echo htmlspecialchars($my_lname); ?>" required></div>
                    <div class="form-group"><label>First Name</label><input type="text" name="firstName"
                            value="<?php echo htmlspecialchars($my_fname); ?>" required></div>
                    <div class="form-group"><label>M.I.</label><input type="text" name="middleInitial"
                            value="<?php echo htmlspecialchars($my_mi); ?>" maxlength="5" placeholder="Opt."></div>
                    <div class="form-group"><label>Sex</label>
                        <select name="sex">
                            <option value="Male" <?php echo ($my_sex == 'Male' ? 'selected' : ''); ?>>Male</option>
                            <option value="Female" <?php echo ($my_sex == 'Female' ? 'selected' : ''); ?>>Female</option>
                        </select>
                    </div>
                </div>
                <div style="padding:20px; text-align:right;">
                    <button class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-container">
        <aside class="sidebar">
            <div class="logo"><i class="fa-solid fa-graduation-cap"></i> <span>ClassMate</span></div>
            <nav style="flex-grow:1">
                <a href="#" class="nav-item active" data-target="subjects"><i class="fa-solid fa-book-open"></i>
                    <span>Subjects</span></a>
                <a href="#" class="nav-item" data-target="sections"><i class="fa-solid fa-layer-group"></i>
                    <span>Sections</span></a>
                <a href="#" class="nav-item" data-target="attendance"><i class="fa-solid fa-calendar-check"></i>
                    <span>Attendance</span></a>
                <a href="#" class="nav-item" data-target="grades"><i class="fa-solid fa-chart-simple"></i> <span>Quizzes
                        & Exams</span></a>
                <a href="#" class="nav-item" data-target="analytics"><i class="fa-solid fa-chart-line"></i>
                    <span>Analytics</span></a>
            </nav>

            <a href="#" class="nav-item" style="color:#ef4444;" onclick="toggleModal('logoutModal', true)"><i
                    class="fa-solid fa-door-open"></i> <span>Logout</span></a>
        </aside>

        <main class="main-content">
            <header class="top-nav">
                <h1 id="pageTitle" style="font-size: 1.1rem; font-weight: 800; color: var(--primary-dark);">My Subjects
                </h1>

                <div class="user-profile-trigger" onclick="toggleModal('customizeInstructorModal', true)">
                    <div style="text-align:right;">
                        <p style="font-weight: 800; font-size: 0.9rem;"><?php echo htmlspecialchars($display_name); ?>
                        </p>
                        <p style="font-size: 0.75rem; color: var(--text-muted);">Instructor</p>
                    </div>
                    <div class="avatar"><?php echo strtoupper(substr($my_fname, 0, 1)); ?></div>
                </div>
            </header>

            <!-- SUBJECTS -->
            <section id="subjects" class="section-card content-section">
                <div class="section-header">
                    <div>
                        <h2>Subject Load</h2>
                        <p>Manage your schedules</p>
                    </div>
                    <button class="btn btn-primary" onclick="toggleModal('addSubjectModal', true)"><i
                            class="fa-solid fa-plus"></i> New Subject</button>
                </div>
                <div class="content-scroll">
                    <?php if (empty($subjects_list)): ?>
                        <!-- EMPTY STATE FOR SUBJECTS -->
                        <div class="empty-state">
                            <i class="fa-solid fa-book-open-reader"></i>
                            <h4>No Subjects Found</h4>
                            <p>You haven't added any subjects yet. Click "New Subject" to get started.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Subject</th>
                                        <th>Day & Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subjects_list as $s): ?>
                                        <tr>
                                            <td><span
                                                    style="font-weight:800; color:var(--primary);"><?php echo $s['sched_code']; ?></span>
                                            </td>
                                            <td><strong><?php echo $s['subject_code']; ?></strong><br><small><?php echo $s['subject_name']; ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                // UPDATE 1: Display Start AND End Time
                                                $startTime = date("h:i A", strtotime($s['start_time']));
                                                $endTime = date("h:i A", strtotime($s['end_time']));
                                                echo $s['sched_day'] . " <small class='text-muted'>$startTime - $endTime</small>";
                                                ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-outline btn-icon"
                                                    onclick='openEditSubject(<?php echo json_encode($s); ?>)'><i
                                                        class="fa-solid fa-pen-to-square"></i></button>
                                                <button class="btn btn-danger btn-icon"
                                                    onclick='askDelete(<?php echo $s['subject_id']; ?>, "subject")'><i
                                                        class="fa-solid fa-trash-can"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- SECTIONS -->
            <section id="sections" class="section-card content-section hidden">
                <div id="sectionListView" class="content-scroll">
                    <div class="section-header">
                        <div>
                            <h2>Sections</h2>
                            <p>Academic year groups</p>
                        </div>
                        <button class="btn btn-primary" onclick="toggleModal('addSectionModal', true)"><i
                                class="fa-solid fa-plus"></i> Add Section</button>
                    </div>

                    <?php if (empty($sections_arr)): ?>
                        <!-- EMPTY STATE FOR SECTIONS -->
                        <div class="empty-state">
                            <i class="fa-solid fa-users-rectangle"></i>
                            <h4>No Sections Added</h4>
                            <p>Create a section to organize your students.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Section Name</th>
                                        <th>SY Range</th>
                                        <th>Semester</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sections_arr as $sec): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($sec['section_name']); ?></strong></td>
                                            <td><?php echo $sec['sy_start'] . '-' . $sec['sy_end']; ?></td>
                                            <td><?php echo $sec['semester']; ?></td>
                                            <td>
                                                <button class="btn btn-primary"
                                                    onclick='viewSection(<?php echo json_encode($sec); ?>)'>Students</button>
                                                <button class="btn btn-outline btn-icon"
                                                    onclick='openEditSection(<?php echo json_encode($sec); ?>)'><i
                                                        class="fa-solid fa-pen-to-square"></i></button>
                                                <button class="btn btn-danger btn-icon"
                                                    onclick='askDelete(<?php echo $sec['section_id']; ?>, "section")'><i
                                                        class="fa-solid fa-trash-can"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="sectionDetailView" class="hidden content-scroll">
                    <div class="section-header">
                        <div>
                            <h2 id="sectTitle"></h2>
                            <p id="sectMeta"></p>
                        </div>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <div class="search-container"><i class="fa-solid fa-magnifying-glass"></i><input type="text"
                                    id="studentSearchInput" placeholder="Search student..."
                                    onkeyup="filterStudentTable()"></div>
                            <button class="btn btn-outline" onclick="showSectionList()">Back</button>
                            <button class="btn btn-primary" onclick="toggleModal('addStudentModal', true)">Add
                                Student</button>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID Number</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentTableBody"></tbody>
                        </table>
                        <div id="noStudentsMsg" class="empty-state hidden">
                            <i class="fa-solid fa-user-xmark"></i>
                            <h4>No Students</h4>
                            <p>This section is empty.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ATTENDANCE -->
            <section id="attendance" class="section-card content-section hidden">
                <div id="attSectionList" class="content-scroll">
                    <div class="section-header">
                        <div>
                            <h2>Attendance Tracking</h2>
                            <p>Select section to record</p>
                        </div>
                    </div>
                    <?php if (empty($sections_arr)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-clipboard-question"></i>
                            <h4>No Sections</h4>
                            <p>You need to create sections before tracking attendance.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid-cards">
                            <?php foreach ($sections_arr as $sec): ?>
                                <div class="grid-item" onclick='prepareAttendanceSheet(<?php echo json_encode($sec); ?>)'>
                                    <h3><?php echo $sec['section_name']; ?></h3>
                                    <p style="color:var(--text-muted); margin-bottom:15px;"><?php echo $sec['semester']; ?></p>
                                    <button class="btn btn-primary" style="width:100%">Mark Attendance</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div id="attSpreadsheet" class="hidden content-scroll">
                    <div class="section-header">
                        <div>
                            <h2 id="attTitle"></h2>
                            <p id="attMeta"></p>
                        </div>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <div class="search-container"><i class="fa-solid fa-search"></i><input type="text"
                                    id="attSearchInput" placeholder="Jump to student..."
                                    onkeyup="searchScroll(event, 'attTableBody')"></div>
                            <button class="btn btn-outline" onclick="backToAttSections()">Exit</button>
                        </div>
                    </div>
                    <div class="sheet-container">
                        <table class="sheet-table">
                            <thead>
                                <tr id="attTableHeader">
                                    <th class="fixed-col">Student Name</th>
                                </tr>
                            </thead>
                            <tbody id="attTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- GRADES (QUIZZES & EXAMS) -->
            <section id="grades" class="section-card content-section hidden">
                <div id="gradeSectionList" class="content-scroll">
                    <div class="section-header">
                        <div>
                            <h2>Quizzes & Exams</h2>
                            <p>Record student scores</p>
                        </div>
                    </div>
                    <?php if (empty($sections_arr)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-file-pen"></i>
                            <h4>No Sections</h4>
                            <p>You need to create sections before recording grades.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid-cards">
                            <?php foreach ($sections_arr as $sec): ?>
                                <div class="grid-item" onclick='prepareGradeSheet(<?php echo json_encode($sec); ?>)'>
                                    <h3><?php echo $sec['section_name']; ?></h3>
                                    <p style="color:var(--text-muted); margin-bottom:15px;"><?php echo $sec['semester']; ?></p>
                                    <button class="btn btn-primary" style="width:100%">Record Grades</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div id="gradeSpreadsheet" class="hidden content-scroll">
                    <div class="section-header">
                        <div>
                            <h2 id="gradeTitle"></h2>
                            <p id="gradeMeta"></p>
                        </div>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <div class="search-container"><i class="fa-solid fa-search"></i><input type="text"
                                    placeholder="Jump to student..." onkeyup="searchScroll(event, 'gradeTableBody')">
                            </div>
                            <button class="btn btn-outline" onclick="backToGradeSections()">Exit</button>
                        </div>
                    </div>
                    <div class="sheet-container">
                        <table class="sheet-table">
                            <thead>
                                <tr id="gradeTableHeader">
                                    <th class="fixed-col">Student Name</th>
                                    <!-- JS will fill Q1-Q10, Midterm, Finals -->
                                </tr>
                            </thead>
                            <tbody id="gradeTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- ANALYTICS -->
            <section id="analytics" class="section-card content-section hidden">
                <!-- VIEW 1: Main Menu -->
                <div id="analyticsMenu" class="content-scroll">
                    <div class="section-header">
                        <div>
                            <h2>Data Analytics</h2>
                            <p>Select an option to proceed</p>
                        </div>
                    </div>
                    <div class="grid-cards">
                        <!-- Analytics Cards (No Changes Needed here) -->
                        <div class="grid-item analytics-card" onclick="selectAnalyticsType('low_attendance')">
                            <i class="fa-solid fa-user-clock analytics-opt-icon" style="color:#ef4444;"></i>
                            <h3>Low Attendance</h3>
                            <p style="color:var(--text-muted); font-size:0.85rem;">Students with < 75% attendance</p>
                        </div>
                        <div class="grid-item analytics-card" onclick="selectAnalyticsType('student_averages')">
                            <i class="fa-solid fa-chart-bar analytics-opt-icon" style="color:#0ea5e9;"></i>
                            <h3>Overall Grade</h3>
                            <p style="color:var(--text-muted); font-size:0.85rem;">View Student Grades</p>
                        </div>
                        <div class="grid-item analytics-card" onclick="selectAnalyticsType('ranking')">
                            <i class="fa-solid fa-trophy analytics-opt-icon" style="color:#eab308;"></i>
                            <h3>Ranking</h3>
                            <p style="color:var(--text-muted); font-size:0.85rem;">Top students by weighted mean</p>
                        </div>
                        <div class="grid-item analytics-card" onclick="selectAnalyticsType('attendance_chart')">
                            <i class="fa-solid fa-chart-pie analytics-opt-icon" style="color:#8b5cf6;"></i>
                            <h3>Attendance Chart</h3>
                            <p style="color:var(--text-muted); font-size:0.85rem;">Percentage by status</p>
                        </div>
                        <div class="grid-item analytics-card" onclick="selectAnalyticsType('late_absent')">
                            <i class="fa-solid fa-business-time analytics-opt-icon" style="color:#f97316;"></i>
                            <h3>Late / Absent</h3>
                            <p style="color:var(--text-muted); font-size:0.85rem;">Consistent late & absent records</p>
                        </div>
                    </div>
                </div>

                <!-- VIEW 2: Section Picker -->
                <div id="analyticsSectionPicker" class="hidden content-scroll">
                    <div class="section-header">
                        <div>
                            <h2 id="anaPickerTitle">Select Section</h2>
                            <p>Choose a section to analyze</p>
                        </div>
                        <button class="btn btn-outline" onclick="closeAnalyticsPicker()">Back</button>
                    </div>
                    <?php if (empty($sections_arr)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-chart-simple"></i>
                            <h4>No Data</h4>
                            <p>Analytics requires sections to be created first.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid-cards">
                            <?php foreach ($sections_arr as $sec): ?>
                                <div class="grid-item" onclick='loadAnalyticsData(<?php echo json_encode($sec); ?>)'>
                                    <h3><?php echo $sec['section_name']; ?></h3>
                                    <p style="color:var(--text-muted); margin-bottom:15px;"><?php echo $sec['semester']; ?></p>
                                    <button class="btn btn-primary" style="width:100%">Select</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- VIEW 3: Results -->
                <div id="analyticsResult" class="hidden content-scroll">
                    <div class="section-header">
                        <div>
                            <h2 id="anaResultTitle">Results</h2>
                            <p id="anaResultMeta">Analysis Data</p>
                        </div>
                        <div style="display:flex; gap:10px;">
                            <div class="search-container"><i class="fa-solid fa-magnifying-glass"></i><input type="text"
                                    id="AnastudentSearchInput" placeholder="Search student..."
                                    onkeyup="filterStudentTableAnalytics()"></div>
                            <button class="btn btn-primary" onclick="window.print()"><i class="fa-solid fa-print"></i>
                                Print</button>
                            <button class="btn btn-outline" onclick="backToAnaPicker()">Change Section</button>
                            <button class="btn btn-outline" onclick="closeAnalyticsPicker()">Menu</button>
                        </div>
                    </div>

                    <div id="anaContentArea" class="table-wrap">
                        <table id="anaResultTable">
                            <!-- JS Fills This -->
                        </table>
                    </div>

                    <div id="anaChartArea" class="chart-container hidden">
                        <canvas id="attendanceChartCanvas"></canvas>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- MODALS -->

    <!-- Add Subject -->
    <div id="addSubjectModal" class="modal">
        <div class="modal-content">
            <div class="modal-banner">
                <h3>New Subject</h3><button class="modal-close"
                    onclick="toggleModal('addSubjectModal', false)">&times;</button>
            </div>
            <form onsubmit="handleFormSubmit(event, 'php/subjects/add_subject.php', 'addSubjectModal')">
                <div class="form-grid">
                    <div class="form-group" style="grid-column: span 2;"><label>Sched Code</label><input type="text"
                            name="schedCode" value="<?php echo $generatedSchedCode; ?>" readonly></div>
                    <div class="form-group"><label>Code</label><input type="text" name="subjectCode" required></div>
                    <div class="form-group"><label>Name</label><input type="text" name="subjectName" required></div>
                    <div class="form-group"><label>Start</label><input type="time" name="startTime" required></div>
                    <div class="form-group"><label>End</label><input type="time" name="endTime" required></div>
                    <div class="form-group" style="grid-column: span 2;"><label>Day</label><select name="schedDay">
                            <option>Monday</option>
                            <option>Tuesday</option>
                            <option>Wednesday</option>
                            <option>Thursday</option>
                            <option>Friday</option>
                            <option>Saturday</option>
                        </select></div>
                </div>
                <div style="padding:20px; text-align:right;"><button class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>

    <!-- Edit Subject -->
    <div id="editSubjectModal" class="modal">
        <div class="modal-content">
            <div class="modal-banner">
                <h3>Update Subject</h3><button class="modal-close"
                    onclick="toggleModal('editSubjectModal', false)">&times;</button>
            </div>
            <form onsubmit="handleFormSubmit(event, 'php/subjects/update_subject.php', 'editSubjectModal')"><input
                    type="hidden" name="subject_id" id="edit_sub_id">
                <div class="form-grid">
                    <div class="form-group" style="grid-column: span 2;"><label>Sched Code</label><input type="text"
                            name="schedCode" id="edit_sub_sched" readonly></div>
                    <div class="form-group"><label>Code</label><input type="text" name="subjectCode" id="edit_sub_code"
                            required></div>
                    <div class="form-group"><label>Name</label><input type="text" name="subjectName" id="edit_sub_name"
                            required></div>
                    <div class="form-group"><label>Start</label><input type="time" name="startTime" id="edit_sub_start"
                            required></div>
                    <div class="form-group"><label>End</label><input type="time" name="endTime" id="edit_sub_end"
                            required></div>
                    <div class="form-group" style="grid-column: span 2;"><label>Day</label><select name="schedDay"
                            id="edit_sub_day">
                            <option>Monday</option>
                            <option>Tuesday</option>
                            <option>Wednesday</option>
                            <option>Thursday</option>
                            <option>Friday</option>
                            <option>Saturday</option>
                        </select></div>
                </div>
                <div style="padding:20px; text-align:right;"><button class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>

    <!-- Add Section -->
    <div id="addSectionModal" class="modal">
        <div class="modal-content">
            <div class="modal-banner">
                <h3>New Section</h3><button class="modal-close"
                    onclick="toggleModal('addSectionModal', false)">&times;</button>
            </div>
            <form onsubmit="handleFormSubmit(event, 'php/sections/add_section.php', 'addSectionModal')">
                <div class="form-grid">
                    <div class="form-group" style="grid-column: span 2;"><label>Name</label><input type="text"
                            name="sectionName" required></div>
                    <div class="form-group"><label>Start Year</label><input type="number" name="syStart" value="2024">
                    </div>
                    <div class="form-group"><label>End Year</label><input type="number" name="syEnd" value="2025"></div>
                    <div class="form-group" style="grid-column: span 2;"><label>Semester</label><select name="semester">
                            <option>1st Semester</option>
                            <option>2nd Semester</option>
                            <option>Summer</option>
                        </select></div>
                </div>
                <div style="padding:20px; text-align:right;"><button class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>

    <!-- Edit Section -->
    <div id="editSectionModal" class="modal">
        <div class="modal-content">
            <div class="modal-banner">
                <h3>Update Section</h3><button class="modal-close"
                    onclick="toggleModal('editSectionModal', false)">&times;</button>
            </div>
            <form onsubmit="handleFormSubmit(event, 'php/sections/update_section.php', 'editSectionModal')"><input
                    type="hidden" name="section_id" id="edit_sec_id">
                <div class="form-grid">
                    <div class="form-group" style="grid-column: span 2;"><label>Name</label><input type="text"
                            name="sectionName" id="edit_sec_name" required></div>
                    <div class="form-group"><label>Start Year</label><input type="number" name="syStart"
                            id="edit_sec_start"></div>
                    <div class="form-group"><label>End Year</label><input type="number" name="syEnd" id="edit_sec_end">
                    </div>
                    <div class="form-group" style="grid-column: span 2;"><label>Semester</label><select name="semester"
                            id="edit_sec_sem">
                            <option>1st Semester</option>
                            <option>2nd Semester</option>
                            <option>Summer</option>
                        </select></div>
                </div>
                <div style="padding:20px; text-align:right;"><button class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>

    <!-- Add Student -->
    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <div class="modal-banner">
                <h3>New Student</h3><button class="modal-close"
                    onclick="toggleModal('addStudentModal', false)">&times;</button>
            </div>
            <form onsubmit="handleStudentAdd(event)">
                <input type="hidden" name="section_id" id="add_student_sec_id">
                <div class="form-grid">
                    <div class="form-group" style="grid-column: span 2;"><label>ID Number</label><input type="text"
                            name="studentIdNumber" required></div>
                    <div class="form-group"><label>First</label><input type="text" name="firstName" required></div>
                    <div class="form-group"><label>Last</label><input type="text" name="lastName" required></div>
                    <div class="form-group"><label>M.I.</label><input type="text" name="middleInitial" maxlength="2"
                            placeholder="Optional"></div>
                    <div class="form-group"><label>Sex</label><select name="sex">
                            <option>Male</option>
                            <option>Female</option>
                        </select></div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label>Course / Program</label>
                        <div style="display:flex; gap:5px;">
                            <input type="text" name="course" id="add_student_course_display" required readonly
                                placeholder="Click Select..." style="background:#f1f5f9; cursor:not-allowed;">
                            <button type="button" class="btn btn-outline"
                                onclick="openCourseModal('add')">Select</button>
                        </div>
                    </div>

                    <div class="form-group" style="grid-column: span 2;"><label>Status</label><select name="status">
                            <option>Regular</option>
                            <option>Irregular</option>
                        </select></div>
                </div>
                <div style="padding:20px; text-align:right;"><button class="btn btn-primary">Add</button></div>
            </form>
        </div>
    </div>

    <!-- Edit Student -->
    <div id="editStudentModal" class="modal">
        <div class="modal-content">
            <div class="modal-banner">
                <h3>Update Info</h3><button class="modal-close"
                    onclick="toggleModal('editStudentModal', false)">&times;</button>
            </div>
            <form onsubmit="handleFormSubmit(event, 'php/sections/update_student.php', 'editStudentModal', true)">
                <input type="hidden" name="student_id" id="edit_stu_id">
                <div class="form-grid">
                    <div class="form-group" style="grid-column: span 2;"><label>ID Number</label><input type="text"
                            name="studentIdNumber" id="edit_stu_num" required></div>
                    <div class="form-group"><label>First</label><input type="text" name="firstName" id="edit_stu_fname"
                            required></div>
                    <div class="form-group"><label>Last</label><input type="text" name="lastName" id="edit_stu_lname"
                            required></div>
                    <div class="form-group"><label>M.I.</label><input type="text" name="middleInitial" id="edit_stu_mi"
                            maxlength="2" placeholder="Optional"></div>
                    <div class="form-group"><label>Sex</label><select name="sex" id="edit_stu_sex">
                            <option>Male</option>
                            <option>Female</option>
                        </select></div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label>Course / Program</label>
                        <div style="display:flex; gap:5px;">
                            <input type="text" name="course" id="edit_stu_course" required readonly
                                style="background:#f1f5f9;">
                            <button type="button" class="btn btn-outline"
                                onclick="openCourseModal('edit')">Change</button>
                        </div>
                    </div>

                    <div class="form-group" style="grid-column: span 2;"><label>Status</label><select name="status"
                            id="edit_stu_status">
                            <option>Regular</option>
                            <option>Irregular</option>
                        </select></div>
                </div>
                <div style="padding:20px; text-align:right;"><button class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>

    <!-- STUDENT PROFILE MODAL -->
    <div id="studentProfileModal" class="modal">
        <div class="modal-content" style="max-width:550px; max-height:90vh; overflow:hidden;">
            <div class="modal-banner" style="text-align:center; padding-bottom:35px;">
                <button class="modal-close" onclick="toggleModal('studentProfileModal', false)">&times;</button>
                <div class="profile-pic-container">
                    <img id="pProfilePic"
                        src="https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png?20150327203541"
                        class="profile-pic">
                </div>
                <h3 id="pName" style="margin-bottom:5px;">Name</h3>
                <p id="pIdNum"
                    style="opacity:0.9; font-size:0.9rem; font-weight:700; background:rgba(255,255,255,0.2); display:inline-block; padding:2px 10px; border-radius:6px;">
                </p>
            </div>
            <div style="padding:25px;">
                <div class="p-info-box">
                    <div><label class="form-group"><label>Program</label></label><span id="pCourse"
                            style="font-weight:700"></span></div>
                    <div><label class="form-group"><label>Sex</label></label><span id="pSex"
                            style="font-weight:700"></span></div>
                </div>

                <div style="margin-bottom:20px; border-top:1px solid #eef2f6; padding-top:20px;">
                    <label class="form-group"><label>Quick Enroll</label></label>
                    <form id="enrollForm" style="display:flex; gap:10px;" onsubmit="handleEnroll(event)">
                        <input type="hidden" name="student_id" id="enroll_sid">
                        <select name="subject_id" required
                            style="flex-grow:1; padding:10px; border-radius:10px; border:1px solid var(--primary-light);">
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects_list as $sl): ?>
                                <!-- UPDATE 2: Show Subject Name in Quick Enroll -->
                                <option value="<?php echo $sl['subject_id']; ?>">
                                    <?php echo $sl['subject_code'] . ' - ' . $sl['subject_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary">Enroll</button>
                    </form>
                </div>

                <label class="form-group"><label>Load</label></label>
                <div id="pSubjectListWrapper" style="max-height: 220px; overflow-y: auto; padding-right: 6px;">
                    <div id="pSubjectList" style="display:grid; gap:8px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirm -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width:380px; text-align:center; padding:30px;">
            <div style="color:#ef4444; font-size:3.5rem; margin-bottom:15px;"><i
                    class="fa-solid fa-circle-exclamation"></i></div>
            <h3>Delete Permanently?</h3>
            <p style="color:var(--text-muted); font-size:0.9rem; margin:10px 0 25px;">This action cannot be undone.</p>
            <div style="display:flex; gap:10px; justify-content:center;">
                <button class="btn btn-outline" onclick="toggleModal('deleteModal', false)">Cancel</button>
                <button class="btn btn-danger" id="confirmDeleteBtn">Delete Now</button>
            </div>
        </div>
    </div>

    <script src="js/instructor-home.js"></script>
</body>

</html>