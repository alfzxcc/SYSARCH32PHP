<?php
include 'admin_check.php';
require_once 'db_connect.php';
include 'admin_header.php';
$conn->set_charset("utf8mb4");

// Logout (time-out) a student
if (isset($_GET['logout_id']) && is_numeric($_GET['logout_id'])) {
    $rid = (int)$_GET['logout_id'];
    
    // Fetch the student's id_number to properly refund the session limit
    $row = $conn->query("SELECT id_number FROM sitin_history WHERE history_id=$rid")->fetch_assoc();
    if ($row) {
        $id_no = $row['id_number'];
        // Update history row to complete status and log out time
        $conn->query("UPDATE sitin_history SET status='Completed', logout_time=NOW() WHERE history_id=$rid AND status='Active'");
        // Refund their remaining session balance
        $conn->query("UPDATE users SET remaining_sessions=remaining_sessions+1 WHERE id_number='$id_no'");
    }
    header("Location: admin_current_sitin.php?msg=timed_out"); exit();
}

// Logout all active students
if (isset($_GET['logout_all'])) {
    $actives = $conn->query("SELECT history_id, id_number FROM sitin_history WHERE status='Active'");
    if ($actives) {
        while ($a = $actives->fetch_assoc()) {
            $conn->query("UPDATE sitin_history SET status='Completed', logout_time=NOW() WHERE history_id={$a['history_id']}");
            $conn->query("UPDATE users SET remaining_sessions=remaining_sessions+1 WHERE id_number='{$a['id_number']}'");
        }
    }
    header("Location: admin_current_sitin.php?msg=all_out"); exit();
}

// Fetch active entries from sitin_history joined cleanly with users table
$active = $conn->query("
    SELECT sh.*, CONCAT(u.firstname,' ',u.lastname) AS full_name, u.course
    FROM sitin_history sh
    JOIN users u ON u.id_number = sh.id_number
    WHERE sh.status = 'Active'
    ORDER BY sh.login_time ASC
");
$count = $active ? $active->num_rows : 0;

// Lab room tracking breakdown using sitin_history columns
$labCount = $conn->query("SELECT lab_name, COUNT(*) AS cnt FROM sitin_history WHERE status='Active' GROUP BY lab_name");
?>

<div class="admin-container" style="max-width: 1600px; margin: 0 auto; padding: 20px;">
    <h2 class="section-title" style="margin-bottom: 20px; font-weight:700; "><i class="fas fa-signal"></i> Current Sit-in Monitor</h2>

    <?php if (isset($_GET['msg'])): ?>
    <div style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:4px solid #28a745; font-weight: 500;">
        <i class="fas fa-check-circle"></i>
        <?php echo $_GET['msg']=='all_out' ? 'All students have been logged out.' : 'Student timed out successfully.'; ?>
    </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 320px 1fr; gap: 25px; align-items: start;">
        
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <div style="background: #fff; border-radius: 10px; padding: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-left: 5px solid #003366;">
                <h4 style="margin: 0 0 5px 0; color: #777; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">System Attendance</h4>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <i class="fas fa-users" style="font-size: 2rem; color: #003366;"></i>
                    <h2 style="margin: 0; font-size: 2rem; color: #333; font-weight: 700;"><?php echo $count; ?> <span style="font-size: 1rem; color: #888; font-weight: 400;">Active</span></h2>
                </div>
            </div>

            <h3 style="margin: 10px 0 5px 5px; font-size: 0.95rem; color: #555; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">
                <i class="fas fa-door-open"></i> Available Labs
            </h3>

            <?php
            $labs = ['Lab 524' => '#003366', 'Lab 526' => '#9b59b6', 'Lab 542' => '#e67e22', 'Lab 544' => '#27ae60'];
            $labData = [];
            if ($labCount) while ($lc = $labCount->fetch_assoc()) $labData[$lc['lab_name']] = $lc['cnt'];
            
            foreach ($labs as $lab => $color):
                $n = $labData[$lab] ?? 0;
            ?>
            <div style="background: white; border-radius: 10px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.04); border-bottom: 4px solid <?php echo $color; ?>; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-desktop" style="color: <?php echo $color; ?>; font-size: 1.4rem;"></i>
                    <span style="font-weight: 600; color: #444; font-size: 0.95rem Haus;"><?php echo $lab; ?></span>
                </div>
                <div style="background: <?php echo $color; ?>15; color: <?php echo $color; ?>; padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 1.1rem;">
                    <?php echo $n; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="admin-card" style="margin: 0; min-height: 520px; background: white; padding: 24px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 15px;">
                <h3 style="margin: 0; font-size: 1.2rem; font-weight:700; color:#333;"><i class="fas fa-list" style="color:#003366;"></i> Live Sit-in Sessions</h3>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="liveSearch" placeholder="Filter by Student, ID, Room..."
                           style="padding: 9px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.88rem; width: 260px; box-sizing: border-box;">
                    <?php if ($count > 0): ?>
                    <a href="?logout_all=1" onclick="return confirm('Force Log out ALL <?php echo $count; ?> active students?')"
                       style="background: #e74c3c; color: white; padding: 9px 16px; border-radius: 8px; text-decoration: none; font-size: 0.88rem; font-weight: bold; white-space: nowrap; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-sign-out-alt"></i> Log Out All
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <div style="overflow-x: auto; border-radius: 8px; border: 1px solid #eaeaea;">
                <table class="admin-table" id="sitinTable" style="margin: 0; width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #eaeaea; color:#444;">
                            <th style="padding: 12px;">#</th>
                            <th style="padding: 12px;">ID Number</th>
                            <th style="padding: 12px;">Student Name</th>
                            <th style="padding: 12px;">Course</th>
                            <th style="padding: 12px;">Laboratory</th>
                            <th style="padding: 12px;">Purpose</th>
                            <th style="padding: 12px;">Time In</th>
                            <th style="padding: 12px;">Duration</th>
                            <th style="padding: 12px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($active && $active->num_rows > 0):
                            $i = 1;
                            while ($r = $active->fetch_assoc()):
                                // Dynamic ongoing counter configuration based on login_time column
                                $login_timestamp = !empty($r['login_time']) ? strtotime($r['login_time']) : time();
                                $mins = floor((time() - $login_timestamp) / 60);
                                $dur  = ($mins >= 60 ? floor($mins/60).'h ' : '') . ($mins%60) . 'm';
                                $warn = $mins > 120 ? 'background: #fffdf0;' : '';
                        ?>
                        <tr style="<?php echo $warn; ?> border-bottom: 1px solid #eee;">
                            <td style="color: #999; font-size: 0.85rem; padding: 14px 12px;"><?php echo $i++; ?></td>
                            <td style="padding: 14px 12px;"><strong><?php echo htmlspecialchars($r['id_number']); ?></strong></td>
                            <td style="padding: 14px 12px;"><span style="font-weight: 600; color: #003366;"><?php echo htmlspecialchars($r['full_name']); ?></span></td>
                            <td style="padding: 14px 12px;"><span style="font-weight: 500; color: #555;"><?php echo htmlspecialchars($r['course']); ?></span></td>
                            <td style="padding: 14px 12px;"><strong style="color: #333;"><?php echo htmlspecialchars($r['lab_name'] ?? '—'); ?></strong></td>
                            <td style="padding: 14px 12px; font-style: italic; color: #666;"><?php echo htmlspecialchars($r['purpose']); ?></td>
                            <td style="padding: 14px 12px; font-size: 0.85rem; color: #444; font-weight: 500;"><?php echo !empty($r['login_time']) ? date('g:i A', $login_timestamp) : '—'; ?></td>
                            <td style="padding: 14px 12px;">
                                <span style="background: <?php echo $mins > 120 ? '#fdf2d1' : '#eaf8eb'; ?>; color: <?php echo $mins > 120 ? '#b78103' : '#2e7d32'; ?>; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; display: inline-block;">
                                    <?php echo $dur; ?>
                                </span>
                            </td>
                            <td style="text-align: center; padding: 14px 12px;">
                                <a href="?logout_id=<?php echo $r['history_id']; ?>" onclick="return confirm('Log out <?php echo htmlspecialchars($r['full_name']); ?>?')"
                                   style="background: #e74c3c; color: white; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: bold; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-sign-out-alt"></i> Log Out
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 60px; color: #bbb; background:#fafafa;">
                                <i class="fas fa-desktop" style="font-size: 3rem; display: block; margin-bottom: 15px; opacity: 0.25; color:#cc1111;"></i>
                                No active student workstations open right now.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; color: #999; font-size: 0.8rem;">
                <span><i class="fas fa-sync-alt"></i> System checks background connections every 60s.</span>
                <span><span style="color: #b78103; font-weight: bold;">⚠ Highlighted Rows</span> signify continuous usage over 2 hours.</span>
            </div>
        </div>

    </div>
</div>

<script>
document.getElementById('liveSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#sitinTable tbody tr').forEach(row => {
        if(row.cells.length > 1) { 
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        }
    });
});

// Auto refresh components smoothly
setTimeout(() => location.reload(), 60000);
</script>
</body>
</html>