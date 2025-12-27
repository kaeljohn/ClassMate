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
