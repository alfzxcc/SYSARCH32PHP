<?php
include 'admin_check.php';
require_once 'db_connect.php';
include 'admin_header.php';
$conn->set_charset("utf8mb4");

$student = null; $history = []; $searchDone = false; $errMsg = '';
$points = 0;

if (!empty($_GET['search_id'])) {
    $searchDone = true;
    $sid = $conn->real_escape_string(trim($_GET['search_id']));
    $userRes = $conn->query("SELECT * FROM users WHERE id_number='$sid' AND role='student' LIMIT 1");
    if ($userRes && $userRes->num_rows > 0) {
        $student = $userRes->fetch_assoc();
        // FIXED: Querying from sitin_history table ordered by login_time
        $history = $conn->query("
            SELECT * FROM sitin_history WHERE id_number='$sid' ORDER BY login_time DESC LIMIT 15
        ");
        $ptRow = $conn->query("SELECT points FROM student_points WHERE id_number='$sid'")->fetch_assoc();
        $points = $ptRow['points'] ?? 0;
    } else {
        $errMsg = "No student found with ID: ".htmlspecialchars($sid);
    }
}
?>
<div class="admin-container" style="max-width: 1600px; margin: 0 auto; padding: 20px;">
    <h2 class="section-title" style="font-weight: 700;  margin-bottom: 25px;"><i class="fas fa-search"></i> Student Search</h2>

    <div class="admin-card" style="max-width:700px; margin:0 auto 30px; text-align:center; background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <p style="color:#666; margin-bottom:15px;">Enter a Student ID Number to view their profile and sit-in history.</p>
        <form method="GET" style="display:flex; gap:10px; justify-content:center;">
            <input type="text" name="search_id" placeholder="e.g. 2023-0001"
                   value="<?php echo isset($_GET['search_id'])?htmlspecialchars($_GET['search_id']):''; ?>"
                   required style="flex:1; padding:12px; border:2px solid #e2e8f0; border-radius:8px; font-size:0.95rem; box-sizing: border-box;">
            <button type="submit" style="padding:0 30px; background:#003366; color:white; border:none; border-radius:8px; font-weight:bold; cursor:pointer;">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
        <?php if ($errMsg): ?>
        <div style="margin-top:15px; color:#e74c3c; background:#fff5f5; padding:10px; border-radius:5px; border:1px solid #feb2b2;">
            <?php echo $errMsg; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($student): ?>
    <div style="display:grid; grid-template-columns:1fr 2.5fr; gap:25px; align-items: start;">

        <div class="admin-card" style="text-align:center; background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <div style="width:80px; height:80px; background:#003366; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 15px; font-size:2rem; color:white; font-weight:700;">
                <?php echo strtoupper(substr($student['firstname'],0,1)); ?>
            </div>
            <h3 style="margin:0 0 5px; font-weight:700;"><?php echo htmlspecialchars($student['firstname'].' '.$student['lastname']); ?></h3>
            <span class="tag info" style="font-size:0.8rem; background:#e1f5fe; color:#0288d1; padding:4px 10px; border-radius:12px; font-weight:600; display:inline-block;"><?php echo htmlspecialchars($student['course']); ?></span>

            <div style="margin-top:20px; border-top:1px solid #f1f5f9; padding-top:20px; text-align:left;">
                <?php
                $fields = [
                    'ID Number'   => $student['id_number'],
                    'Email'       => $student['email'],
                    'Year Level'  => 'Year '.($student['course_level']??'—'),
                    'Address'     => $student['address']??'—',
                ];
                foreach ($fields as $label => $val): ?>
                <div style="margin-bottom:12px;">
                    <div style="font-size:0.75rem; font-weight:700; color:#003366; text-transform:uppercase; margin-bottom:2px;"><?php echo $label; ?></div>
                    <div style="font-size:0.88rem; color:#555; background:#f8f9fa; padding:7px 10px; border-radius:6px; font-weight:500;"><?php echo htmlspecialchars($val); ?></div>
                </div>
                <?php endforeach; ?>
                
                <div style="background:#f0fdf4; padding:15px; border-radius:8px; border:1px solid #bbf7d0; margin-top:10px; text-align:center;">
                    <div style="font-size:0.75rem; font-weight:700; color:#166534; text-transform:uppercase;">Remaining Sessions</div>
                    <div style="font-size:2rem; font-weight:800; color:#15803d;"><?php echo $student['remaining_sessions']??0; ?></div>
                </div>
                <div style="background:#fff9e6; padding:12px; border-radius:8px; border:1px solid #FFD700; margin-top:10px; text-align:center;">
                    <div style="font-size:0.75rem; font-weight:700; color:#856404; text-transform:uppercase;">Reward Points</div>
                    <div style="font-size:1.6rem; font-weight:800; color:#d4af37;"><i class="fas fa-star"></i> <?php echo $points; ?></div>
                </div>
            </div>
        </div>

        <div class="admin-card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <h3 style="margin:0 0 15px; font-weight:700; color:#333;"><i class="fas fa-history" style="color:#003366; margin-right:5px;"></i> Recent Sit-in Records</h3>
            <div style="overflow-x: auto;">
                <table class="admin-table" style="width:100%; border-collapse:collapse; font-size:0.88rem; text-align:left;">
                    <thead>
                        <tr style="background:#f8f9fa; border-bottom:2px solid #eaeaea; color:#444;">
                            <th style="padding:10px;">Date / Time In</th>
                            <th style="padding:10px;">Laboratory</th>
                            <th style="padding:10px;">Purpose</th>
                            <th style="padding:10px;">Duration</th>
                            <th style="padding:10px; text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($history && $history->num_rows > 0):
                            while ($r = $history->fetch_assoc()):
                                $st = $r['status'];
                                $sc = ['Pending'=>'#d97706', 'Active'=>'#27ae60', 'Completed'=>'#2980b9', 'Rejected'=>'#7f8c8d'];
                                $badge_color = $sc[$st] ?? '#666';

                                $t_in = $r['login_time'] ?? null;
                                $t_out = $r['logout_time'] ?? null;

                                $time_in_display = !empty($t_in) && $t_in !== '0000-00-00 00:00:00' ? date('M d, Y', strtotime($t_in)).'<br><small style="color:#888;">'.date('g:i A', strtotime($t_in)).'</small>' : '—';
                                
                                // Process duration length dynamically
                                $dur = '—';
                                if (!empty($t_in) && !empty($t_out) && $t_out !== '0000-00-00 00:00:00') {
                                    $diff = strtotime($t_out) - strtotime($t_in);
                                    $dur = round($diff / 60) . 'm';
                                } elseif ($st === 'Active') {
                                    $dur = 'Ongoing';
                                }
                        ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:12px 10px; font-weight:500;"><?php echo $time_in_display; ?></td>
                            <td style="padding:12px 10px; font-weight:600; color:#333;"><?php echo htmlspecialchars($r['lab_name'] ?? '—'); ?></td>
                            <td style="padding:12px 10px; font-style:italic; color:#666;"><?php echo htmlspecialchars($r['purpose']); ?></td>
                            <td style="padding:12px 10px; font-weight:500;"><?php echo $dur; ?></td>
                            <td style="padding:12px 10px; text-align: center;">
                                <span style="color: <?php echo $badge_color; ?>; font-weight: bold; font-size: 0.8rem; background: <?php echo $badge_color; ?>10; padding: 3px 8px; border-radius: 12px; border: 1px solid <?php echo $badge_color; ?>20; display: inline-block;">
                                    ● <?php echo $st; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:40px; color:#aaa; background:#fafafa;">
                                <i class="fas fa-folder-open" style="font-size:2rem; display:block; margin-bottom:8px; color:#ccc;"></i>
                                No sit-in history logs found for this student.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
</body>
</html>