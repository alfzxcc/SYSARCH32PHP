<?php 
include 'header.php'; 

// Optional: Redirect users if they are already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 1) {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}
?>

<div class="login-page-container">
    <div class="login-card">
        <div class="card-header">
            <img src="ccs.jpg" alt="CCS Logo" class="app-logo">
            <h2 style="color: #003366; margin-top: 10px; font-size: 1.2rem;">Portal Login</h2>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div style="color: #e74c3c; text-align: center; font-size: 0.85rem; margin-bottom: 10px; background: #fdeaea; padding: 8px; border-radius: 5px;">
                <?php 
                    if ($_GET['error'] == 'unauthorized') echo "Access Denied: Admins Only.";
                    else echo "Invalid ID or Password.";
                ?>
            </div>
        <?php endif; ?>

        <form action="login_process.php" method="POST">
            <div class="input-group">
                <i class="fas fa-id-badge" style="position: absolute; margin: 22px 0 0 12px; color: #aaa;"></i>
                <input type="text" name="username" placeholder="ID Number" style="padding-left: 35px;" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock" style="position: absolute; margin: 22px 0 0 12px; color: #aaa;"></i>
                <input type="password" name="password" placeholder="Password" style="padding-left: 35px;" required>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn-login">Sign In</button>
                <a href="register.php" class="btn-register">Create New Account</a>
            </div>
        </form>
        
        <div class="footer-text">
            <strong>UC-CICS</strong><br>
            SYSARCH32PHP Project Environment
        </div>
    </div>
</div>

</main>
</body>
</html>