let currentSection = null;
let currentSubject = null;
let deleteTarget = { id: null, type: null };
let activeCellTarget = null;
let massTargetWeek = null;
let massRowTargetId = null; 
let currentAnalyticsType = null;
let analyticsChartInstance = null;
let isAttendanceMode = false; 
let isAnalyticsMode = false; 

let currentMaxScores = {}; 
let currentMaxScoreType = null;

const courseData = {
    "Graduate School and Open Learning College": {
        courses: ["PhD in Agriculture", "PhD in Education", "PhD in Management", "Master in Business Administration", "Master of Agriculture", "Master of Arts in Education", "Master of Engineering", "Master of Management", "Master of Professional Studies", "MS Agriculture", "MS Biology", "MS Food Science", "Master in Information Technology"]
    },
    "Undergraduate Programs": {
        subcategories: {
            "College of Agriculture, Food, Environment and Natural Resources": ["Bachelor of Agricultural Entrepreneurship", "BS Agriculture", "BS Environmental Science", "BS Food Technology"],
            "College of Arts and Sciences": ["BA English Language Studies", "BA Journalism", "BA Political Science", "BS Applied Mathematics", "BS Biology", "BS Psychology", "BS Social Work"],
            "College of Criminal Justice": ["BS Criminology", "BS Industrial Security Management"],
            "College of Economics, Management, and Development Studies": ["BS Accountancy", "BS Business Management", "BS Development Management", "BS Economics", "BS International Studies", "BS Office Administration"],
            "College of Education": ["Bachelor of Early Childhood Education", "Bachelor of Elementary Education", "Bachelor of Secondary Education", "Bachelor of Special Needs Education", "Bachelor of Technology and Livelihood Education", "BS Hospitality Management", "BS Tourism Management", "Teacher Certificate Program", "Science High School", "Elementary Education", "Pre-Elementary Education"],
            "College of Engineering and Information Technology": ["BS Agricultural and Biosystems Engineering", "BS Architecture", "BS Civil Engineering", "BS Computer Engineering", "BS Computer Science", "BS Electrical Engineering", "BS Electronics Engineering", "BS Industrial Engineering", "BS Industrial Technology Major in Automotive Technology", "BS Industrial Technology Major in Electrical Technology", "BS Industrial Technology Major in Electronics Technology", "BS Information Technology"],
            "College of Nursing": ["BS Medical Technology", "BS Midwifery", "BS Nursing", "Diploma in Midwifery"],
            "College of Sports, Physical Education and Recreation": ["Bachelor of Physical Education", "Bachelor of Exercise and Sports Sciences"],
            "College of Veterinary Medicine and Biomedical Sciences": ["Doctor of Veterinary Medicine"]
        }
    }
};

let courseModalTargetInput = null; 

function toggleModal(id, show) { 
    const el = document.getElementById(id);
    if(el) el.style.display = show ? 'flex' : 'none'; 
}

function confirmLogout() {
    window.location.href = 'php/logout.php';
}

// --- ATTENDANCE ---
function prepareAttendanceSheet(sec) {
    currentSection = sec;
    isAttendanceMode = true;
    isAnalyticsMode = false;
    toggleModal('selectSubjectModal', true);
}

// --- GRADES ---
function prepareGradeSheet(sec) {
    currentSection = sec;
    isAttendanceMode = false;
    isAnalyticsMode = false;
    toggleModal('selectSubjectModal', true);
}

// --- UNIVERSAL SUBJECT HANDLER ---
function handleUniversalSubjectSelect(sub) {
    currentSubject = sub;
    toggleModal('selectSubjectModal', false);
    
    if (isAnalyticsMode) {
        loadSubjectAnalytics(sub);
    } else if (isAttendanceMode) {
        openAttendanceSheet(currentSection, currentSubject);
    } else {
        openGradeSheet(currentSection, currentSubject);
    }
}

// --- ATTENDANCE LOGIC ---
function openAttendanceSheet(sec, sub) {
    document.getElementById('attSectionList').classList.add('hidden');
    document.getElementById('attSpreadsheet').classList.remove('hidden');
    
    document.getElementById('attTitle').innerText = sec.section_name;
    document.getElementById('attMeta').innerText = `${sub.subject_code} (${sub.sched_day}) | ${sec.semester}`;
    
    saveState('attendance', sec);

    const headerRow = document.getElementById('attTableHeader');
    headerRow.innerHTML = '<th class="fixed-col">Student Name</th>';
    for (let w = 1; w <= 18; w++) headerRow.innerHTML += `<th onclick="showMassPicker(this, ${w})" style="cursor:pointer">W${w}</th>`;

    // UPDATED: Fetch Enrolled Students ONLY
    fetch(`php/attendance/get_enrolled_students.php?section_id=${sec.section_id}&subject_id=${sub.subject_id}`)
        .then(r => r.json())
        .then(res => {
            const tbody = document.getElementById('attTableBody');
            tbody.innerHTML = '';
            
            if (res.status === 'success' && res.data.length > 0) {
                const sorted = res.data.sort((a, b) => a.last_name.localeCompare(b.last_name));

                sorted.forEach(s => {
                    const tr = document.createElement('tr');
                    tr.id = `att-row-${s.student_id}`;
                    let html = `<td class="fixed-col" style="cursor:pointer" onclick="showRowPicker(this, ${s.student_id})"><strong>${s.last_name}, ${s.first_name}</strong></td>`;
                    for (let w = 1; w <= 18; w++) html += `<td><button class="att-cell empty" id="cell-${s.student_id}-${w}" onclick="showAttPicker(this, ${s.student_id}, ${w})">-</button></td>`;
                    tr.innerHTML = html;
                    tbody.appendChild(tr);
                });

                fetch(`php/attendance/get_attendance.php?section_id=${sec.section_id}&subject_id=${sub.subject_id}`)
                    .then(r => r.json())
                    .then(attRes => {
                        if (attRes.status === 'success') {
                            attRes.data.forEach(rec => {
                                const cell = document.getElementById(`cell-${rec.student_id}-${rec.week_number}`);
                                if (cell && rec.status !== 'NONE') {
                                    cell.className = 'att-cell ' + rec.status.toLowerCase();
                                    cell.innerText = rec.status;
                                }
                            });
                        }
                    });
            } else {
                 tbody.innerHTML = `<tr><td colspan="20" style="text-align:center;">No students enrolled in this subject.</td></tr>`;
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
    if (!activeCellTarget) return;
    const { studentId, week, element } = activeCellTarget;
    document.getElementById('attOptionsPicker').style.display = 'none';
    element.className = 'att-cell ' + (status === 'NONE' ? 'empty' : status.toLowerCase());
    element.innerText = (status === 'NONE' ? '-' : status);
    
    saveAttendanceRecord(studentId, week, status);
}

function saveAttendanceRecord(sid, week, status) {
    const fd = new FormData();
    fd.append('student_id', sid); 
    fd.append('section_id', currentSection.section_id); 
    fd.append('subject_id', currentSubject.subject_id); 
    fd.append('week', week); 
    fd.append('status', status);
    fetch('php/attendance/save_attendance.php', { method: 'POST', body: fd });
}

function showMassPicker(th, week) {
    massTargetWeek = week;
    const picker = document.getElementById('massAttPicker');
    const rect = th.getBoundingClientRect();
    picker.style.display = 'flex';
    picker.style.top = (rect.bottom + window.scrollY + 5) + 'px';
    picker.style.left = (rect.left + window.scrollX) + 'px';
    const closer = (e) => { if (!picker.contains(e.target) && e.target !== th) { picker.style.display = 'none'; document.removeEventListener('mousedown', closer); } };
    document.addEventListener('mousedown', closer);
}

function massMark(status) {
    document.getElementById('massAttPicker').style.display = 'none';
    const rows = document.querySelectorAll('#attTableBody tr');
    rows.forEach(row => {
        if(!row.id.startsWith('att-row-')) return; 
        const studentId = row.id.split('-')[2];
        const cell = document.getElementById(`cell-${studentId}-${massTargetWeek}`);
        if (cell) {
            cell.className = 'att-cell ' + status.toLowerCase();
            cell.innerText = status;
            saveAttendanceRecord(studentId, massTargetWeek, status);
        }
    });
}

function showRowPicker(td, studentId) {
    massRowTargetId = studentId;
    const picker = document.getElementById('massRowPicker');
    const rect = td.getBoundingClientRect();
    picker.style.display = 'flex';
    picker.style.top = (rect.bottom + window.scrollY + 5) + 'px';
    picker.style.left = (rect.left + window.scrollX) + 'px';
    const closer = (e) => { if (!picker.contains(e.target) && e.target !== td) { picker.style.display = 'none'; document.removeEventListener('mousedown', closer); } };
    document.addEventListener('mousedown', closer);
}

function massMarkRow(status) {
    document.getElementById('massRowPicker').style.display = 'none';
    if (!massRowTargetId) return;

    for(let w=1; w<=18; w++) {
        const cell = document.getElementById(`cell-${massRowTargetId}-${w}`);
        if(cell) {
            cell.className = 'att-cell ' + status.toLowerCase();
            cell.innerText = status;
            saveAttendanceRecord(massRowTargetId, w, status);
        }
    }
    showFeedback('success', 'Row Update', `Student marked as ${status} for all weeks.`);
}

// --- GRADE SHEET LOGIC ---
function openGradeSheet(sec, sub) {
    document.getElementById('gradeSectionList').classList.add('hidden');
    document.getElementById('gradeSpreadsheet').classList.remove('hidden');
    document.getElementById('gradeTitle').innerText = `${sec.section_name} - ${sub.subject_code}`;
    document.getElementById('gradeMeta').innerText = sub.subject_name;
    saveState('grades', sec);

    currentMaxScores = {}; 
    fetch(`php/quizandexams/get_max_scores.php?section_id=${sec.section_id}&subject_id=${sub.subject_id}`)
        .then(r => r.json())
        .then(maxRes => {
            if (maxRes.status === 'success') {
                maxRes.data.forEach(item => {
                    currentMaxScores[item.assessment_type] = parseInt(item.max_score);
                });
            }
            renderGradeSheet(sec, sub);
        });
}

function renderGradeSheet(sec, sub) {
    const header = document.getElementById('gradeTableHeader');
    let html = '<th class="fixed-col">Student Name</th>';
    
    const genHead = (lbl, type) => {
        const max = currentMaxScores[type] || 100;
        return `<th style="cursor:pointer; min-width:80px;" onclick="promptMaxScore('${type}')">
                    ${lbl}
                    <span class="max-score-display" id="header-max-${type}">/ ${max}</span>
                </th>`;
    };

    for (let i = 1; i <= 10; i++) html += genHead(`Q${i}`, `Quiz${i}`);
    html += genHead('Midterm', 'Midterm');
    html += genHead('Finals', 'Finals');
    header.innerHTML = html;

    // UPDATED: Fetch only enrolled students
    fetch(`php/attendance/get_enrolled_students.php?section_id=${sec.section_id}&subject_id=${sub.subject_id}`).then(r => r.json()).then(res => {
        const tbody = document.getElementById('gradeTableBody');
        tbody.innerHTML = '';
        if(res.status === 'success' && res.data.length > 0) {
            res.data.sort((a, b) => a.last_name.localeCompare(b.last_name)).forEach(s => {
                let html = `<tr><td class="fixed-col"><strong>${s.last_name}, ${s.first_name}</strong></td>`;
                for (let i = 1; i <= 10; i++) html += `<td><input class="grade-input" type="number" id="g-${s.student_id}-Quiz${i}" onchange="saveGrade(${s.student_id}, 'Quiz${i}', this)"></td>`;
                html += `<td><input class="grade-input" type="number" id="g-${s.student_id}-Midterm" onchange="saveGrade(${s.student_id}, 'Midterm', this)"></td>`;
                html += `<td><input class="grade-input" type="number" id="g-${s.student_id}-Finals" onchange="saveGrade(${s.student_id}, 'Finals', this)"></td></tr>`;
                tbody.innerHTML += html;
            });

            fetch(`php/quizandexams/get_grades.php?section_id=${sec.section_id}&subject_id=${sub.subject_id}`).then(r => r.json()).then(d => {
                if (d.status === 'success') d.data.forEach(g => {
                    const field = document.getElementById(`g-${g.student_id}-${g.assessment_type}`);
                    if (field) field.value = g.score;
                });
            });
        } else {
             tbody.innerHTML = `<tr><td colspan="15" style="text-align:center;">No students enrolled in this subject.</td></tr>`;
        }
    });
}

function promptMaxScore(type) {
    currentMaxScoreType = type;
    const currentMax = currentMaxScores[type] || 100;
    
    document.getElementById('maxScoreModalLabel').innerText = `Enter Max Score for ${type}`;
    document.getElementById('maxScoreInput').value = currentMax;
    toggleModal('maxScoreModal', true);
}

function saveMaxScoreFromModal(e) {
    e.preventDefault();
    const newMax = document.getElementById('maxScoreInput').value;
    const type = currentMaxScoreType;
    
    if (!newMax || newMax < 1) return;
    
    const val = parseInt(newMax);

    const fd = new FormData();
    fd.append('section_id', currentSection.section_id);
    fd.append('subject_id', currentSubject.subject_id);
    fd.append('assessment_type', type);
    fd.append('max_score', val);

    fetch('php/quizandexams/save_max_score.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                currentMaxScores[type] = val;
                document.getElementById(`header-max-${type}`).innerText = `/ ${val}`;
                toggleModal('maxScoreModal', false);
            } else {
                showFeedback('error', 'Save Failed', "Failed to save max score.");
            }
        });
}

function saveGrade(sid, type, inputEl) {
    const max = currentMaxScores[type] || 100;
    let val = parseFloat(inputEl.value);

    if (val < 0) { inputEl.value = 0; val = 0; }
    if (val > max) { 
        showFeedback('error', 'Invalid Score', `Score cannot exceed maximum of ${max}`);
        inputEl.value = max; 
        val = max; 
    }

    const fd = new FormData();
    fd.append('student_id', sid); 
    fd.append('section_id', currentSection.section_id); 
    fd.append('subject_id', currentSubject.subject_id); 
    fd.append('type', type); 
    fd.append('score', val);
    fetch('php/quizandexams/save_grade.php', { method: 'POST', body: fd });
}

// --- COURSE MODALS ---
let navStack = []; 

function openCourseModal(targetType) {
    courseModalTargetInput = targetType; 
    toggleModal('courseSelectionModal', true);
    renderMainCategories();
}

function courseNavBack() {
    if (navStack.length > 0) {
        navStack.pop();
        if (navStack.length === 0) renderMainCategories();
        else {
             const last = navStack[navStack.length-1];
             if (courseData[last]) {
                 handleCategorySelect(last, false); 
             } else {
                 renderMainCategories();
             }
        }
    }
}

function updateBreadcrumb(text) {
    document.getElementById('courseBreadcrumb').innerText = text;
    document.getElementById('courseBackBtn').disabled = (text === 'Main Categories');
}

function renderMainCategories() {
    navStack = [];
    document.getElementById('mainCategoryView').classList.remove('hidden');
    document.getElementById('subCategoryView').classList.add('hidden');
    document.getElementById('courseListView').classList.add('hidden');
    document.getElementById('searchResultsView').classList.add('hidden');
    document.getElementById('courseSearchInput').value = '';
    
    updateBreadcrumb('Main Categories');
    
    const container = document.getElementById('mainCategoryView');
    container.innerHTML = '';
    Object.keys(courseData).forEach(cat => {
        container.innerHTML += `<button class="course-cat-btn" onclick="handleCategorySelect('${cat}')"><strong>${cat}</strong></button>`;
    });
}

function handleCategorySelect(catName, pushStack = true) {
    if(pushStack) navStack.push(catName);
    
    const catData = courseData[catName];
    document.getElementById('mainCategoryView').classList.add('hidden');
    
    if (catData.subcategories) {
        renderSubCategories(catData.subcategories, catName);
    } else {
        renderCourseList(catData.courses, catName);
    }
}

function renderSubCategories(subcats, parentName) {
    document.getElementById('subCategoryView').classList.remove('hidden');
    document.getElementById('courseListView').classList.add('hidden');
    updateBreadcrumb(parentName);
    
    const container = document.getElementById('subCategoryView');
    container.innerHTML = '';
    Object.keys(subcats).forEach(sub => {
        container.innerHTML += `<button class="course-cat-btn" onclick="handleSubCatSelect('${sub}', '${parentName}')"><strong>${sub}</strong></button>`;
    });
}

function handleSubCatSelect(subName, parentName) {
    const courses = courseData[parentName].subcategories[subName];
    renderCourseList(courses, subName);
}

function renderCourseList(courses, title) {
    document.getElementById('subCategoryView').classList.add('hidden');
    document.getElementById('courseListView').classList.remove('hidden');
    updateBreadcrumb(title);
    
    const container = document.getElementById('courseListView');
    container.innerHTML = '';
    courses.forEach(c => {
        container.innerHTML += `<div class="course-list-item" onclick="selectCourse('${c}')">${c}</div>`;
    });
}

function filterCourses() {
    const val = document.getElementById('courseSearchInput').value.toLowerCase().trim();
    
    if (val === '') {
        if (navStack.length === 0) renderMainCategories(); 
        else document.getElementById('searchResultsView').classList.add('hidden');
        return;
    }

    document.getElementById('mainCategoryView').classList.add('hidden');
    document.getElementById('subCategoryView').classList.add('hidden');
    document.getElementById('courseListView').classList.add('hidden');
    document.getElementById('searchResultsView').classList.remove('hidden');
    
    const container = document.getElementById('searchResultsView');
    container.innerHTML = '';
    
    let results = [];
    const terms = val.split(' ').filter(t => t.length > 0);

    Object.keys(courseData).forEach(main => {
        if(courseData[main].courses) {
            courseData[main].courses.forEach(c => {
                if(terms.every(term => c.toLowerCase().includes(term))) {
                    results.push({ course: c, location: main });
                }
            });
        }
        if(courseData[main].subcategories) {
            Object.keys(courseData[main].subcategories).forEach(sub => {
                courseData[main].subcategories[sub].forEach(c => {
                    if(terms.every(term => c.toLowerCase().includes(term))) {
                        results.push({ course: c, location: sub });
                    }
                });
            });
        }
    });

    if (results.length === 0) {
        container.innerHTML = `<div style="text-align:center; padding: 20px;">
            <i class="fa-solid fa-magnifying-glass" style="color:#cbd5e1; font-size:2rem; margin-bottom:10px;"></i>
            <p style="color:var(--text-muted);">No programs found matching "<strong>${val}</strong>"</p>
        </div>`;
    } else {
        results.forEach(r => {
            container.innerHTML += `<div class="course-list-item" onclick="selectCourse('${r.course}')">
                <strong>${r.course}</strong><br>
                <small style="color:var(--text-muted);">${r.location}</small>
            </div>`;
        });
    }
}

function selectCourse(courseName) {
    const targetId = courseModalTargetInput === 'add' ? 'add_student_course_display' : 'edit_stu_course';
    const input = document.getElementById(targetId);
    if(input) input.value = courseName;
    toggleModal('courseSelectionModal', false);
}

// --- ANALYTICS ---
function selectAnalyticsType(type) {
    currentAnalyticsType = type;
    document.getElementById('analyticsMenu').classList.add('hidden');
    document.getElementById('analyticsSectionPicker').classList.remove('hidden');
    document.getElementById('analyticsResult').classList.add('hidden');
    const titles = { 'low_attendance': 'Low Attendance', 'student_averages': 'Overall Grades', 'ranking': 'Student Ranking', 'attendance_chart': 'Attendance Overview', 'late_absent': 'Late & Absent' };
    document.getElementById('anaPickerTitle').innerText = (titles[type] || 'Analysis') + " - Select Section";
}

function closeAnalyticsPicker() {
    document.getElementById('analyticsMenu').classList.remove('hidden');
    document.getElementById('analyticsSectionPicker').classList.add('hidden');
    document.getElementById('analyticsResult').classList.add('hidden');
    currentAnalyticsType = null;
    if (analyticsChartInstance) { analyticsChartInstance.destroy(); analyticsChartInstance = null; }
}

function backToAnaPicker() {
    document.getElementById('analyticsSectionPicker').classList.remove('hidden');
    document.getElementById('analyticsResult').classList.add('hidden');
}

function loadAnalyticsData(sec) {
    if (!currentAnalyticsType) return;
    currentSection = sec;

    // UPDATE: For attendance/late types, we MUST pick a subject first
    const typesRequiringSubject = ['low_attendance', 'attendance_chart', 'late_absent'];
    
    if (typesRequiringSubject.includes(currentAnalyticsType)) {
        isAnalyticsMode = true;
        isAttendanceMode = false;
        toggleModal('selectSubjectModal', true);
    } else {
        // Proceed directly for Rankings and Overall Grade
        fetchAndRenderAnalytics(sec, null);
    }
}

function loadSubjectAnalytics(sub) {
    currentSubject = sub;
    fetchAndRenderAnalytics(currentSection, sub);
}

function fetchAndRenderAnalytics(sec, sub) {
    document.getElementById('analyticsSectionPicker').classList.add('hidden');
    document.getElementById('analyticsResult').classList.remove('hidden');
    
    // Update title
    let title = sec.section_name;
    if (sub) title += ` - ${sub.subject_code}`;
    document.getElementById('anaResultTitle').innerText = title;

    const table = document.getElementById('anaResultTable');
    const chartArea = document.getElementById('anaChartArea');
    const contentArea = document.getElementById('anaContentArea');

    table.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:20px;">Loading data...</td></tr>`;
    chartArea.classList.add('hidden');
    contentArea.classList.remove('hidden');
    if (analyticsChartInstance) { analyticsChartInstance.destroy(); analyticsChartInstance = null; }

    // Build URL
    let url = `php/analytics/get_analytics.php?section_id=${sec.section_id}&type=${currentAnalyticsType}`;
    if (sub) url += `&subject_id=${sub.subject_id}`;

    fetch(url)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') renderAnalytics(res.data, currentAnalyticsType);
            else table.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:20px; color:red;">${res.message}</td></tr>`;
        })
        .catch(err => {
            // FIXED: Added error handling to prevent getting stuck on loading
            table.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:20px; color:red;">Error loading data. Check console.</td></tr>`;
            console.error(err);
        });
}

function renderAnalytics(data, type) {
    const table = document.getElementById('anaResultTable');
    const chartArea = document.getElementById('anaChartArea');
    const contentArea = document.getElementById('anaContentArea');
    let html = '';

    if (data.length === 0) {
        table.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted);">No records found.</td></tr>`;
        if (type === 'attendance_chart') { contentArea.classList.remove('hidden'); chartArea.classList.add('hidden'); }
        return;
    }

    if (type === 'low_attendance') {
        document.getElementById('anaResultMeta').innerText = "Students below 75% attendance threshold (Selected Subject)";
        html = `<thead><tr><th>Student Name</th><th>Present Days</th><th>Attendance Rate</th><th>Status</th></tr></thead><tbody>`;
        data.forEach(row => {
            const pct = parseFloat(row.percentage).toFixed(1);
            html += `<tr id="anaResultTableBody"><td><strong>${row.last_name}, ${row.first_name}</strong></td><td>${row.present_count}</td><td style="font-weight:700; color:#ef4444;">${pct}%</td><td><span class="badge badge-irr">At Risk</span></td></tr>`;
        });
        table.innerHTML = html;
    }
    else if (type === 'student_averages') {
        document.getElementById('anaResultMeta').innerText = "Class List - View Student Profile";
        html = `<thead><tr><th>Student Name</th><th>Actions</th></tr></thead><tbody>`;
        data.forEach(row => {
            html += `<tr id="anaResultTableBody">
                        <td><strong>${row.student_name}</strong></td>
                        <td>
                            <button class="btn btn-primary" style="padding: 5px 12px; font-size: 0.8rem;" 
                                onclick="openStudentProfileAnalytics('${row.student_id}', '${row.student_name}')">
                                <i class="fa-solid fa-address-card"></i> View Profile
                            </button>
                        </td>
                     </tr>`;
        });
        table.innerHTML = html;
    }
    else if (type === 'ranking') {
        document.getElementById('anaResultMeta').innerText = "Top Students (GWA across all subjects)";
        html = `<thead><tr><th>Rank</th><th>Student Name</th><th>GWA</th></tr></thead><tbody>`;
        data.forEach((row, index) => {
            let rankDisplay = `<div class="rank-circle ${index === 0 ? 'rank-1' : (index === 1 ? 'rank-2' : (index === 2 ? 'rank-3' : ''))}" style="${index > 2 ? 'background:#f1f5f9; color:var(--text-muted);' : ''}">${index + 1}</div>`;
            const grade = row.average_grade; 
            
            html += `<tr id="anaResultTableBody"><td style="width:60px;">${rankDisplay}</td><td><strong>${row.student_name}</strong></td><td style="font-weight:800; color:#22c55e; font-size:1.1rem;">${grade}</td></tr>`;
        });
        table.innerHTML = html;
    }
    else if (type === 'attendance_chart') {
        document.getElementById('anaResultMeta').innerText = "Attendance Overview (Selected Subject)";
        contentArea.classList.add('hidden'); chartArea.classList.remove('hidden');
        const labels = ['Present', 'Late', 'Excused', 'Absent'];
        const counts = [0, 0, 0, 0];
        const colors = ['#22c55e', '#f97316', '#1e293b', '#ef4444'];
        data.forEach(item => { if (item.status === 'P') counts[0] = item.count; if (item.status === 'L') counts[1] = item.count; if (item.status === 'E') counts[2] = item.count; if (item.status === 'A') counts[3] = item.count; });
        const ctx = document.getElementById('attendanceChartCanvas').getContext('2d');
        analyticsChartInstance = new Chart(ctx, { type: 'doughnut', data: { labels: labels, datasets: [{ data: counts, backgroundColor: colors, borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } } });
    }
    else if (type === 'late_absent') {
        document.getElementById('anaResultMeta').innerText = "Late & Absent Records (Selected Subject)";
        html = `<thead><tr><th>Student Name</th><th>Late Count</th><th>Absent Count</th><th>Total Issues</th></tr></thead><tbody>`;
        data.forEach(row => {
            const total = parseInt(row.late_count) + parseInt(row.absent_count);
            html += `<tr id="anaResultTableBody"><td><strong>${row.last_name}, ${row.first_name}</strong></td><td style="color:var(--att-late); font-weight:700;">${row.late_count}</td><td style="color:var(--att-absent); font-weight:700;">${row.absent_count}</td><td>${total}</td></tr>`;
        });
        table.innerHTML = html;
    }
}

// --- NEW: Open Student Profile Grade Breakdown (Multi-Subject) ---
function openStudentProfileAnalytics(studentId, studentName) {
    toggleModal('studentProfileGradesModal', true);
    document.getElementById('spgmTitle').innerText = studentName;
    const body = document.getElementById('spgmBody');
    const totalEl = document.getElementById('spgmTotal');
    body.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px;">Loading profile...</td></tr>';
    totalEl.innerText = '...';

    // New request to get breakdown
    fetch(`php/analytics/get_analytics.php?section_id=${currentSection.section_id}&type=student_breakdown&student_id=${studentId}`)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                let html = '';
                res.data.subjects.forEach(sub => {
                    const statusColor = sub.status === 'Passed' ? '#22c55e' : (sub.status === 'Failed' ? '#ef4444' : '#f59e0b');
                    html += `<tr>
                        <td style="padding:10px;"><strong>${sub.subject_code}</strong></td>
                        <td style="padding:10px;">${sub.subject_name}</td>
                        <td style="padding:10px; font-weight:700;">${sub.grade}</td>
                        <td style="padding:10px;"><span style="color:${statusColor}; font-weight:600;">${sub.status}</span></td>
                    </tr>`;
                });
                body.innerHTML = html || '<tr><td colspan="4" style="text-align:center; padding:20px;">No enrolled subjects.</td></tr>';
                totalEl.innerText = res.data.gwa;
            } else {
                body.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:20px; color:red;">${res.message}</td></tr>`;
            }
        })
        .catch(err => {
            // FIXED: Added error handling
            body.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:20px; color:red;">Error loading profile.</td></tr>`;
            console.error(err);
        });
}

// --- GENERAL ---
function searchScroll(e, tbodyId) {
    const val = e.target.value.toLowerCase();
    if (!val) return;
    const rows = document.querySelectorAll(`#${tbodyId} tr`);
    
    rows.forEach(r => r.classList.remove('search-highlight'));

    for (let r of rows) {
        if (r.innerText.toLowerCase().includes(val)) {
            r.classList.add('search-highlight');
            r.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => r.classList.remove('search-highlight'), 3000);
            return;
        }
    }
}

function showFeedback(type, title, msg) {
    const m = document.getElementById('universalModal');
    document.getElementById('feedbackIcon').innerHTML = type === 'success' ? '<i class="fa-solid fa-check-circle" style="color:var(--att-present)"></i>' : '<i class="fa-solid fa-circle-xmark" style="color:var(--att-absent)"></i>';
    document.getElementById('modalTitle').innerText = title;
    document.getElementById('modalMsg').innerText = msg;
    document.getElementById('universalModalFooter').innerHTML = `<button class="btn btn-primary" onclick="toggleModal('universalModal', false)">OK</button>`;
    m.style.display = 'flex';
}

function saveState(tab, sec = null) { 
    localStorage.setItem('cm_state', JSON.stringify({ tab, sec })); 
}

function restoreState() {
    const s = JSON.parse(localStorage.getItem('cm_state'));
    if (s && s.tab) {
        const nav = document.querySelector(`.nav-item[data-target="${s.tab}"]`);
        if (nav) nav.click();
        if (s.tab === 'sections' && s.sec) {
            viewSection(s.sec);
        }
    }
}

document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', (e) => {
        if (item.getAttribute('href') !== '#') return;
        e.preventDefault();

        if (item.innerText.includes('Logout')) return;

        document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
        item.classList.add('active');
        document.querySelectorAll('.content-section').forEach(s => s.classList.add('hidden'));
        document.getElementById(item.dataset.target).classList.remove('hidden');
        document.getElementById('pageTitle').innerText = item.querySelector('span').innerText;
        if (item.dataset.target === 'sections') showSectionList();
        if (item.dataset.target === 'attendance') backToAttSections();
        if (item.dataset.target === 'grades') backToGradeSections();
        if (item.dataset.target === 'analytics') closeAnalyticsPicker();
        
        saveState(item.dataset.target);
    });
});

function showSectionList() { document.getElementById('sectionListView').classList.remove('hidden'); document.getElementById('sectionDetailView').classList.add('hidden'); }
function viewSection(sec) {
    currentSection = sec;
    document.getElementById('sectTitle').innerText = sec.section_name;
    const sem = sec.semester || 'N/A';
    const sy = (sec.sy_start && sec.sy_end) ? `SY ${sec.sy_start}-${sec.sy_end}` : '';
    document.getElementById('sectMeta').innerText = `${sem} | ${sy}`;
    
    document.getElementById('sectionListView').classList.add('hidden');
    document.getElementById('sectionDetailView').classList.remove('hidden');
    
    saveState('sections', sec);
    refreshStudents();
}
function backToAttSections() { document.getElementById('attSectionList').classList.remove('hidden'); document.getElementById('attSpreadsheet').classList.add('hidden'); saveState('attendance'); }
function backToGradeSections() { document.getElementById('gradeSectionList').classList.remove('hidden'); document.getElementById('gradeSpreadsheet').classList.add('hidden'); saveState('grades'); }

function handleFormSubmit(e, url, mid, isStudentUpdate = false) {
    e.preventDefault();
    fetch(url, { method: 'POST', body: new FormData(e.target) }).then(r => r.json()).then(res => {
        if (res.status === 'success') { toggleModal(mid, false); if (isStudentUpdate) refreshStudents(); else window.location.reload(); }
        else showFeedback('error', 'Error', res.message);
    });
}

function handleStudentAdd(e) {
    e.preventDefault();
    const secId = document.getElementById('add_student_sec_id').value;

    if (!secId && currentSection) document.getElementById('add_student_sec_id').value = currentSection.section_id;

    fetch('php/sections/add_student.php', { method: 'POST', body: new FormData(e.target) }).then(r => r.json()).then(res => {
        if (res.status === 'success') { toggleModal('addStudentModal', false); refreshStudents(); }
        else showFeedback('error', 'Error', res.message);
    });
}

function refreshStudents() {
    document.getElementById('add_student_sec_id').value = currentSection.section_id;
    fetch(`php/sections/get_students.php?section_id=${currentSection.section_id}`).then(r => r.json()).then(res => {
        if (res.data.length === 0) {
            document.getElementById('studentTableBody').innerHTML = '';
            document.getElementById('noStudentsMsg').classList.remove('hidden');
        } else {
            document.getElementById('noStudentsMsg').classList.add('hidden');
            document.getElementById('studentTableBody').innerHTML = res.data.map(s => `
                        <tr><td>${s.student_id_number}</td><td><strong>${s.last_name}, ${s.first_name}</strong></td><td><span class='badge badge-reg'>${s.status}</span></td>
                        <td><button class='btn btn-outline btn-icon' onclick='openProfile(${JSON.stringify(s)})'><i class='fa-solid fa-address-card'></i></button>
                        <button class='btn btn-outline btn-icon' onclick='openEditStudent(${JSON.stringify(s)})'><i class='fa-solid fa-user-pen'></i></button>
                        <button class='btn btn-danger btn-icon' onclick='askDelete(${s.student_id}, "student")'><i class='fa-solid fa-trash'></i></button></td></tr>
                    `).join('');
        }
    });
}

function handleEnroll(e) {
    e.preventDefault();
    fetch('php/sections/enroll_student.php', { method: 'POST', body: new FormData(e.target) }).then(r => r.json()).then(res => {
        if (res.status === 'error') showFeedback('error', 'Failed', res.message);
        else loadEnrolledSubjects(document.getElementById('enroll_sid').value);
    });
}

function openEditSubject(s) { document.getElementById('edit_sub_id').value = s.subject_id; document.getElementById('edit_sub_sched').value = s.sched_code; document.getElementById('edit_sub_code').value = s.subject_code; document.getElementById('edit_sub_name').value = s.subject_name; document.getElementById('edit_sub_start').value = s.start_time; document.getElementById('edit_sub_end').value = s.end_time; document.getElementById('edit_sub_day').value = s.sched_day; toggleModal('editSubjectModal', true); }
function openEditSection(sec) { document.getElementById('edit_sec_id').value = sec.section_id; document.getElementById('edit_sec_name').value = sec.section_name; document.getElementById('edit_sec_start').value = sec.sy_start; document.getElementById('edit_sec_end').value = sec.sy_end; document.getElementById('edit_sec_sem').value = sec.semester; toggleModal('editSectionModal', true); }

function openEditStudent(s) {
    document.getElementById('edit_stu_id').value = s.student_id;
    document.getElementById('edit_stu_num').value = s.student_id_number;
    document.getElementById('edit_stu_fname').value = s.first_name;
    document.getElementById('edit_stu_lname').value = s.last_name;
    document.getElementById('edit_stu_mi').value = s.middle_initial || ''; 
    document.getElementById('edit_stu_sex').value = s.sex;
    document.getElementById('edit_stu_status').value = s.status;
    document.getElementById('edit_stu_course').value = s.course;
    toggleModal('editStudentModal', true);
}

function openProfile(s) {
    toggleModal('studentProfileModal', true);
    let mi = s.middle_initial ? s.middle_initial + '.' : '';
    document.getElementById('pName').innerText = `${s.last_name}, ${s.first_name} ${mi}`;
    document.getElementById('pIdNum').innerText = s.student_id_number;
    document.getElementById('pCourse').innerText = s.course;
    document.getElementById('pSex').innerText = s.sex;
    document.getElementById('enroll_sid').value = s.student_id;

    document.getElementById('pProfilePic').src = "https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png?20150327203541";

    loadEnrolledSubjects(s.student_id);
}

function loadEnrolledSubjects(sid) { 
    fetch(`php/sections/get_student_subjects.php?student_id=${sid}`).then(r => r.json()).then(res => { 
        document.getElementById('pSubjectList').innerHTML = res.data.length ? res.data.map(sb => `
            <div style='background:#f8fafc; padding:10px; border-radius:12px; border:1px solid #eef2f6; display:flex; justify-content:space-between; align-items:center;'>
                <div>
                    <strong>${sb.subject_code}</strong><br>
                    <small>${sb.subject_name}</small>
                </div>
                <div style="text-align:right">
                    <span style='font-size:0.7rem; font-weight:700; display:block;'>${sb.sched_day}</span>
                    <button class="btn btn-danger btn-icon" style="padding:2px 6px; font-size:0.7rem; margin-top:5px;" onclick="removeSubject(${sid}, ${sb.subject_id})">
                        <i class="fa-solid fa-trash"></i> Remove
                    </button>
                </div>
            </div>`).join('') : '<p style="text-align:center;font-size:0.8rem;">No subjects.</p>'; 
    }); 
}

function removeSubject(stuId, subId) {
    if(!confirm('Are you sure you want to remove this subject from the student?')) return;
    
    const fd = new FormData();
    fd.append('student_id', stuId);
    fd.append('subject_id', subId);
    
    fetch('php/sections/remove_student_subject.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.status === 'success') {
                loadEnrolledSubjects(stuId);
            } else {
                showFeedback('error', 'Error', res.message);
            }
        });
}

function askDelete(id, type) { deleteTarget = { id, type }; toggleModal('deleteModal', true); }
document.getElementById('confirmDeleteBtn').onclick = () => { const url = deleteTarget.type === 'subject' ? 'php/subjects/delete_subject.php' : (deleteTarget.type === 'section' ? 'php/sections/delete_section.php' : 'php/sections/delete_student.php'); fetch(`${url}?id=${deleteTarget.id}`).then(() => { toggleModal('deleteModal', false); if (deleteTarget.type === 'student') refreshStudents(); else window.location.reload(); }); };
function filterStudentTable() { const val = document.getElementById('studentSearchInput').value.toLowerCase(); document.querySelectorAll('#studentTableBody tr').forEach(r => r.style.display = r.innerText.toLowerCase().includes(val) ? '' : 'none'); }
function filterStudentTableAnalytics() { const val = document.getElementById('AnastudentSearchInput').value.toLowerCase(); document.querySelectorAll('#anaResultTableBody').forEach(r => r.style.display = r.innerText.toLowerCase().includes(val) ? '' : 'none'); }

window.onload = restoreState;