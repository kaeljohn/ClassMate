function showFeedback(type, title, msg) {
    const m = document.getElementById('universalModal');
    const icon = type === 'success'
        ? '<i class="fa-solid fa-check-circle" style="color:#22c55e"></i>'
        : '<i class="fa-solid fa-circle-xmark" style="color:#ef4444"></i>';

    document.getElementById('feedbackIcon').innerHTML = icon;
    document.getElementById('modalTitle').innerText = title;
    document.getElementById('modalMsg').innerText = msg;

    if (type === 'success') {
        document.getElementById('universalModalFooter').innerHTML = `<button class="btn-primary" onclick="window.location.href='instructor-home.php'">Go to Dashboard</button>`;
    } else {
        document.getElementById('universalModalFooter').innerHTML = `<button class="btn-primary" onclick="document.getElementById('universalModal').style.display='none'">Try Again</button>`;
    }
    m.style.display = 'flex';
}

function handleLogin(e) {
    e.preventDefault();
    const fd = new FormData(e.target);

    fetch('php/instructor-login.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                showFeedback('success', 'Login Successful', 'Redirecting to your dashboard...');
                setTimeout(() => window.location.href = 'instructor-home.php', 1500);
            } else {
                showFeedback('error', 'Access Denied', res.message);
            }
        })
        .catch(err => showFeedback('error', 'Network Error', 'Could not connect to server.'));
}