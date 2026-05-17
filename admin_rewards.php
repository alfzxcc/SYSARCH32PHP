<?php
include 'admin_check.php';
require_once 'db_connect.php';
include 'admin_header.php';
$conn->set_charset("utf8mb4");

// Create tables if not exist
$conn->query("CREATE TABLE IF NOT EXISTS student_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_number VARCHAR(30) NOT NULL UNIQUE,
    points INT DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB");

$conn->query("CREATE TABLE IF NOT EXISTS points_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_number VARCHAR(30) NOT NULL,
    points_change INT NOT NULL,
    reason VARCHAR(255),
    awarded_by VARCHAR(30),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

$conn->query("CREATE TABLE IF NOT EXISTS rewards_catalog (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reward_name VARCHAR(100) NOT NULL,
    description TEXT,
    points_required INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

// Seed rewards catalog
$conn->query("INSERT IGNORE INTO rewards_catalog (id, reward_name, description, points_required) VALUES
(1,'Extra Sit-in Session','Earn 1 bonus sit-in session',50),
(2,'Priority Reservation','Skip the queue for 1 reservation',80),
(3,'Extended Session','Get 30 extra minutes in a session',30),
(4,'Lab Access Pass','Free lab access for 1 day',120)");

$msg = $err = '';
$adminId = $_SESSION['user_id'];

// Award / deduct points
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['action'])) {
    $idNum  = $conn->real_escape_string(trim($_POST['id_number']));
    $pts    = (int)$_POST['points'];
    $reason = $conn->real_escape_string(trim($_POST['reason']));

    $exists = $conn->query("SELECT id_number FROM users WHERE id_number='$idNum' AND role='student'")->fetch_assoc();
    if (!$exists) {
        $err = "Student ID '$idNum' not found.";
    } else {
        if ($_POST['action']=='deduct') $pts = -abs($pts);
        // Upsert points
        $conn->query("INSERT INTO student_points (id_number, points) VALUES ('$idNum', $pts)
                      ON DUPLICATE KEY UPDATE points = GREATEST(0, points + $pts)");
        $conn->query("INSERT INTO points_history (id_number, points_change, reason, awarded_by, created_at)
                      VALUES ('$idNum', $pts, '$reason', '$adminId', NOW())");
        $msg = ($pts>=0?"Awarded +$pts":"Deducted ".abs($pts))." points to student $idNum.";
    }
}

// Add reward to catalog
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['add_reward'])) {
    $rname = $conn->real_escape_string(trim($_POST['reward_name']));
    $rdesc = $conn->real_escape_string(trim($_POST['reward_desc']));
    $rpts  = (int)$_POST['reward_points'];
    $conn->query("INSERT INTO rewards_catalog (reward_name, description, points_required) VALUES ('$rname','$rdesc',$rpts)");
    $msg = "Reward '$rname' added!";
}

// Delete reward
if (isset($_GET['del_reward']) && is_numeric($_GET['del_reward'])) {
    $conn->query("DELETE FROM rewards_catalog WHERE id=".(int)$_GET['del_reward']);
    header("Location: admin_rewards.php?msg=deleted"); exit();
}

// Toggle reward active
if (isset($_GET['toggle_reward']) && is_numeric($_GET['toggle_reward'])) {
    $rid = (int)$_GET['toggle_reward'];
    $cur = $conn->query("SELECT is_active FROM rewards_catalog WHERE id=$rid")->fetch_assoc();
    $conn->query("UPDATE rewards_catalog SET is_active=".($cur['is_active']?0:1)." WHERE id=$rid");
    header("Location: admin_rewards.php"); exit();
}

// Leaderboard
$leaderboard = $conn->query("
    SELECT sp.id_number, sp.points,
           CONCAT(u.firstname,' ',u.lastname) AS full_name, u.course
    FROM student_points sp
    JOIN users u ON u.id_number = sp.id_number
    WHERE sp.points > 0
    ORDER BY sp.points DESC LIMIT 20
");

// Recent history
$history = $conn->query("
    SELECT ph.*, CONCAT(u.firstname,' ',u.lastname) AS student_name
    FROM points_history ph
    LEFT JOIN users u ON u.id_number = ph.id_number
    ORDER BY ph.created_at DESC LIMIT 30
");

// Catalog
$catalog = $conn->query("SELECT * FROM rewards_catalog ORDER BY points_required ASC");
?>

<div class="admin-container" style="max-width: 1400px; margin: 0 auto; padding: 20px;">
    <h2 class="section-title"><i class="fas fa-star"></i> Rewards & Points System</h2>

    <?php if ($msg): ?>
    <div style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:4px solid #28a745;">
        <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
    </div>
    <?php endif; ?>
    <?php if ($err): ?>
    <div style="background:#f8d7da;color:#721c24;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:4px solid #e74c3c;">
        <i class="fas fa-exclamation-circle"></i> <?php echo $err; ?>
    </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1fr 1.2fr; gap:20px; margin-bottom:25px; align-items: start;">

        <div class="admin-card" style="height: 100%; box-sizing: border-box;">
            <h3><i class="fas fa-gift"></i> Award / Deduct Points</h3>
            <form method="POST" style="margin-top:15px;">
                <div style="margin-bottom:13px;">
                    <label style="font-size:0.85rem;font-weight:600;display:block;margin-bottom:4px;">Student ID Number</label>
                    <input type="text" name="id_number" placeholder="e.g. 2023-0001" required
                           style="width:100%;padding:9px 12px;border:1px solid #ddd;border-radius:7px;box-sizing:border-box;">
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:13px; align-items: end;">
                    <div>
                        <label style="font-size:0.85rem; font-weight:600; display:block; margin-bottom:5px;">Points</label>
                        <input type="number" name="points" min="1" max="500" value="10" required
                            style="width:100%; height:38px; padding:0 12px; border:1px solid #ddd; border-radius:7px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:0.85rem; font-weight:600; display:block; margin-bottom:10px;">Action</label>
                        <select name="action" style="width:100%; height:38px; padding:0 9px; border:1px solid #ddd; border-radius:7px; box-sizing:border-box; background-color: #fff; margin-bottom: 10px;">
                            <option value="award">Award Points</option>
                            <option value="deduct">Deduct Points</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="font-size:0.85rem;font-weight:600;display:block;margin-bottom:4px;">Reason</label>
                    <select name="reason" style="width:100%;padding:9px;border:1px solid #ddd;border-radius:7px;">
                        <option value="Perfect Attendance">Perfect Attendance</option>
                        <option value="Completed All Sessions">Completed All Sessions</option>
                        <option value="Good Behavior">Good Behavior</option>
                        <option value="Lab Contribution">Lab Contribution</option>
                        <option value="Academic Achievement">Academic Achievement</option>
                        <option value="Admin Adjustment">Admin Adjustment</option>
                        <option value="Violation Penalty">Violation Penalty</option>
                    </select>
                </div>
                <button type="submit" class="btn-post" style="width:100%;">
                    <i class="fas fa-paper-plane"></i> Apply Points
                </button>
            </form>
        </div>

        <div class="admin-card">
            <h3><i class="fas fa-trophy"></i> Rewards Catalog Control</h3>
            
            <form method="POST" style="margin-top:15px;padding:15px;background:#f8f9fa;border-radius:8px;margin-bottom:15px; display: grid; grid-template-columns: 1.5fr 1.5fr 1fr; gap: 10px; align-items: end;">
                <input type="hidden" name="add_reward" value="1">
                <div>
                    <label style="font-size:0.82rem;font-weight:600;display:block;margin-bottom:4px;">Reward Name</label>
                    <input type="text" name="reward_name" placeholder="e.g. Free Lab Pass" required
                           style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:7px;box-sizing:border-box;font-size:0.85rem;">
                </div>
                <div>
                    <label style="font-size:0.82rem;font-weight:600;display:block;margin-bottom:4px;">Description</label>
                    <input type="text" name="reward_desc" placeholder="Brief details"
                           style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:7px;box-sizing:border-box;font-size:0.85rem;">
                </div>
                <div>
                    <label style="font-size:0.82rem;font-weight:600;display:block;margin-bottom:4px;">Points</label>
                    <input type="number" name="reward_points" min="1" value="50" required
                           style="width:100%;padding:3px 12px;border:1px solid #ddd;border-radius:7px;box-sizing:border-box;font-size:0.85rem;">
                </div>
                <button type="submit" class="btn-post" style="grid-column: span 3; width:100%; background:#27ae60; margin-top: 5px; padding: 10px;">
                    <i class="fas fa-plus"></i> Add New Reward to Catalog
                </button>
            </form>

            <div style="max-height: 205px; overflow-y: auto; padding-right: 3px;">
                <?php if ($catalog && $catalog->num_rows > 0): while ($rw = $catalog->fetch_assoc()): ?>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:10px;background:<?php echo $rw['is_active']?'#f0fdf4':'#fafafa'; ?>;border-radius:8px;margin-bottom:8px;border:1px solid <?php echo $rw['is_active']?'#a5d6a7':'#e5e7eb'; ?>;">
                    <div>
                        <div style="font-weight:600;font-size:0.88rem;"><?php echo htmlspecialchars($rw['reward_name']); ?></div>
                        <div style="font-size:0.75rem;color:#888;"><?php echo htmlspecialchars($rw['description']); ?></div>
                        <div style="font-size:0.78rem;color:#FFD700;font-weight:700;margin-top:3px;"><i class="fas fa-star"></i> <?php echo $rw['points_required']; ?> pts</div>
                    </div>
                    <div style="display:flex;gap:5px;flex-shrink:0;">
                        <a href="?toggle_reward=<?php echo $rw['id']; ?>" title="<?php echo $rw['is_active']?'Disable':'Enable'; ?>"
                           style="background:<?php echo $rw['is_active']?'#ffc107':'#27ae60'; ?>;color:<?php echo $rw['is_active']?'#333':'white'; ?>;padding:4px 9px;border-radius:5px;text-decoration:none;font-size:0.75rem;">
                            <?php echo $rw['is_active']?'Off':'On'; ?>
                        </a>
                        <a href="?del_reward=<?php echo $rw['id']; ?>" onclick="return confirm('Delete reward?')"
                           style="background:#e74c3c;color:white;padding:4px 9px;border-radius:5px;text-decoration:none;font-size:0.75rem;">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                <?php endwhile; endif; ?>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 1.1fr 1fr; gap:20px; align-items: start;">

        <div class="admin-card">
            <h3><i class="fas fa-crown" style="color:#FFD700;"></i> Points Leaderboard</h3>
            <div style="overflow-x:auto; margin-top:15px; max-height: 400px; overflow-y: auto;">
                <table class="admin-table">
                    <thead style="position: sticky; top: 0; background: #fff; z-index: 1;">
                        <tr><th>Rank</th><th>ID Number</th><th>Student Name</th><th>Course</th><th>Points</th><th>Badge</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($leaderboard && $leaderboard->num_rows > 0):
                            $rank=1; while ($lb = $leaderboard->fetch_assoc()):
                                $badge = $rank==1?'🥇':($rank==2?'🥈':($rank==3?'🥉':'#'.$rank));
                                $pts = $lb['points'];
                                $barW = min(100, round(($pts/500)*100));
                        ?>
                        <tr>
                            <td style="font-size:1.1rem;"><?php echo $badge; ?></td>
                            <td><?php echo htmlspecialchars($lb['id_number']); ?></td>
                            <td><strong><?php echo htmlspecialchars($lb['full_name']); ?></strong></td>
                            <td><span class="tag info"><?php echo htmlspecialchars($lb['course']); ?></span></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="background:#eee;border-radius:10px;height:8px;width:70px;overflow:hidden;flex-shrink:0;">
                                        <div style="background:<?php echo $rank<=3?'#FFD700':'#003366'; ?>;width:<?php echo $barW; ?>%;height:100%;border-radius:10px;"></div>
                                    </div>
                                    <strong style="color:#003366;"><?php echo $pts; ?></strong>
                                </div>
                            </td>
                            <td><?php
                                if ($pts>=200) echo '<span style="background:#FFD700;color:#003366;padding:3px 9px;border-radius:10px;font-size:0.75rem;font-weight:700;">Gold</span>';
                                elseif ($pts>=100) echo '<span style="background:#C0C0C0;color:#333;padding:3px 9px;border-radius:10px;font-size:0.75rem;font-weight:700;">Silver</span>';
                                elseif ($pts>=50) echo '<span style="background:#cd7f32;color:white;padding:3px 9px;border-radius:10px;font-size:0.75rem;font-weight:700;">Bronze</span>';
                                else echo '<span style="color:#aaa;font-size:0.8rem;">—</span>';
                            ?></td>
                        </tr>
                        <?php $rank++; endwhile;
                        else: ?>
                        <tr><td colspan="6" style="text-align:center;padding:30px;color:#aaa;">No points awarded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-card">
            <h3><i class="fas fa-history"></i> Recent Points History</h3>
            <div style="overflow-x:auto; margin-top:15px; max-height: 400px; overflow-y: auto;">
                <table class="admin-table">
                    <thead style="position: sticky; top: 0; background: #fff; z-index: 1;">
                        <tr><th>Date</th><th>Student</th><th>ID</th><th>Change</th><th>Reason</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($history && $history->num_rows > 0):
                            while ($h = $history->fetch_assoc()):
                                $positive = $h['points_change'] >= 0;
                        ?>
                        <tr>
                            <td style="font-size:0.82rem;color:#888;"><?php echo date('M d, h:i A', strtotime($h['created_at'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($h['student_name']??'—'); ?></strong></td>
                            <td style="font-size:0.85rem;"><?php echo htmlspecialchars($h['id_number']); ?></td>
                            <td>
                                <span style="color:<?php echo $positive?'#27ae60':'#e74c3c'; ?>;font-weight:700;">
                                    <?php echo $positive?'+':''.$h['points_change']; ?> pts
                                </span>
                            </td>
                            <td style="font-size:0.82rem;color:#555;"><?php echo htmlspecialchars($h['reason']??'—'); ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" style="text-align:center;padding:20px;color:#aaa;">No history yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
</body>
</html>