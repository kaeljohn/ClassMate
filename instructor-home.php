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
    .table-container { overflow-x: auto; margin-top: 20px; }
    .name-row { display: flex; gap: 10px; margin-bottom: 15px; }
    .name-row .form-group { flex: 1; }
    .modal select { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; background-color: #f8fafc; font-family: 'Quicksand', sans-serif; }
    
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(8px); display: flex; justify-content: center; align-items: center; z-index: 10000; }
    .feedback-card { background: #ffffff; width: 90%; max-width: 400px; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3); text-align: center; padding: 30px; animation: modalPop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .feedback-icon { font-size: 3rem; margin-bottom: 15px; }
    .error .feedback-icon { color: #ef4444; }
    .success .feedback-icon { color: #10b981; }
    .feedback-btn { width: 100%; padding: 12px; border: none; border-radius: 12px; background: #1e293b; color: white; font-weight: 600; cursor: pointer; margin-top: 20px; }

    @keyframes modalPop {
        from { opacity: 0; transform: scale(0.8); }
        to { opacity: 1; transform: scale(1); }
    }
</style>


</head>

<body>
<!-- Universal Feedback Modal -->
<div id="universalModal" class="modal-overlay" style="display: none;">
<div id="feedbackCard" class="feedback-card">
<div id="feedbackIcon" class="feedback-icon"></div>
<h2 id="modalTitle" style="margin-bottom: 10px; color: #1e293b;"></h2>
<p id="modalMsg" style="color: #64748b;"></p>
<div id="universalModalFooter">
<button class="feedback-btn" onclick="handleAcknowledge()">Acknowledge</button>
</div>
</div>
</div>

<header>
    <h1><a href="index.html" class="logo-link"><i class="fa-solid fa-graduation-cap"></i> ClassMate</a></h1>
    <div class="header-user-info">
        <span class="welcome-text">Welcome, <strong><?php echo htmlspecialchars($current_instructor); ?>!</strong></span>
        <a href="logout.php" class="account-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</header>

<section class="main-dashboard">
    <div class="dashboard-box">
        <nav class="sidebar">
            <ul class="nav-links">
                <li><a href="#" class="nav-btn active" data-target="subjects"><i class="fa-solid fa-book-open"></i> Subjects</a></li>
                <li><a href="#" class="nav-btn" data-target="students"><i class="fa-solid fa-user-graduate"></i> Students</a></li>
                <li><a href="#" class="nav-btn" data-target="attendance"><i class="fa-solid fa-calendar-check"></i> Attendance</a></li>
                <li><a href="#" class="nav-btn" data-target="evaluation"><i class="fa-solid fa-file-invoice"></i> Evaluation</a></li>
                <li><a href="#" class="nav-btn" data-target="analytics"><i class="fa-solid fa-chart-line"></i> Analytics</a></li>
            </ul>
        </nav>

        <main class="content-area">
            <section id="subjects" class="content-section">
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
                                    <th>Sched Code</th>
                                    <th>Subject Code</th>
                                    <th>Subject Description</th>
                                    <th>Schedule Time</th>
                                    <th>Day</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0):
                                    while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['sched_code']); ?></td>
                                            <td><?php echo htmlspecialchars($row['subject_code']); ?></td>
                                            <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                            <td>
                                                <?php 
                                                    $start = date("h:i A", strtotime($row['start_time']));
                                                    $end = date("h:i A", strtotime($row['end_time']));
                                                    echo "$start - $end"; 
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['sched_day']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick='openEditSubjectModal(<?php echo json_encode($row); ?>)'>
                                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $row['subject_id']; ?>, '<?php echo htmlspecialchars($row['subject_code']); ?>')">
                                                    <i class="fa-solid fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                    <tr class="no-data"><td colspan="6">No subjects found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- ADD MODAL -->
            <div id="addSubjectModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Add New Subject</h2>
                        <button class="close-btn" onclick="closeAddSubjectModal()">&times;</button>
                    </div>
                    <form action="php/add_subject.php" method="POST">
                        <div class="form-group"><label>Sched Code</label><input type="text" name="schedCode" required></div>
                        <div class="form-group"><label>Subject Code</label><input type="text" name="subjectCode" required></div>
                        <div class="form-group"><label>Subject Description</label><input type="text" name="subjectName" required></div>
                        <div class="name-row">
                            <div class="form-group"><label>Start Time</label><input type="time" name="startTime" required></div>
                            <div class="form-group"><label>End Time</label><input type="time" name="endTime" required></div>
                        </div>
                        <div class="form-group">
                            <label>Day</label>
                            <select name="schedDay" required>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Subject</button></div>
                    </form>
                </div>
            </div>

            <!-- EDIT MODAL -->
            <div id="editSubjectModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Edit Subject</h2>
                        <button class="close-btn" onclick="closeEditSubjectModal()">&times;</button>
                    </div>
                    <form action="php/update_subject.php" method="POST">
                        <input type="hidden" name="subject_id" id="edit_subject_id">
                        <div class="form-group"><label>Sched Code</label><input type="text" name="schedCode" id="edit_schedCode" required></div>
                        <div class="form-group"><label>Subject Code</label><input type="text" name="subjectCode" id="edit_subjectCode" required></div>
                        <div class="form-group"><label>Subject Description</label><input type="text" name="subjectName" id="edit_subjectName" required></div>
                        <div class="name-row">
                            <div class="form-group"><label>Start Time</label><input type="time" name="startTime" id="edit_startTime" required></div>
                            <div class="form-group"><label>End Time</label><input type="time" name="endTime" id="edit_endTime" required></div>
                        </div>
                        <div class="form-group">
                            <label>Day</label>
                            <select name="schedDay" id="edit_schedDay" required>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary">Update Subject</button></div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</section>

<script>
    function openAddSubjectModal() { document.getElementById('addSubjectModal').style.display = 'flex'; }
    function closeAddSubjectModal() { document.getElementById('addSubjectModal').style.display = 'none'; }
    
    function openEditSubjectModal(data) {
        document.getElementById('edit_subject_id').value = data.subject_id;
        document.getElementById('edit_schedCode').value = data.sched_code;
        document.getElementById('edit_subjectCode').value = data.subject_code;
        document.getElementById('edit_subjectName').value = data.subject_name;
        document.getElementById('edit_startTime').value = data.start_time;
        document.getElementById('edit_endTime').value = data.end_time;
        document.getElementById('edit_schedDay').value = data.sched_day;
        document.getElementById('editSubjectModal').style.display = 'flex';
    }
    function closeEditSubjectModal() { document.getElementById('editSubjectModal').style.display = 'none'; }

    function handleAcknowledge() {
        document.getElementById('universalModal').style.display = 'none';
        window.location.reload();
    }

    function confirmDelete(id, code) {
        if (confirm(`Delete ${code}?`)) {
            fetch(`php/delete_subject.php?id=${id}`)
                .then(r => r.json())
                .then(data => {
                    const modal = document.getElementById('universalModal');
                    document.getElementById('feedbackCard').className = 'feedback-card ' + (data.success ? 'success' : 'error');
                    document.getElementById('feedbackIcon').innerHTML = data.success ? '<i class="fa-solid fa-circle-check"></i>' : '<i class="fa-solid fa-circle-xmark"></i>';
                    document.getElementById('modalTitle').innerText = data.success ? 'Success!' : 'Error';
                    document.getElementById('modalMsg').innerText = data.message;
                    modal.style.display = 'flex';
                });
        }
    }
</script>


</body>
</html>