<?php include 'header.php'; ?>

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

                <input type="text" name="course" placeholder="Course (e.g., BSIT)" required>
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

</main>
</body>
</html>