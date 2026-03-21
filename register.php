<?php include 'header.php'; ?>

<<<<<<< HEAD
<div class="login-card">
    <h2>Create Account</h2>
    
    <form action="register_process.php" method="POST">
        <input type="text" name="new_user" placeholder="Choose Username" required>
        <input type="password" name="new_pass" placeholder="Choose Password" required>
        
        <div class="btn-group">
            <button type="submit" class="btn-login" style="background: #27ae60;">Register</button>
            <a href="index.php" class="btn-register">Back to Login</a>
        </div>
    </form>
    
    <div class="footer-text">
        SYSARCH32PHP Registration Portal
    </div>
</div>

=======
<div class="login-center-wrapper">
    <div class="register-card">
        <div class="card-header">
            <h2 class="app-title">Student Registration</h2>
            <p class="form-subtitle">CCS Sit-in Monitoring System</p>
        </div>

        <form action="register_process.php" method="POST">
            <div class="form-grid">
                <div class="input-full">
                    <input type="text" name="id_num" placeholder="ID Number (e.g., 21000123)" required>
                </div>

                <input type="text" name="firstname" placeholder="First Name" required>
                <input type="text" name="lastname" placeholder="Last Name" required>
                <input type="text" name="midname" placeholder="Middle Name">

                <select name="course" id="courseSelect" onchange="toggleCustomCourse()" required>
                    <option value="" disabled selected>Select Course</option>
                    <option value="BSIT">BS Information Technology</option>
                    <option value="BSCS">BS Computer Science</option>
                    <option value="BSIS">BS Information Systems</option>
                    <option value="ACT">Associate in Computer Technology</option>
                    <option value="Other">Other (Please Specify)</option>
                </select>

                <div id="customCourseWrapper" class="input-full" style="display: none;">
                    <input type="text" name="custom_course" id="customCourseInput" placeholder="Specify your Course">
                </div>

                <input type="number" name="course_level" placeholder="Year Level (1-4)" min="1" max="4" required>

                <input type="email" name="email" placeholder="Email Address" required>
                
                <div class="input-full">
                    <input type="text" name="address" placeholder="Home Address" required>
                </div>

                <input type="password" name="pass" placeholder="Password" required>
                <input type="password" name="confirm_pass" placeholder="Repeat Password" required>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn-login">Create Account</button>
                <a href="login_page.php" class="btn-register">Already have an account? Login</a>
            </div>
        </form>
        
        <div class="footer-text">
            Please ensure all details match your Study Load.
        </div>
    </div>
</div>

<script>
function toggleCustomCourse() {
    const select = document.getElementById('courseSelect');
    const wrapper = document.getElementById('customCourseWrapper');
    const input = document.getElementById('customCourseInput');

    if (select.value === 'Other') {
        wrapper.style.display = 'block';
        input.setAttribute('required', 'required');
    } else {
        wrapper.style.display = 'none';
        input.removeAttribute('required');
        input.value = ''; // Clear input if user switches back to preset
    }
}
</script>

</main>
>>>>>>> master
</body>
</html>