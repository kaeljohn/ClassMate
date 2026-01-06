<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ClassMate | Instructor Registration</title>
    <link rel="icon" type="image/svg+xml" href="svg/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css/instructor-register.css">
</head>
<body>

<div class="custom-shape-divider-top-1766060304">
    <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" class="shape-fill"></path>
        <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" class="shape-fill"></path>
        <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" class="shape-fill"></path>
    </svg>
</div>

<!-- Feedback Modal -->
<div id="universalModal" class="modal">
    <div class="modal-content">
        <div id="feedbackIcon" style="font-size:3rem; margin-bottom:15px;"></div>
        <h3 id="modalTitle" style="margin-bottom:10px; color:var(--text-main); font-weight:800;"></h3>
        <p id="modalMsg" style="color:var(--text-muted); margin-bottom:20px; font-size:0.95rem;"></p>
        <div id="universalModalFooter"></div>
    </div>
</div>

<header>
    <h1>
        <a href="index.html" class="logo-link"><i class="fa-solid fa-graduation-cap"></i> ClassMate</a>
    </h1>
</header>

<section class="login-container">
    <div class="login-box">
        <a href="get-started.html" class="back-link"><i class="fa-solid fa-arrow-left"></i> Go Back</a>
        <h2>Join ClassMate</h2>
        <p class="subtitle">Create your instructor account</p>

        <form id="registerForm" onsubmit="handleRegister(event)">
            <!-- Name Fields -->
            <div class="row-group">
                <div class="input-group">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="firstName" placeholder="First Name" required>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="lastName" placeholder="Last Name" required>
                </div>
            </div>
            
            <div class="row-group">
                <div class="input-group">
                    <i class="fa-solid fa-font"></i>
                    <input type="text" name="middleInitial" placeholder="M.I." maxlength="2">
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-venus-mars"></i>
                    <select name="sex" style="height:48px;">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>

            <div class="input-group">
                <i class="fa-solid fa-id-card"></i>
                <!-- Added pattern, inputmode, and oninput to enforce numericality -->
                <input type="text" name="instructor_id" placeholder="Create ID" 
                       pattern="[0-9]+" 
                       inputmode="numeric" 
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')" 
                       required>
            </div>
            
            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Create Password" required>
            </div>
            
            <button type="submit" class="btn-primary">Register</button>
        </form>
        
        <p style="text-align:center; margin-top:20px; font-size:0.85rem; color:var(--text-muted);">
            Already have an account? <a href="instructor-login.php" style="color:var(--primary); font-weight:700; text-decoration:none;">Log In</a>
        </p>
    </div>
</section>

<script src="js/instructor-register.js"></script>

</body>
</html>