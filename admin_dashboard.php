<?php
include 'admin_check.php';
require_once 'db_connect.php';
$conn->set_charset("utf8mb4");

// 1. Count registered students accurately
$totalStudents  = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='student'")->fetch_assoc()['c'] ?? 0;

// 2. FIXED: Count active sit-ins using correct schema casing and table
$activeSitins   = $conn->query("SELECT COUNT(*) AS c FROM sitin_history WHERE status='Active'")->fetch_assoc()['c'] ?? 0;

// 3. FIXED: Count today's total requested sessions using login_time or fallback registration trace
$todaySessions  = $conn->query("SELECT COUNT(*) AS c FROM sitin_history WHERE DATE(login_time) = CURDATE() OR (login_time IS NULL AND DATE(created_at) = CURDATE())")->fetch_assoc()['c'] ?? 0;

// 4. FIXED: Count pending requests using matching structural casing from sitin_history
$pendingRes     = $conn->query("SELECT COUNT(*) AS c FROM sitin_history WHERE status='Pending'")->fetch_assoc()['c'] ?? 0;

// BYPASS: Set to 0 since the 'testimonials' table does not exist in your database yet
$pendingTest    = 0; 

// Safely fetch announcements
$announcements  = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5");

include 'admin_header.php';
?>

<div class="admin-container" style="max-width: 1600px; margin: 0 auto; padding: 20px;">
    <h2 class="section-title" style="font-weight: 700; margin-bottom: 25px;"><i class="fas fa-chart-line"></i> System Overview</h2>

    <section class="stats-overview" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom:25px;">
        <div class="stat-box blue" style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid #003366; display: flex; align-items: center; gap: 15px;">
            <i class="fas fa-users" style="font-size: 2.2rem; color: #003366;"></i>
            <div class="stat-text"><h3 style="margin: 0; font-size: 1.8rem; color: #333; font-weight:700;"><?php echo $totalStudents; ?></h3><p style="margin: 2px 0 0 0; color: #777; font-size: 0.88rem;">Registered Students</p></div>
        </div>
        <div class="stat-box green" style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid #27ae60; display: flex; align-items: center; gap: 15px;">
            <i class="fas fa-desktop" style="font-size: 2.2rem; color: #27ae60;"></i>
            <div class="stat-text"><h3 style="margin: 0; font-size: 1.8rem; color: #333; font-weight:700;"><?php echo $activeSitins; ?></h3><p style="margin: 2px 0 0 0; color: #777; font-size: 0.88rem;">Active Sit-ins</p></div>
        </div>
        <div class="stat-box gold" style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid #FFD700; display: flex; align-items: center; gap: 15px;">
            <i class="fas fa-calendar-day" style="font-size: 2.2rem; color: #FFD700;"></i>
            <div class="stat-text"><h3 style="margin: 0; font-size: 1.8rem; color: #333; font-weight:700;"><?php echo $todaySessions; ?></h3><p style="margin: 2px 0 0 0; color: #777; font-size: 0.88rem;">Today's Sessions</p></div>
        </div>
        <div class="stat-box" style="background:white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left:5px solid #e74c3c; display: flex; align-items: center; gap: 15px;">
            <i class="fas fa-clock" style="font-size:2.2rem; color:#e74c3c;"></i>
            <div class="stat-text"><h3 style="margin: 0; font-size: 1.8rem; color:#e74c3c; font-weight:700;"><?php echo $pendingRes; ?></h3><p style="margin: 2px 0 0 0; color:#777; font-size: 0.88rem;">Pending Reservations</p></div>
        </div>
    </section>

    <div style="display:grid; grid-template-columns: 1fr 1.5fr 1fr; gap:20px; align-items:start;">

        <div class="admin-card" id="announce" style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <h3 style="margin: 0 0 15px 0; font-size: 1.1rem; font-weight:700; color:#333;"><i class="fas fa-bullhorn" style="color:#003366; margin-right: 5px;"></i> Create Announcement</h3>
            <form action="post_announcement.php" method="POST" class="announcement-form">
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#555; margin-bottom:5px;">Title</label>
                <input type="text" name="title" placeholder="e.g. Lab Maintenance" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; margin-bottom:15px; box-sizing:border-box;">
                
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#555; margin-bottom:5px;">Category</label>
                <select name="category" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; background:white; margin-bottom:15px; box-sizing:border-box;">
                    <option value="System Update">System Update</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Event">Event</option>
                    <option value="Reminder">Reminder</option>
                </select>
                
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#555; margin-bottom:5px;">Details</label>
                <textarea name="content" rows="4" placeholder="Enter message..." required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; resize:vertical; margin-bottom:15px; box-sizing:border-box; font-family: inherit;"></textarea>
                
                <button type="submit" class="btn-post" style="width:100%; background:#003366; color:white; border:none; padding:12px; border-radius:6px; font-weight:bold; cursor:pointer;"><i class="fas fa-paper-plane"></i> Publish Now</button>
            </form>
        </div>

        <div class="admin-card" style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <h3 style="margin: 0 0 15px 0; font-size: 1.1rem; font-weight:700; color:#333;"><i class="fas fa-history" style="color:#003366; margin-right: 5px;"></i> Recent Announcements</h3>
            <div style="overflow-x: auto;">
                <table class="admin-table" style="width:100%; border-collapse:collapse; font-size:0.88rem; text-align:left;">
                    <thead>
                        <tr style="border-bottom: 2px solid #eaeaea; background:#f8f9fa;">
                            <th style="padding:10px;">Date</th>
                            <th style="padding:10px;">Title</th>
                            <th style="padding:10px;">Category</th>
                            <th style="padding:10px; text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($announcements && $announcements->num_rows > 0):
                            while ($row=$announcements->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:12px 10px; font-size:0.82rem; color:#888;"><?php echo date('M d', strtotime($row['created_at'])); ?></td>
                            <td style="padding:12px 10px;"><strong style="font-size:0.88rem; color:#333;"><?php echo htmlspecialchars($row['title']); ?></strong></td>
                            <td style="padding:12px 10px;"><span class="tag info" style="background:#e1f5fe; color:#0288d1; padding:3px 8px; border-radius:12px; font-size:0.75rem; font-weight:600;"><?php echo htmlspecialchars($row['category']); ?></span></td>
                            <td style="padding:12px 10px; text-align:center;">
                                <a href="delete_anno.php?id=<?php echo $row['id']; ?>" class="btn-delete"
                                   onclick="return confirm('Delete this announcement?')" style="color:#e74c3c; font-size:1rem;">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4" style="text-align:center; padding:30px; color:#aaa; background:#fafafa;">No announcements posted yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-card" style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <h3 style="margin: 0 0 15px 0; font-size: 1.1rem; font-weight:700; color:#333;"><i class="fas fa-chart-pie" style="color:#003366; margin-right: 5px;"></i> Log Distribution</h3>
            <div style="position:relative; height:220px; margin-top:15px;">
                <canvas id="sitinChart"></canvas>
            </div>
            <?php if ($pendingTest > 0): ?>
            <div style="margin-top:15px; background:#fff9e6; border:1px solid #FFD700; border-radius:8px; padding:10px; text-align:center; font-size:0.85rem; color:#856404;">
                <i class="fas fa-star"></i> <strong><?php echo $pendingTest; ?></strong> testimonial(s) pending approval.
                <br><a href="admin_testimonials.php" style="color:#003366; font-weight:600;">Review now →</a>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('sitinChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['Registered Students', 'Active Sit-ins', "Today's Sessions", 'Pending Res.'],
        datasets: [{
            data: [<?php echo $totalStudents; ?>, <?php echo $activeSitins; ?>, <?php echo $todaySessions; ?>, <?php echo $pendingRes; ?>],
            backgroundColor: ['#003366', '#27ae60', '#FFD700', '#e74c3c'],
            borderWidth: 2, 
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true, 
        maintainAspectRatio: false, 
        cutout: '65%',
        plugins: { 
            legend: { 
                position: 'bottom', 
                labels: { 
                    boxWidth: 10, 
                    font: { size: 11, weight: '600' },
                    color: '#444'
                } 
            } 
        }
    }
});
</script>
</body>
</html>