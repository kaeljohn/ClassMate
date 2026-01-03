<?php
session_start();
include 'php/db_connect.php';

if (!isset($_SESSION['instructor_name'])) {
    header("Location: instructor-login.php");
    exit();
}

$current_instructor = $_SESSION['instructor_name'];
$inst = $current_instructor;

// Generate unique Sched Code
$currentYear = date("Y");
$sql_last_code = "SELECT sched_code FROM subjects WHERE sched_code LIKE '$currentYear%' ORDER BY sched_code DESC LIMIT 1";
$res_last = $conn->query($sql_last_code);

if ($res_last && $res_last->num_rows > 0) {
    $last_row = $res_last->fetch_assoc();
    $last_seq = (int)substr($last_row['sched_code'], 4);
    $new_seq = str_pad($last_seq + 1, 5, '0', STR_PAD_LEFT);
} else {
    $new_seq = '00001';
}
$generatedSchedCode = $currentYear . $new_seq;

// Data Fetching
$res_subs = $conn->query("SELECT * FROM subjects WHERE instructor_id = '$inst' ORDER BY created_at DESC");
$subjects_list = [];
if ($res_subs && $res_subs->num_rows > 0) {
    while($row = $res_subs->fetch_assoc()) {
        $subjects_list[] = $row;
    }
}
$res_sections = $conn->query("SELECT * FROM sections WHERE instructor_id = '$inst' ORDER BY created_at DESC");
$sections_arr = [];
while($row = $res_sections->fetch_assoc()) { $sections_arr[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ClassMate | Sky Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary: #0ea5e9;
            --primary-dark: #0369a1;
            --primary-light: #7dd3fc;
            --bg-gradient: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            --glass: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(14, 165, 233, 0.15);
            --text-main: #0c4a6e;
            --text-muted: #64748b;
            --shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.1);
            --att-present: #22c55e;
            --att-late: #f97316;
            --att-excused: #1e293b;
            --att-absent: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg-gradient); color: var(--text-main); min-height: 100vh; overflow-x: hidden; }

        .app-container { display: flex; min-height: 100vh; padding: 20px; gap: 20px; }

        /* Sidebar */
        .sidebar { width: 260px; background: var(--glass); backdrop-filter: blur(12px); border: 1px solid var(--glass-border); border-radius: 24px; padding: 30px 20px; display: flex; flex-direction: column; box-shadow: var(--shadow); position: sticky; top: 20px; height: calc(100vh - 40px); }
        .logo { display: flex; align-items: center; gap: 12px; font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-bottom: 40px; padding-left: 10px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 15px; border-radius: 12px; color: var(--text-muted); text-decoration: none; font-weight: 600; transition: 0.3s; margin-bottom: 5px; cursor: pointer; }
        .nav-item:hover { background: #f0f9ff; color: var(--primary); }
        .nav-item.active { background: var(--primary); color: white; box-shadow: 0 8px 16px -4px rgba(14, 165, 233, 0.4); }

        /* Main Content */
        .main-content { flex-grow: 1; display: flex; flex-direction: column; gap: 20px; }
        .top-nav { background: var(--glass); backdrop-filter: blur(12px); border: 1px solid var(--glass-border); border-radius: 16px; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow); }
        .avatar { width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), #38bdf8); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; }

        /* Sections */
        .section-card { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 24px; padding: 30px; box-shadow: var(--shadow); flex-grow: 1; animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }

        /* Tables */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        th { padding: 12px 15px; text-align: left; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; }
        td { padding: 15px; background: rgba(255, 255, 255, 0.6); border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); font-size: 0.9rem; }
        td:first-child { border-left: 1px solid var(--glass-border); border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
        td:last-child { border-right: 1px solid var(--glass-border); border-top-right-radius: 12px; border-bottom-right-radius: 12px; }

        /* Buttons & Badges */
        .btn { padding: 10px 18px; border-radius: 10px; border: none; font-weight: 700; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; font-size: 0.85rem; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-outline { background: white; border: 1px solid var(--primary-light); color: var(--primary); }
        .btn-danger { background: #fee2e2; color: #ef4444; }
        .btn-icon { padding: 8px; }
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; }
        .badge-reg { background: #dcfce7; color: #166534; }
        .badge-irr { background: #fff7ed; color: #9a3412; }

        /* Search */
        .search-container { position: relative; width: 300px; }
        .search-container input { width: 100%; padding: 10px 15px 10px 35px; border-radius: 12px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.5); outline: none; }
        .search-container i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }

        /* Attendance Grid */
        .att-table-container { overflow-x: auto; border-radius: 16px; border: 1px solid #e2e8f0; max-height: 60vh; }
        .att-table { border-spacing: 0; width: 100%; }
        .att-table th { position: sticky; top: 0; z-index: 20; background: #f8fafc; text-align: center; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; }
        .att-table td { padding: 8px; text-align: center; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; background: white; }
        .att-table .student-col { text-align: left; min-width: 220px; position: sticky; left: 0; z-index: 30; background: #f8fafc; border-right: 2px solid #e2e8f0; padding-left: 15px; }
        
        .att-cell { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; color: white; font-size: 0.75rem; cursor: pointer; border: none; }
        .att-cell.empty { background: #f1f5f9; color: #94a3b8; }
        .att-cell.p { background: var(--att-present); }
        .att-cell.l { background: var(--att-late); }
        .att-cell.e { background: var(--att-excused); }
        .att-cell.a { background: var(--att-absent); }

        .att-picker { position: absolute; display: none; background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); z-index: 5000; padding: 8px; flex-direction: column; gap: 5px; border: 1px solid var(--glass-border); }
        .att-opt { display: flex; align-items: center; gap: 10px; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 600; color: var(--text-main); }
        .att-opt:hover { background: #f0f9ff; color: var(--primary); }
        .att-opt .dot { width: 10px; height: 10px; border-radius: 50%; }

        .search-highlight { background: #fef08a !important; transition: background 0.5s; }

        /* Modals */
        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px); display: none; justify-content: center; align-items: center; z-index: 3000; }
        .modal-content { background: white; width: 95%; max-width: 500px; border-radius: 24px; overflow: hidden; animation: zoomIn 0.2s ease; }
        @keyframes zoomIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-banner { background: linear-gradient(135deg, var(--primary), #38bdf8); padding: 25px; color: white; position: relative; }
        .modal-close { position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.2); border: none; color: white; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; padding: 25px; }
        .form-group label { display: block; font-size: 0.7rem; font-weight: 800; color: var(--primary-dark); text-transform: uppercase; margin-bottom: 6px; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc; }

        .p-info-box { background: #f0f9ff; padding: 15px; border-radius: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
        .hidden { display: none; }
    </style>
</head>
<body>

    <!-- Attendance Picker -->
    <div id="attOptionsPicker" class="att-picker">
        <div class="att-opt" onclick="selectAttStatus('P')"><span class="dot" style="background:var(--att-present)"></span> Present</div>
        <div class="att-opt" onclick="selectAttStatus('L')"><span class="dot" style="background:var(--att-late)"></span> Late</div>
        <div class="att-opt" onclick="selectAttStatus('E')"><span class="dot" style="background:var(--att-excused)"></span> Excused</div>
        <div class="att-opt" onclick="selectAttStatus('A')"><span class="dot" style="background:var(--att-absent)"></span> Absent</div>
        <div class="att-opt" onclick="selectAttStatus('NONE')" style="border-top:1px solid #eee; color:#94a3b8;"><i class="fa-solid fa-eraser"></i> Clear</div>
    </div>

    <div class="app-container">
        <aside class="sidebar">
            <div class="logo"><i class="fa-solid fa-cloud"></i> <span>ClassMate</span></div>
            <nav style="flex-grow:1">
                <a class="nav-item active" data-target="subjects"><i class="fa-solid fa-book-open"></i> <span>Subjects</span></a>
                <a class="nav-item" data-target="sections"><i class="fa-solid fa-layer-group"></i> <span>Sections</span></a>
                <a class="nav-item" data-target="attendance"><i class="fa-solid fa-calendar-check"></i> <span>Attendance</span></a>
            </nav>
            <a href="php/logout.php" class="nav-item" style="color:#ef4444;"><i class="fa-solid fa-door-open"></i> <span>Logout</span></a>
        </aside>

        <main class="main-content">
            <header class="top-nav">
                <h1 id="pageTitle" style="font-size: 1.1rem; font-weight: 800; color: var(--primary-dark);">My Subjects</h1>
                <div style="display:flex; align-items:center; gap:15px;">
                    <div style="text-align:right;">
                        <p style="font-weight: 800; font-size: 0.9rem;"><?php echo htmlspecialchars($current_instructor); ?></p>
                        <p style="font-size: 0.75rem; color: var(--text-muted);">Instructor</p>
                    </div>
                    <div class="avatar"><?php echo strtoupper(substr($current_instructor, 0, 1)); ?></div>
                </div>
            </header>

            <!-- SUBJECTS -->
            <section id="subjects" class="section-card content-section">
                <div class="section-header">
                    <div><h2>Subject Load</h2><p>Manage your schedules</p></div>
                    <button class="btn btn-primary" onclick="toggleModal('addSubjectModal', true)"><i class="fa-solid fa-plus"></i> New Subject</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Code</th><th>Subject</th><th>Day & Time</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($subjects_list as $s): ?>
                            <tr>
                                <td><span style="font-weight:800; color:var(--primary);"><?php echo $s['sched_code']; ?></span></td>
                                <td><strong><?php echo $s['subject_code']; ?></strong><br><small><?php echo $s['subject_name']; ?></small></td>
                                <td><?php echo $s['sched_day']; ?><br><small><?php echo date("h:i A", strtotime($s['start_time'])); ?></small></td>
                                <td>
                                    <button class="btn btn-outline btn-icon" onclick='openEditSubject(<?php echo json_encode($s); ?>)'><i class="fa-solid fa-pen-to-square"></i></button>
                                    <button class="btn btn-outline btn-icon" onclick='askDelete(<?php echo $s['subject_id']; ?>, "subject")'><i class="fa-solid fa-trash-can"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- SECTIONS -->
            <section id="sections" class="section-card content-section hidden">
                <div id="sectionListView">
                    <div class="section-header">
                        <div><h2>Sections</h2><p>Academic year groups</p></div>
                        <button class="btn btn-primary" onclick="toggleModal('addSectionModal', true)"><i class="fa-solid fa-plus"></i> Add Section</button>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Section Name</th><th>SY Range</th><th>Semester</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($sections_arr as $sec): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($sec['section_name']); ?></strong></td>
                                    <td><?php echo $sec['sy_start'].'-'.$sec['sy_end']; ?></td>
                                    <td><?php echo $sec['semester']; ?></td>
                                    <td>
                                        <button class="btn btn-primary" onclick='viewSection(<?php echo json_encode($sec); ?>)'>Students</button>
                                        <button class="btn btn-outline btn-icon" onclick='openEditSection(<?php echo json_encode($sec); ?>)'><i class="fa-solid fa-pen-to-square"></i></button>
                                        <button class="btn btn-danger btn-icon" onclick='askDelete(<?php echo $sec['section_id']; ?>, "section")'><i class="fa-solid fa-trash-can"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="sectionDetailView" class="hidden">
                    <div class="section-header">
                        <div><h2 id="activeSecTitle"></h2><p id="activeSecMeta"></p></div>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <div class="search-container">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="studentSearchInput" placeholder="Search student..." onkeyup="filterStudentTable()">
                            </div>
                            <button class="btn btn-outline" onclick="showSectionList()"><i class="fa-solid fa-arrow-left"></i> Back</button>
                            <button class="btn btn-primary" onclick="toggleModal('addStudentModal', true)"><i class="fa-solid fa-user-plus"></i> Add Student</button>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>ID Number</th><th>Name</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody id="studentTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- ATTENDANCE -->
            <section id="attendance" class="section-card content-section hidden">
                <div id="attSectionList">
                    <div class="section-header"><h2>Mark Attendance</h2></div>
                    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
                        <?php foreach($sections_arr as $sec): ?>
                        <div style="background:white; padding:25px; border-radius:18px; border:1px solid var(--glass-border); box-shadow:var(--shadow);">
                            <h3><?php echo $sec['section_name']; ?></h3>
                            <p style="margin-bottom:20px; color:var(--text-muted);"><?php echo $sec['semester']; ?></p>
                            <button class="btn btn-primary" style="width:100%" onclick='openAttendanceSheet(<?php echo json_encode($sec); ?>)'>Open Sheet</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="attSpreadsheet" class="hidden">
                    <div class="section-header">
                        <div><h2 id="attTitle"></h2><p id="attMeta" style="color:var(--text-muted); font-size:0.9rem;"></p></div>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <div class="search-container"><i class="fa-solid fa-search"></i><input type="text" id="attSearchInput" placeholder="Jump to student..." onkeyup="searchAndHighlightStudent(event)"></div>
                            <button class="btn btn-outline" onclick="backToAttSections()"><i class="fa-solid fa-arrow-left"></i> Exit</button>
                        </div>
                    </div>
                    <div class="att-table-container">
                        <table class="att-table">
                            <thead><tr id="attTableHeader"><th class="student-col">Student Name</th></tr></thead>
                            <tbody id="attTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- MODALS -->
    <!-- Add Subject -->
    <div id="addSubjectModal" class="modal"><div class="modal-content"><div class="modal-banner"><h3>New Subject</h3><button class="modal-close" onclick="toggleModal('addSubjectModal', false)">&times;</button></div>
        <form onsubmit="handleFormSubmit(event, 'php/add_subject.php', 'addSubjectModal')">
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;"><label>Sched Code</label><input type="text" name="schedCode" value="<?php echo $generatedSchedCode; ?>" readonly></div>
                <div class="form-group"><label>Code</label><input type="text" name="subjectCode" required></div>
                <div class="form-group"><label>Name</label><input type="text" name="subjectName" required></div>
                <div class="form-group"><label>Start</label><input type="time" name="startTime" required></div>
                <div class="form-group"><label>End</label><input type="time" name="endTime" required></div>
                <div class="form-group" style="grid-column: span 2;"><label>Day</label><select name="schedDay"><option>Monday</option><option>Tuesday</option><option>Wednesday</option><option>Thursday</option><option>Friday</option></select></div>
            </div>
            <div style="padding:20px; text-align:right;"><button class="btn btn-primary">Save</button></div>
        </form>
    </div></div>

    <!-- Edit Subject -->
    <div id="editSubjectModal" class="modal"><div class="modal-content"><div class="modal-banner"><h3>Update Subject</h3><button class="modal-close" onclick="toggleModal('editSubjectModal', false)">&times;</button></div>
        <form onsubmit="handleFormSubmit(event, 'php/update_subject.php', 'editSubjectModal')">
            <input type="hidden" name="subject_id" id="edit_sub_id">
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;"><label>Sched Code</label><input type="text" name="schedCode" id="edit_sub_sched" readonly></div>
                <div class="form-group"><label>Code</label><input type="text" name="subjectCode" id="edit_sub_code" required></div>
                <div class="form-group"><label>Name</label><input type="text" name="subjectName" id="edit_sub_name" required></div>
                <div class="form-group"><label>Start</label><input type="time" name="startTime" id="edit_sub_start" required></div>
                <div class="form-group"><label>End</label><input type="time" name="endTime" id="edit_sub_end" required></div>
                <div class="form-group" style="grid-column: span 2;"><label>Day</label><select name="schedDay" id="edit_sub_day"><option>Monday</option><option>Tuesday</option><option>Wednesday</option><option>Thursday</option><option>Friday</option></select></div>
            </div>
            <div style="padding:20px; text-align:right;"><button class="btn btn-primary">Save Changes</button></div>
        </form>
    </div></div>

    <!-- Add Section -->
    <div id="addSectionModal" class="modal"><div class="modal-content"><div class="modal-banner"><h3>New Section</h3><button class="modal-close" onclick="toggleModal('addSectionModal', false)">&times;</button></div>
        <form onsubmit="handleFormSubmit(event, 'php/add_section.php', 'addSectionModal')">
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;"><label>Name</label><input type="text" name="sectionName" required></div>
                <div class="form-group"><label>Start Year</label><input type="number" name="syStart" value="2024"></div>
                <div class="form-group"><label>End Year</label><input type="number" name="syEnd" value="2025"></div>
                <div class="form-group" style="grid-column: span 2;"><label>Semester</label><select name="semester"><option>1st Semester</option><option>2nd Semester</option></select></div>
            </div>
            <div style="padding:20px; text-align:right;"><button class="btn btn-primary">Save</button></div>
        </form>
    </div></div>

    <!-- Edit Section -->
    <div id="editSectionModal" class="modal"><div class="modal-content"><div class="modal-banner"><h3>Update Section</h3><button class="modal-close" onclick="toggleModal('editSectionModal', false)">&times;</button></div>
        <form onsubmit="handleFormSubmit(event, 'php/update_section.php', 'editSectionModal')">
            <input type="hidden" name="section_id" id="edit_sec_id">
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;"><label>Name</label><input type="text" name="sectionName" id="edit_sec_name" required></div>
                <div class="form-group"><label>Start Year</label><input type="number" name="syStart" id="edit_sec_start" required></div>
                <div class="form-group"><label>End Year</label><input type="number" name="syEnd" id="edit_sec_end" required></div>
                <div class="form-group" style="grid-column: span 2;"><label>Semester</label><select name="semester" id="edit_sec_sem"><option>1st Semester</option><option>2nd Semester</option></select></div>
            </div>
            <div style="padding:20px; text-align:right;"><button class="btn btn-primary">Save Changes</button></div>
        </form>
    </div></div>

    <!-- Add Student -->
    <div id="addStudentModal" class="modal"><div class="modal-content"><div class="modal-banner"><h3>Add Student</h3><button class="modal-close" onclick="toggleModal('addStudentModal', false)">&times;</button></div>
        <form onsubmit="handleStudentAdd(event)">
            <input type="hidden" name="section_id" id="add_student_sec_id">
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;"><label>ID Number</label><input type="text" name="studentIdNumber" required></div>
                <div class="form-group"><label>First Name</label><input type="text" name="firstName" required></div>
                <div class="form-group"><label>Last Name</label><input type="text" name="lastName" required></div>
                <div class="form-group"><label>Sex</label><select name="sex"><option>Male</option><option>Female</option></select></div>
                <div class="form-group"><label>Status</label><select name="status"><option>Regular</option><option>Irregular</option></select></div>
                <div class="form-group" style="grid-column: span 2;"><label>Course</label><input type="text" name="course" required></div>
            </div>
            <div style="padding:20px; text-align:right;"><button class="btn btn-primary">Add</button></div>
        </form>
    </div></div>

    <!-- Edit Student -->
    <div id="editStudentModal" class="modal"><div class="modal-content"><div class="modal-banner"><h3>Update Info</h3><button class="modal-close" onclick="toggleModal('editStudentModal', false)">&times;</button></div>
        <form onsubmit="handleFormSubmit(event, 'php/update_student.php', 'editStudentModal', true)">
            <input type="hidden" name="student_id" id="edit_stu_id">
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;"><label>ID Number</label><input type="text" name="studentIdNumber" id="edit_stu_num" required></div>
                <div class="form-group"><label>First Name</label><input type="text" name="firstName" id="edit_stu_fname" required></div>
                <div class="form-group"><label>Last Name</label><input type="text" name="lastName" id="edit_stu_lname" required></div>
                <div class="form-group"><label>Sex</label><select name="sex" id="edit_stu_sex"><option>Male</option><option>Female</option></select></div>
                <div class="form-group"><label>Status</label><select name="status" id="edit_stu_status"><option>Regular</option><option>Irregular</option></select></div>
                <div class="form-group" style="grid-column: span 2;"><label>Course</label><input type="text" name="course" id="edit_stu_course" required></div>
            </div>
            <div style="padding:20px; text-align:right;"><button class="btn btn-primary">Update</button></div>
        </form>
    </div></div>

    <!-- Student Profile -->
    <div id="studentProfileModal" class="modal"><div class="modal-content" style="max-width:550px"><div class="modal-banner" style="text-align:center;"><div style="font-size:2.5rem; margin-bottom:10px;"><i class="fa-solid fa-circle-user"></i></div><h3 id="pName">Student</h3><p id="pIdNum" style="opacity:0.8; font-size:0.8rem;"></p><button class="modal-close" onclick="toggleModal('studentProfileModal', false)">&times;</button></div>
        <div style="padding:25px;">
            <div class="p-info-box">
                <div><label class="form-group"><label>Program</label></label><span id="pCourse" style="font-weight:700"></span></div>
                <div><label class="form-group"><label>Sex</label></label><span id="pSex" style="font-weight:700"></span></div>
                <div><label class="form-group"><label>Status</label></label><span id="pStatus"></span></div>
            </div>
            <div style="margin-bottom:20px; border-top:1px solid #eef2f6; padding-top:20px;">
                <label class="form-group"><label>Quick Enroll to Subject</label></label>
                <form id="enrollForm" style="display:flex; gap:10px;" onsubmit="handleEnroll(event)">
                    <input type="hidden" name="student_id" id="enroll_sid">
                    <select name="subject_id" required style="flex-grow:1; padding:10px; border-radius:10px; border:1px solid var(--primary-light);">
                        <option value="">Select Subject</option>
                        <?php foreach($subjects_list as $sl): ?><option value="<?php echo $sl['subject_id']; ?>"><?php echo $sl['subject_code']; ?></option><?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary">Enroll</button>
                </form>
            </div>
            <label class="form-group"><label>Current Subject Load</label></label>
            <div id="pSubjectList" style="display:grid; gap:8px;"></div>
        </div>
    </div></div>

    <!-- Delete Confirm -->
    <div id="deleteModal" class="modal"><div class="modal-content" style="max-width:380px; text-align:center; padding:30px;">
        <div style="color:#ef4444; font-size:3.5rem; margin-bottom:15px;"><i class="fa-solid fa-circle-exclamation"></i></div>
        <h3>Delete Permanently?</h3>
        <p style="color:var(--text-muted); font-size:0.9rem; margin:10px 0 25px;">This action cannot be undone.</p>
        <div style="display:flex; gap:10px; justify-content:center;">
            <button class="btn btn-outline" onclick="toggleModal('deleteModal', false)">Cancel</button>
            <button class="btn btn-danger" id="confirmDeleteBtn">Delete Now</button>
        </div>
    </div></div>

    <script>
        let currentSection = null;
        let deleteTarget = { id: null, type: null };
        let activeCellTarget = null;

        function toggleModal(id, show) { document.getElementById(id).style.display = show ? 'flex' : 'none'; }

        // --- STATE PERSISTENCE ---
        function saveState(tab, secId = null, secName = null) {
            localStorage.setItem('cm_state', JSON.stringify({ tab, secId, secName }));
        }

        function restoreState() {
            const saved = localStorage.getItem('cm_state');
            if (saved) {
                const state = JSON.parse(saved);
                const tab = document.querySelector(`.nav-item[data-target="${state.tab}"]`);
                if (tab) tab.click();
                if (state.tab === 'attendance' && state.secId) {
                    openAttendanceSheet({ section_id: state.secId, section_name: state.secName });
                } else if (state.tab === 'sections' && state.secId) {
                    viewSection({ section_id: state.secId, section_name: state.secName });
                }
            }
        }

        // --- ATTENDANCE SYSTEM ---
        function openAttendanceSheet(sec) {
            currentSection = sec;
            document.getElementById('attSectionList').classList.add('hidden');
            document.getElementById('attSpreadsheet').classList.remove('hidden');
            document.getElementById('attTitle').innerText = sec.section_name;
            document.getElementById('attMeta').innerText = `${sec.semester} | SY ${sec.sy_start}-${sec.sy_end}`;
            saveState('attendance', sec.section_id, sec.section_name);
            
            const headerRow = document.getElementById('attTableHeader');
            headerRow.innerHTML = '<th class="student-col">Student Name</th>';
            for(let w=1; w<=18; w++) headerRow.innerHTML += `<th>W${w}</th>`;

            fetch(`php/get_students.php?section_id=${sec.section_id}`)
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        const sorted = res.data.sort((a, b) => a.last_name.localeCompare(b.last_name));
                        const tbody = document.getElementById('attTableBody');
                        tbody.innerHTML = '';
                        
                        sorted.forEach(s => {
                            const tr = document.createElement('tr');
                            tr.id = `att-row-${s.student_id}`;
                            let html = `<td class="student-col"><strong>${s.last_name}, ${s.first_name}</strong></td>`;
                            for(let w=1; w<=18; w++) {
                                html += `<td><button class="att-cell empty" id="cell-${s.student_id}-${w}" onclick="showAttPicker(this, ${s.student_id}, ${w})">-</button></td>`;
                            }
                            tr.innerHTML = html;
                            tbody.appendChild(tr);
                        });

                        // Hydrate Attendance
                        fetch(`php/get_attendance.php?section_id=${sec.section_id}`)
                            .then(r => r.json())
                            .then(attRes => {
                                if(attRes.status === 'success' && attRes.data) {
                                    attRes.data.forEach(rec => {
                                        const cell = document.getElementById(`cell-${rec.student_id}-${rec.week_number}`);
                                        if(cell && rec.status !== 'NONE') {
                                            cell.className = 'att-cell ' + rec.status.toLowerCase();
                                            cell.innerText = rec.status;
                                        }
                                    });
                                }
                            });
                    }
                });
        }

        function showAttPicker(btn, studentId, week) {
            const picker = document.getElementById('attOptionsPicker');
            const rect = btn.getBoundingClientRect();
            activeCellTarget = { studentId, week, element: btn };
            picker.style.display = 'flex';
            picker.style.top = (rect.bottom + window.scrollY + 5) + 'px';
            picker.style.left = (rect.left + window.scrollX) + 'px';
            
            const closer = (e) => { if (!picker.contains(e.target) && e.target !== btn) { picker.style.display = 'none'; document.removeEventListener('mousedown', closer); } };
            document.addEventListener('mousedown', closer);
        }

        function selectAttStatus(status) {
            if(!activeCellTarget) return;
            const { studentId, week, element } = activeCellTarget;
            document.getElementById('attOptionsPicker').style.display = 'none';
            element.className = 'att-cell ' + (status === 'NONE' ? 'empty' : status.toLowerCase());
            element.innerText = (status === 'NONE' ? '-' : status);

            const fd = new FormData();
            fd.append('student_id', studentId);
            fd.append('section_id', currentSection.section_id);
            fd.append('week', week);
            fd.append('status', status);
            fetch('php/save_attendance.php', { method: 'POST', body: fd });
        }

        function searchAndHighlightStudent(e) {
            const val = e.target.value.toLowerCase();
            if(!val) return;
            document.querySelectorAll('#attTableBody tr').forEach(r => {
                const name = r.querySelector('.student-col').innerText.toLowerCase();
                if(name.includes(val)) {
                    r.classList.add('search-highlight');
                    r.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => r.classList.remove('search-highlight'), 3000);
                }
            });
        }

        function backToAttSections() {
            document.getElementById('attSectionList').classList.remove('hidden');
            document.getElementById('attSpreadsheet').classList.add('hidden');
            saveState('attendance');
        }

        // --- SECTION & STUDENT MGMT ---
        function handleFormSubmit(e, url, modalId, isStudentUpdate = false) {
            e.preventDefault();
            fetch(url, { method: 'POST', body: new FormData(e.target) })
                .then(r => r.json())
                .then(res => { if(res.status === 'success') { toggleModal(modalId, false); if(isStudentUpdate) refreshStudents(); else window.location.reload(); } else alert(res.message); });
        }

        function openEditSubject(s) {
            document.getElementById('edit_sub_id').value = s.subject_id;
            document.getElementById('edit_sub_sched').value = s.sched_code;
            document.getElementById('edit_sub_code').value = s.subject_code;
            document.getElementById('edit_sub_name').value = s.subject_name;
            document.getElementById('edit_sub_start').value = s.start_time;
            document.getElementById('edit_sub_end').value = s.end_time;
            document.getElementById('edit_sub_day').value = s.sched_day;
            toggleModal('editSubjectModal', true);
        }

        function openEditSection(sec) {
            document.getElementById('edit_sec_id').value = sec.section_id;
            document.getElementById('edit_sec_name').value = sec.section_name;
            document.getElementById('edit_sec_start').value = sec.sy_start;
            document.getElementById('edit_sec_end').value = sec.sy_end;
            document.getElementById('edit_sec_sem').value = sec.semester;
            toggleModal('editSectionModal', true);
        }

        function openEditStudent(s) {
            document.getElementById('edit_stu_id').value = s.student_id;
            document.getElementById('edit_stu_num').value = s.student_id_number;
            document.getElementById('edit_stu_fname').value = s.first_name;
            document.getElementById('edit_stu_lname').value = s.last_name;
            document.getElementById('edit_stu_sex').value = s.sex;
            document.getElementById('edit_stu_status').value = s.status;
            document.getElementById('edit_stu_course').value = s.course;
            toggleModal('editStudentModal', true);
        }

        function viewSection(sec) {
            currentSection = sec;
            document.getElementById('activeSecTitle').innerText = sec.section_name;
            document.getElementById('activeSecMeta').innerText = `${sec.semester} | SY ${sec.sy_start}-${sec.sy_end}`;
            document.getElementById('add_student_sec_id').value = sec.section_id;
            document.getElementById('sectionListView').classList.add('hidden');
            document.getElementById('sectionDetailView').classList.remove('hidden');
            saveState('sections', sec.section_id, sec.section_name);
            refreshStudents();
        }

        function showSectionList() {
            document.getElementById('sectionListView').classList.remove('hidden');
            document.getElementById('sectionDetailView').classList.add('hidden');
            saveState('sections');
        }

        function refreshStudents() {
            fetch(`php/get_students.php?section_id=${currentSection.section_id}`)
                .then(r => r.json())
                .then(res => {
                    const tbody = document.getElementById('studentTableBody');
                    tbody.innerHTML = res.data.map(s => `
                        <tr>
                            <td><span style="font-weight:700;">${s.student_id_number}</span></td>
                            <td><strong>${s.last_name}, ${s.first_name}</strong></td>
                            <td><span class="badge badge-${s.status.toLowerCase() === 'regular' ? 'reg' : 'irr'}">${s.status}</span></td>
                            <td>
                                <button class="btn btn-outline btn-icon" onclick='openProfile(${JSON.stringify(s)})'><i class="fa-solid fa-address-card"></i></button>
                                <button class="btn btn-outline btn-icon" onclick='openEditStudent(${JSON.stringify(s)})'><i class="fa-solid fa-user-pen"></i></button>
                                <button class="btn btn-danger btn-icon" onclick="askDelete(${s.student_id}, 'student')"><i class="fa-solid fa-trash-can"></i></button>
                            </td>
                        </tr>
                    `).join('');
                });
        }

        function handleStudentAdd(e) {
            e.preventDefault();
            fetch('php/add_student.php', { method: 'POST', body: new FormData(e.target) })
                .then(r => r.json()).then(res => { if(res.status === 'success') { toggleModal('addStudentModal', false); refreshStudents(); } else alert(res.message); });
        }

        function openProfile(s) {
            toggleModal('studentProfileModal', true);
            document.getElementById('pName').innerText = `${s.first_name} ${s.last_name}`;
            document.getElementById('pIdNum').innerText = s.student_id_number;
            document.getElementById('pCourse').innerText = s.course;
            document.getElementById('pSex').innerText = s.sex;
            document.getElementById('pStatus').innerHTML = `<span class="badge badge-${s.status.toLowerCase() === 'regular' ? 'reg' : 'irr'}">${s.status}</span>`;
            document.getElementById('enroll_sid').value = s.student_id;
            loadEnrolledSubjects(s.student_id);
        }

        function loadEnrolledSubjects(sid) {
            const list = document.getElementById('pSubjectList');
            list.innerHTML = '<p style="text-align:center; font-size:0.8rem;">Loading...</p>';
            fetch(`php/get_student_subjects.php?student_id=${sid}`)
                .then(r => r.json())
                .then(res => {
                    list.innerHTML = res.data.length > 0 ? res.data.map(sb => `
                        <div style="background:#f8fafc; padding:10px; border-radius:12px; border:1px solid #eef2f6; display:flex; justify-content:space-between; align-items:center;">
                            <div><strong style="color:var(--primary);">${sb.subject_code}</strong><br><small>${sb.subject_name}</small></div>
                            <span style="font-size:0.7rem; font-weight:700;">${sb.sched_day}</span>
                        </div>
                    `).join('') : '<p style="text-align:center; color:var(--text-muted); padding:10px; font-size:0.8rem;">No subjects enrolled.</p>';
                });
        }

        function handleEnroll(e) {
            e.preventDefault();
            const sid = document.getElementById('enroll_sid').value;
            fetch('php/enroll_student.php', { method: 'POST', body: new FormData(e.target) })
                .then(r => r.json()).then(res => { if(res.status === 'success') loadEnrolledSubjects(sid); else alert(res.message); });
        }

        function askDelete(id, type) { deleteTarget = { id, type }; toggleModal('deleteModal', true); }

        document.getElementById('confirmDeleteBtn').onclick = () => {
            const url = deleteTarget.type === 'subject' ? 'php/delete_subject.php' : 
                        (deleteTarget.type === 'section' ? 'php/delete_section.php' : 'php/delete_student.php');
            fetch(`${url}?id=${deleteTarget.id}`).then(() => { toggleModal('deleteModal', false); if(deleteTarget.type === 'student') refreshStudents(); else window.location.reload(); });
        };

        function filterStudentTable() {
            const val = document.getElementById('studentSearchInput').value.toLowerCase();
            document.querySelectorAll('#studentTableBody tr').forEach(r => {
                r.style.display = r.innerText.toLowerCase().includes(val) ? '' : 'none';
            });
        }

        // --- NAVIGATION ---
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', () => {
                document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                item.classList.add('active');
                document.querySelectorAll('.content-section').forEach(s => s.classList.add('hidden'));
                document.getElementById(item.dataset.target).classList.remove('hidden');
                document.getElementById('pageTitle').innerText = item.querySelector('span').innerText;
                saveState(item.dataset.target);
            });
        });

        window.onload = restoreState;
    </script>
</body>
</html>