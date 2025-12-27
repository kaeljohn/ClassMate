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

function openAddSectionModal() {
    document.getElementById('addSectionModal').style.display = 'block';
}

function closeAddSectionModal() {
    document.getElementById('addSectionModal').style.display = 'none';
}

function viewStudentsInSection(sectionId) {
    document.getElementById('viewStudentsModal').style.display = 'block';
    document.getElementById('hidden_section_id').value = sectionId;
    
    // Fetch students in this section using AJAX
    fetch('php/get_section_students.php?section_id=' + sectionId)
        .then(response => response.text())
        .then(data => {
            document.getElementById('sectionStudentsList').innerHTML = data;
        });
}

function closeViewStudentsModal() {
    document.getElementById('viewStudentsModal').style.display = 'none';
}

function filterStudents() {
    let input = document.getElementById("studentSearch").value.toLowerCase();
    let rows = document.querySelectorAll("#enrollmentTable tbody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

function toggleAll(source) {
    let checkboxes = document.querySelectorAll('input[name="student_ids[]"]');
    for(let checkbox of checkboxes) {
        checkbox.checked = source.checked;
    }
}

// Function to open modal and set the section ID
function openEnrollment(sectionId) {
    document.getElementById('active_section_id').value = sectionId;
    document.getElementById('enrollmentModal').style.display = 'block';
}