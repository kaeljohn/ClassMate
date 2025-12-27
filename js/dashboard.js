console.log("ClassMate Dashboard Script Loaded!");

// navigation
document.addEventListener("DOMContentLoaded", function() {
    const navButtons = document.querySelectorAll('.nav-btn');
    const sections = document.querySelectorAll('.content-section');

    navButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            const targetId = this.getAttribute('data-target');
            
            navButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            sections.forEach(section => {
                section.style.display = 'none';
            });

            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.style.display = 'block';
                
                // load data when switching tabs
                if (targetId === 'students') {
                    loadSections();
                } else if (targetId === 'courses') {
                    loadSubjects();
                }
            }
        });
    });

    // load subjects sa page load (default active)
    loadSubjects();
    
    // form listener setup
    setupFormListeners();
});

// Function to show the feedback modal
function showFeedback(type, title, message) {
    const modal = document.getElementById('universalModal');
    const card = document.getElementById('feedbackCard');
    const icon = document.getElementById('feedbackIcon');
    const titleEl = document.getElementById('modalTitle');
    const msgEl = document.getElementById('modalMsg');

    // Reset classes
    card.className = 'feedback-card ' + type;
    
    // Set Content
    titleEl.innerText = title;
    msgEl.innerText = message;
    icon.innerHTML = (type === 'error') 
        ? '<i class="fa-solid fa-triangle-exclamation"></i>' 
        : '<i class="fa-solid fa-circle-check"></i>';

    // Show Modal
    modal.style.display = 'flex';
}

function closeFeedback() {
    document.getElementById('universalModal').style.display = 'none';
}

// Intercept the Registration Form
document.querySelector('#addStudentModal form').addEventListener('submit', function(e) {
    e.preventDefault(); // Stop page reload

    const formData = new FormData(this);

    fetch('php/add_new_student.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) // We expect JSON back from PHP
    .then(data => {
        if (data.status === 'exists') {
            showFeedback('error', 'Already Exists', 'This student is already in the database.');
        } else if (data.status === 'success') {
            showFeedback('success', 'Registered!', 'Student has been added successfully.');
            closeAddStudentModal(); // Close the input form
            // Optionally: reload the table or add the row via JS
        }
    })
    .catch(error => {
        showFeedback('error', 'System Error', 'Something went wrong on the server.');
    });
});