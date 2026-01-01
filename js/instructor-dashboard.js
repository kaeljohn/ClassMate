// 1. Sidebar Navigation
document.querySelectorAll(".nav-btn").forEach((btn) => {
  btn.addEventListener("click", function (e) {
    e.preventDefault();
    const target = this.getAttribute("data-target");
    
    // Hide all sections
    document.querySelectorAll(".content-section").forEach((s) => (s.style.display = "none"));
    
    // Show target section
    const targetSection = document.getElementById(target);
    if (targetSection) {
      targetSection.style.display = "block";
    }
    
    // Update active button state
    document.querySelectorAll(".nav-btn").forEach((b) => b.classList.remove("active"));
    this.classList.add("active");
  });
});

// 2. Feedback Modal System
function showFeedback(type, title, message) {
  const modal = document.getElementById("universalModal");
  const card = document.getElementById("feedbackCard");
  const iconContainer = document.getElementById("feedbackIcon");
  
  // Clean up previous classes and set new ones (success, error, info)
  card.className = "feedback-card " + type;
  
  document.getElementById("modalTitle").innerText = title;
  document.getElementById("modalMsg").innerText = message;
  
  // Set appropriate icon based on type
  if (type === "error") {
    iconContainer.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i>';
  } else if (type === "success") {
    iconContainer.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
  } else {
    iconContainer.innerHTML = '<i class="fa-solid fa-circle-info"></i>';
  }
  
  modal.style.display = "flex";
}

function closeFeedback() {
  document.getElementById('universalModal').style.display = 'none';
  // Reset footer to default button (clearing any custom confirm/cancel buttons)
  document.getElementById('universalModalFooter').innerHTML =
    `<button class="feedback-btn" onclick="closeFeedback()">Acknowledge</button>`;
}

// 3. Subject Management (Add/Edit/Delete)
function openAddSubjectModal() {
  document.getElementById("subjectModalTitle").innerText = "Add New Subject";
  document.getElementById("modalSubjectId").value = "";
  document.getElementById("modalSubjectCode").value = "";
  document.getElementById("modalSubjectName").value = "";
  document.getElementById("addSubjectModal").style.display = "block";
}

function openEditSubjectModal(id, code, name) {
  document.getElementById("subjectModalTitle").innerText = "Edit Subject";
  document.getElementById("modalSubjectId").value = id;
  document.getElementById("modalSubjectCode").value = code;
  document.getElementById("modalSubjectName").value = name;
  document.getElementById("addSubjectModal").style.display = "block";
}

function closeAddSubjectModal() {
  document.getElementById("addSubjectModal").style.display = "none";
}

function confirmDelete(id, code) {
  const modal = document.getElementById("universalModal");
  const card = document.getElementById("feedbackCard");
  const footer = document.getElementById("universalModalFooter");
  const iconContainer = document.getElementById("feedbackIcon");

  // Style as error/warning for deletion
  card.className = "feedback-card error";
  document.getElementById("modalTitle").innerText = "Confirm Delete";
  document.getElementById("modalMsg").innerText = `Are you sure you want to delete subject ${code}? This action cannot be undone.`;
  iconContainer.innerHTML = '<i class="fa-solid fa-trash-can"></i>';

  // Use a flex container for buttons to ensure they align nicely like the screenshot
  footer.innerHTML = `
        <div style="display:flex; gap:12px; margin-top:20px; width: 100%;">
            <button class="feedback-btn" style="background: #e2e8f0; color: #475569; margin-top:0; flex: 1;" onclick="closeFeedback()">Cancel</button>
            <button class="feedback-btn" style="background: #ef4444; color: white; margin-top:0; flex: 1;" onclick="executeDelete(${id})">Yes, Delete</button>
        </div>
    `;
  modal.style.display = 'flex';
}

function executeDelete(id) {
  fetch(`php/delete_subject.php?id=${id}`)
    .then((res) => res.json())
    .then((data) => {
      // Prepare for the result message
      const footer = document.getElementById('universalModalFooter');
      footer.innerHTML = `<button class="feedback-btn" onclick="closeFeedbackAndReload()">Acknowledge</button>`;
        
      if (data.status === "success" || data.success) {
        showFeedback("success", "Deleted!", "The subject has been removed.");
      } else {
        showFeedback("error", "Error", data.message || "Failed to delete subject.");
      }
    })
    .catch(err => {
        document.getElementById('universalModalFooter').innerHTML =
            `<button class="feedback-btn" onclick="closeFeedback()">Acknowledge</button>`;
        showFeedback("error", "Connection Error", "Could not reach the server.");
    });
}

// Special closer for reload-heavy actions
function closeFeedbackAndReload() {
    closeFeedback();
    location.reload();
}

function viewSubject(id) {
  // Navigate to sections tab
  const sectionBtn = document.querySelector('.nav-btn[data-target="students"]');
  if (sectionBtn) sectionBtn.click();
}

// 4. Section Management
function openAddSectionModal() {
  document.getElementById("addSectionModal").style.display = "block";
}

function closeAddSectionModal() {
  document.getElementById("addSectionModal").style.display = "none";
}

function updateEndYear() {
  const startSelect = document.getElementById('sy_start');
  const endSelect = document.getElementById('sy_end');
  if (startSelect && endSelect) {
    const startYear = parseInt(startSelect.value);
    endSelect.value = startYear + 1;
  }
}

// 5. Student Management
function openAddStudentModal() {
  document.getElementById("addStudentModal").style.display = "block";
}

function closeAddStudentModal() {
  document.getElementById("addStudentModal").style.display = "none";
}

// Close modals when clicking outside
window.onclick = function (event) {
  const modals = document.querySelectorAll('.modal');
  modals.forEach(modal => {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  });
};