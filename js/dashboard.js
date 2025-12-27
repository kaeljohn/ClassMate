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

// 1. Universal Feedback Trigger
function showFeedback(type, title, message) {
    const modal = document.getElementById('universalModal');
    const card = document.getElementById('feedbackCard');
    const icon = document.getElementById('feedbackIcon');
    const titleEl = document.getElementById('modalTitle');
    const msgEl = document.getElementById('modalMsg');

    card.className = 'feedback-card ' + type;
    titleEl.innerText = title;
    msgEl.innerText = message;
    
    icon.innerHTML = (type === 'error') 
        ? '<i class="fa-solid fa-triangle-exclamation" style="color: #ef4444;"></i>' 
        : '<i class="fa-solid fa-circle-check" style="color: #10b981;"></i>';

    modal.style.display = 'flex';
}

function closeFeedback() {
    document.getElementById('universalModal').style.display = 'none';
}

// 2. Handle the Registration via AJAX
const registrationForm = document.querySelector('#addStudentModal form');
if(registrationForm) {
    registrationForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('php/add_new_student.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'exists') {
                showFeedback('error', 'Action Denied', 'This student is already in the system.');
            } else if (data.status === 'success') {
                showFeedback('success', 'Success!', 'Student registered successfully.');
                closeAddStudentModal(); // Closes the entry modal
                registrationForm.reset(); // Clears the inputs
                // Option: location.reload(); if you want the table to update immediately
            } else {
                showFeedback('error', 'Error', 'Something went wrong.');
            }
        })
        .catch(err => {
            showFeedback('error', 'System Error', 'Could not connect to the server.');
        });
    });
}