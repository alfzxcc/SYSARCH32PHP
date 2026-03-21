<?php 
include 'admin_check.php'; 
require_once 'db_connect.php'; 
include 'admin_header.php'; 

// 1. Get Top Laboratories by Usage
$labQuery = "SELECT lab_room, COUNT(*) as count FROM sitin_records GROUP BY lab_room ORDER BY count DESC";
$labStats = $conn->query($labQuery);

// 2. Get Top Purposes
$purposeQuery = "SELECT purpose, COUNT(*) as count FROM sitin_records GROUP BY purpose ORDER BY count DESC LIMIT 5";
$purposeStats = $conn->query($purposeQuery);

// 3. Get Today's Total Sit-ins
$today = date('Y-m-d');
$todayQuery = "SELECT COUNT(*) as total FROM sitin_records WHERE DATE(time_in) = '$today'";
$todayCount = $conn->query($todayQuery)->fetch_assoc()['total'];
?>

<div class="admin-container">
    <h2 class="section-title"><i class="fas fa-file-invoice"></i> Analytical Reports</h2>

    <div class="admin-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px;">
        
        <div class="admin-card">
            <h3><i class="fas fa-chart-bar"></i> Lab Popularity</h3>
            <div style="margin-top: 20px;">
                <?php while($row = $labStats->fetch_assoc()): 
                    $percent = ($row['count'] > 0) ? ($row['count'] / 50) * 100 : 0; // Assuming 50 is max capacity for scale ?>
                    <div style="margin-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 5px;">
                            <span><?php echo $row['lab_room']; ?></span>
                            <strong><?php echo $row['count']; ?> entries</strong>
                        </div>
                        <div style="background: #eee; border-radius: 10px; height: 10px; overflow: hidden;">
                            <div style="background: #3498db; width: <?php echo $percent; ?>%; height: 100%;"></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="admin-card">
            <h3><i class="fas fa-bullseye"></i> Top Purposes</h3>
            <ul style="list-style: none; padding: 0; margin-top: 20px;">
                <?php while($row = $purposeStats->fetch_assoc()): ?>
                    <li style="padding: 10px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                        <span><?php echo $row['purpose']; ?></span>
                        <span class="tag info"><?php echo $row['count']; ?> Students</span>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <div class="admin-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3><i class="fas fa-calendar-day"></i> Today's Summary (<?php echo date('M d, Y'); ?>)</h3>
            <h2 style="color: #27ae60; margin: 0;"><?php echo $todayCount; ?> <small style="font-size: 12px; color: #666;">Total Sit-ins</small></h2>
        </div>
        <hr>
        <p style="color: #666; font-size: 14px;">This report summarizes lab activity for the current date. For full history, visit the Records page.</p>
        <button onclick="window.print()" class="btn-post" style="width: 200px; background: #2c3e50;">
            <i class="fas fa-download"></i> Export as PDF/Print
        </button>
    </div>
</div>

</body>
</html>