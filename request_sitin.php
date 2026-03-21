<?php 
session_start();
include 'header.php'; 
?>

<div class="login-center-wrapper">
    <div class="login-card" style="width: 450px;">
        <h3><i class="fas fa-desktop"></i> Start Sit-in Session</h3>
        <p style="font-size: 0.9rem; color: #666;">Current Balance: <strong><?php echo $_SESSION['sessions']; ?></strong> sessions</p>
        <hr>
        
        <form action="process_sitin.php" method="POST">
            <label>Select Laboratory</label>
            <select name="lab_room" required>
                <option value="Lab 524">Lab 524</option>
                <option value="Lab 542">Lab 542</option>
                <option value="Lab 526">Lab 526</option>
                <option value="Lab 544">Lab 544</option>
            </select>

            <label>Purpose</label>
            <select name="purpose" required>
                <option value="C++ Programming">C++ Programming</option>
                <option value="Java Programming">Java Programming</option>
                <option value="Web Development">Web Development</option>
                <option value="Research">Research</option>
            </select>

            <div class="btn-group">
                <button type="submit" class="btn-login" style="background: #27ae60;">Confirm & Sit-in</button>
                <a href="dashboard.php" class="btn-register">Cancel</a>
            </div>
        </form>
    </div>
</div>