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

// ADD NEW STUDENT

// 1. Search Function for the Enrollment Table
function filterEnrollmentTable() {
    let input = document.getElementById("enrollmentSearchInput").value.toLowerCase();
    let rows = document.querySelectorAll("#mainEnrollmentTable tbody tr:not(.no-data)");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

// 2. Select All Checkboxes Function
function toggleSelectAll(source) {
    let checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(cb => {
        // Only toggle visible checkboxes (in case user has filtered the list)
        if (cb.closest('tr').style.display !== 'none') {
            cb.checked = source.checked;
        }
    });
}

// 3. Modal Controls
function openAddStudentModal() {
    document.getElementById('addStudentModal').style.display = 'block';
}

function closeAddStudentModal() {
    document.getElementById('addStudentModal').style.display = 'none';
}