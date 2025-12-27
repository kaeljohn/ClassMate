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