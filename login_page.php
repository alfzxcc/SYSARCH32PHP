<?php include 'header.php'; ?>

<div class="login-center-wrapper">
    <div class="login-card">
        <div class="card-header">
            <img src="ccs.jpg" alt="Project Logo" class="app-logo">
        </div>

        <form action="login_process.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            
            <div class="btn-group">
                <button type="submit" class="btn-login">Login</button>
                <a href="register.php" class="btn-register">Create New Account</a>
            </div>
        </form>
        
        <div class="footer-text">
            SYSARCH32PHP Project Environment
        </div>
    </div>
</div>

</main>
</body>
</html>