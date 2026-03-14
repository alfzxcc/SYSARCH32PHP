<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
// Security Check: If no session exists, kick them back to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.php");
    exit();
}
include 'header.php'; 
?>


<div class="dashboard-wrapper">
    <aside class="dash-sidebar">
        <div class="user-profile">
            <div class="profile-pic"><i class="fas fa-user-graduate"></i></div>
            <h4><?php echo $_SESSION['firstname'] . " " . $_SESSION['lastname']; ?></h4>
            <p><?php echo $_SESSION['course']; ?></p>
        </div>
        <nav class="dash-nav">
            <a href="dashboard.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="edit_profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a>
            <a href="history.php"><i class="fas fa-history"></i> Sit-in History</a>
            <a href="reservation.php"><i class="fas fa-calendar-check"></i> Reservation</a>
            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <section class="dash-main">
        <div class="welcome-banner">
            <h2>Welcome back, <?php echo $_SESSION['firstname']; ?>!</h2>
            <p>Ready for your sit-in session today?</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <div class="stat-info">
                    <h3>30</h3>
                    <p>Remaining Hours</p>
                </div>
            </div>
            <div class="stat-card active-session">
                <i class="fas fa-desktop"></i>
                <div class="stat-info">
                    <h3>Status</h3>
                    <p>Not Logged In</p>
                </div>
            </div>
        </div>

        <div class="action-section">
            <div class="announcement-mini">
                <h4><i class="fas fa-info-circle"></i> Lab Rules</h4>
                <ul>
                    <li>Proper attire is required.</li>
                    <li>No food or drinks inside the lab.</li>
                    <li>Clean your station after use.</li>
                </ul>
            </div>
        </div>
    </section>
</div>

</main>
</body>
</html>