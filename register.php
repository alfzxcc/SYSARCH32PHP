<?php include 'header.php'; ?>

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

</body>
</html>