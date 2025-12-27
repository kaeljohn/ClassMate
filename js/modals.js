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