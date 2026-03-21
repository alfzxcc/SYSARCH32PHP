<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.php");
    exit();
}
include 'header.php'; 
require_once 'db_connect.php'; // Required to fetch announcements
?>

<div class="dashboard-container">
    <aside class="dash-column side-column">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-icon"><i class="fas fa-user-circle"></i></div>
                <h3>Student Information</h3>
            </div>
            
            <div class="info-group">
                <label><i class="fas fa-id-card"></i> ID NUMBER</label>
                <span><?php echo $_SESSION['user_id']; ?></span>
            </div>

            <div class="info-group">
                <label><i class="fas fa-user"></i> FULL NAME</label>
                <span><?php echo $_SESSION['firstName'] . " " . $_SESSION['midName'] . " " . $_SESSION['lastName']; ?></span>
            </div>

            <div class="info-group">
                <label><i class="fas fa-graduation-cap"></i> COURSE</label>
                <span><?php echo $_SESSION['course']; ?></span>
            </div>

            <div class="info-group">
                <label><i class="fas fa-level-up-alt"></i> YEAR LEVEL</label>
                <span>Year <?php echo $_SESSION['course_level'] ?? 'N/A'; ?></span>
            </div>

            <div class="info-group highlight">
                <label><i class="fas fa-hourglass-half"></i> REMAINING SESSIONS</label>
                <span class="session-count">
                    <?php 
                        // This checks if 'sessions' exists. If not, it displays 30 instead of crashing.
                        echo isset($_SESSION['sessions']) ? $_SESSION['sessions'] : '30'; 
                    ?>
                </span> 
            </div>
        </div>
        <div style="margin-top: 20px;">
            <a href="student_sitin.php" class="btn-login" style="display: block; text-align: center; text-decoration: none; background: #003366;">
                <i class="fas fa-plus"></i> Request New Sit-in
            </a>
        </div>
    </aside>

    <section class="dash-column center-column">
        <div class="announcement-card">
            <div class="card-header-main">
                <h2><i class="fas fa-bullhorn"></i> Announcements</h2>
                <span class="view-all">Recent Updates</span>
            </div>

            <div class="announcement-feed">
                <?php
                // Fetch latest 5 announcements
                $sql = "SELECT * FROM announcements ORDER BY date_posted DESC LIMIT 5";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        // Set CSS color class based on category
                        $category = $row['category'];
                        $class = "";
                        if ($category == 'Maintenance') $class = "warning";
                        if ($category == 'Event') $class = "info";

                        echo '
                        <div class="announcement-box">
                            <div class="post-meta">
                                <span class="post-category ' . $class . '">' . htmlspecialchars($category) . '</span>
                                <span class="post-time">' . date("M d, Y", strtotime($row['date_posted'])) . '</span>
                            </div>
                            <h4>' . htmlspecialchars($row['title']) . '</h4>
                            <p>' . nl2br(htmlspecialchars($row['content'])) . '</p>
                        </div>';
                    }
                } else {
                    echo '<p style="text-align:center; padding: 20px; color: #777;">No announcements at this time.</p>';
                }
                ?>
            </div>
        </div>
    </section>

    <aside class="dash-column side-column">
        <div class="rules-card">
            <div class="rules-header">
                <img src="uc2.jpg" alt="UC Logo" class="rules-logo"> 
                <h4>COLLEGE OF INFORMATION & COMPUTER STUDIES</h4>
                <hr>
                <p class="rules-title">Laboratory Rules and Regulations</p>
            </div>

            <div class="rules-scroll-area">
                <div class="rule-item"><span class="rule-num">1</span><p>Maintain silence and proper decorum.</p></div>
                <div class="rule-item"><span class="rule-num">2</span><p>Games are not allowed inside the lab.</p></div>
                <div class="rule-item"><span class="rule-num">3</span><p>Surfing is only allowed with permission.</p></div>
                <div class="rule-item"><span class="rule-num">4</span><p>Eating and drinking are strictly prohibited.</p></div>
                <div class="rule-item"><span class="rule-num">5</span><p>Keep workstations clean and orderly.</p></div>
                <div class="rule-item"><span class="rule-num">6</span><p>Damage to equipment is the student's responsibility.</p></div>
            </div>
            
            <div class="rules-footer">
                <p>Please observe these rules accordingly.</p>
            </div>
        </div>
    </aside>
</div>