// 1. Sidebar Navigation
document.querySelectorAll(".nav-btn").forEach((btn) => {
  btn.addEventListener("click", function (e) {
    e.preventDefault();
    const target = this.getAttribute("data-target");
    document
      .querySelectorAll(".content-section")
      .forEach((s) => (s.style.display = "none"));
    document.getElementById(target).style.display = "block";
    document
      .querySelectorAll(".nav-btn")
      .forEach((b) => b.classList.remove("active"));
    this.classList.add("active");
  });
});

function showFeedback(type, title, message) {
  const modal = document.getElementById("universalModal");
  const card = document.getElementById("feedbackCard");
  card.className = "feedback-card " + type;
  document.getElementById("modalTitle").innerText = title;
  document.getElementById("modalMsg").innerText = message;
  document.getElementById("feedbackIcon").innerHTML =
    type === "error"
      ? '<i class="fa-solid fa-triangle-exclamation"></i>'
      : '<i class="fa-solid fa-circle-check"></i>';
  modal.style.display = "flex";
}

function closeFeedback() {
    <div id="universalModal" class="modal-overlay" style="display: none;">
    <div id="feedbackCard" class="feedback-card">
        <div id="feedbackIcon" class="feedback-icon"></div>
        <h2 id="modalTitle" style="margin-bottom: 10px; color: #1e293b;"></h2>
        <p id="modalMsg" style="color: #64748b;"></p>
        <div id="modalFooter">
            <button class="feedback-btn" onclick="closeFeedback()">Acknowledge</button>
        </div>
    </div>
</div>
}

document
  .getElementById("quickRegisterForm")
  .addEventListener("submit", function (e) {
    e.preventDefault();
    const btn = document.getElementById("regBtn");
    const errorDiv = document.getElementById("modalError");

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

    fetch("php/add_new_student.php", {
      method: "POST",
      body: new FormData(this),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "exists") {
          document.getElementById("errorText").innerText = data.message;
          errorDiv.style.display = "block";
          btn.disabled = false;
          btn.innerText = "Register Student";
        } else if (data.status === "success") {
          closeAddStudentModal();
          showFeedback("success", "Success!", "Student added to database.");
          setTimeout(() => location.reload(), 2000);
        }
      })
      .catch(() =>
        showFeedback("error", "System Error", "Could not connect to server.")
      );
  });

function filterEnrollmentTable() {
  let input = document.getElementById("enrollmentSearch").value.toLowerCase();
  let rows = document.querySelectorAll("#mainEnrollmentTable tbody tr");
  rows.forEach((row) => {
    row.style.display = row.innerText.toLowerCase().includes(input)
      ? ""
      : "none";
  });
}

function toggleSelectAll(source) {
  document.querySelectorAll(".student-checkbox").forEach((cb) => {
    if (cb.closest("tr").style.display !== "none") cb.checked = source.checked;
  });
}

// AJAX for Adding Subject
const subjectForm = document.querySelector("#addSubjectModal form");
if (subjectForm) {
  subjectForm.addEventListener("submit", function (e) {
    e.preventDefault();
    fetch("php/add_subject.php", { method: "POST", body: new FormData(this) })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "success") {
          closeAddSubjectModal();
          showFeedback("success", "Subject Added", data.message);
          setTimeout(() => location.reload(), 1500);
        } else {
          showFeedback("error", "Failed", data.message);
        }
      });
  });
}

// AJAX for Adding Section
const sectionForm = document.querySelector("#addSectionModal form");
if (sectionForm) {
  sectionForm.addEventListener("submit", function (e) {
    e.preventDefault();
    fetch("php/add_section.php", { method: "POST", body: new FormData(this) })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "success") {
          closeAddSectionModal();
          showFeedback("success", "Section Created", data.message);
          setTimeout(() => location.reload(), 1500);
        } else {
          showFeedback("error", "Failed", data.message);
        }
      });
  });
}

function confirmDelete(id, code) {
    const modal = document.getElementById('universalModal');
    const card = document.getElementById('feedbackCard');
    
    // 1. Set the visual style and text
    card.className = 'feedback-card error';
    document.getElementById('feedbackIcon').innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
    document.getElementById('modalTitle').innerText = 'Confirm Deletion';
    document.getElementById('modalMsg').innerText = `Are you sure you want to delete ${code}? This will also remove all sections associated with it.`;

    // 2. Clear and inject the dual-button layout
    const footer = document.querySelector('.feedback-btn').parentElement;
    footer.id = "modalFooter"; // Giving it an ID for easy resetting later
    footer.innerHTML = `
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button class="feedback-btn" style="background: #64748b; margin-top:0;" onclick="closeFeedback()">Cancel</button>
            <button class="feedback-btn" style="background: #ef4444; margin-top:0;" onclick="executeDelete(${id})">Yes, Delete</button>
        </div>
    `;
    
    modal.style.display = 'flex';
}

function executeDelete(id) {
  fetch(`php/delete_subject.php?id=${id}`, { method: "GET" })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        // Remove the row from the table instantly
        document.getElementById(`subject-row-${id}`).remove();
        showFeedback("success", "Deleted!", "The subject has been removed.");

        // Reset the modal buttons back to normal for the next time it's used
        setTimeout(() => location.reload(), 1500);
      } else {
        showFeedback("error", "Error", data.message);
      }
    });
}

// Modal Controls
function openAddSubjectModal() {
  document.getElementById("addSubjectModal").style.display = "block";
}
function closeAddSubjectModal() {
  document.getElementById("addSubjectModal").style.display = "none";
}
function openAddSectionModal() {
  document.getElementById("addSectionModal").style.display = "block";
}
function closeAddSectionModal() {
  document.getElementById("addSectionModal").style.display = "none";
}
function openAddStudentModal() {
  document.getElementById("addStudentModal").style.display = "block";
}
function closeAddStudentModal() {
  document.getElementById("addStudentModal").style.display = "none";
}
