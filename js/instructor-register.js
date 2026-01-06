function showFeedback(type, title, msg) {
    const m = document.getElementById('universalModal');
    const icon = type === 'success'
        ? '<i class="fa-solid fa-check-circle" style="color:#22c55e"></i>'
        : '<i class="fa-solid fa-circle-xmark" style="color:#ef4444"></i>';

    document.getElementById('feedbackIcon').innerHTML = icon;
    document.getElementById('modalTitle').innerText = title;
    document.getElementById('modalMsg').innerText = msg;

    if (type === 'success') {
        document.getElementById('universalModalFooter').innerHTML = `<button class="btn-primary" onclick="window.location.href='instructor-login.php'">Go to Login</button>`;
    } else {
        document.getElementById('universalModalFooter').innerHTML = `<button class="btn-primary" onclick="document.getElementById('universalModal').style.display='none'">Try Again</button>`;
    }
    m.style.display = 'flex';
}

function handleRegister(e) {
    e.preventDefault();
    const fd = new FormData(e.target);

    fetch('php/instructor-register.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                showFeedback('success', 'Account Created', 'You can now log in with your credentials.');
            } else {
                showFeedback('error', 'Registration Failed', res.message);
            }
        })
        .catch(err => showFeedback('error', 'Network Error', 'Could not connect to server.'));
}