// Function to show the Add Subject modal
function openAddSubjectModal() {
    const modal = document.getElementById('addSubjectModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

// Function to hide the Add Subject modal
function closeAddSubjectModal() {
    const modal = document.getElementById('addSubjectModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close the modal if the user clicks the dark background area
window.onclick = function(event) {
    const modal = document.getElementById('addSubjectModal');
    if (event.target === modal) {
        modal.style.display = "none";
    }
}

// Check for URL parameters to show alerts
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('success')) {
    alert("Subject added successfully!");
}
if (urlParams.has('deleted')) {
    alert("Subject deleted successfully!");
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Basic Sidebar Tab Switching
    const navBtns = document.querySelectorAll('.nav-btn');
    const sections = document.querySelectorAll('.content-section');

    navBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const target = btn.getAttribute('data-target');

            // Remove active class from all buttons and hide sections
            navBtns.forEach(b => b.classList.remove('active'));
            sections.forEach(s => s.style.display = 'none');

            // Add active class and show target section
            btn.classList.add('active');
            document.getElementById(target).style.display = 'block';
        });
    });
});

// 2. Specific function for the "View Students" button
function viewStudentsBySubject(subjectId) {
    // Switch tab UI to "Students"
    const studentBtn = document.querySelector('[data-target="students"]');
    studentBtn.click(); 

    // Fetch students using AJAX
    fetch(`php/get_students.php?subject_id=${subjectId}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('studentsTableBody');
            tbody.innerHTML = ''; // Clear current table

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No students enrolled in this subject.</td></tr>';
            } else {
                data.forEach(student => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${student.student_number}</td>
                            <td>${student.full_name}</td>
                            <td>${student.email}</td>
                            <td><button class="btn btn-sm btn-danger">Unenroll</button></td>
                        </tr>
                    `;
                });
            }
        });
}