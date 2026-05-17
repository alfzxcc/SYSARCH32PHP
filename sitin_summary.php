<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.php");
    exit();
}
include 'header.php'; 
require_once 'db_connect.php'; 

// Fix mapping context: your system maps active logins via the string session field: 'id_number'
$uid = $_SESSION['id_number'] ?? $_SESSION['user_id'];
$userData = $conn->query("SELECT * FROM users WHERE id_number = '$uid'")->fetch_assoc();

// ─── Stats Queries ─────────────────────────────────────────────────────────────
$hoursResult = $conn->query("
    SELECT 
        SUM(TIMESTAMPDIFF(MINUTE, login_time, logout_time)) AS total_minutes,
        COUNT(*) AS total_sessions,
        MAX(TIMESTAMPDIFF(MINUTE, login_time, logout_time)) AS longest_minutes
    FROM sitin_history 
    WHERE id_number = '$uid' AND status = 'Completed' AND logout_time IS NOT NULL
");
$stats = $hoursResult ? $hoursResult->fetch_assoc() : [];

$totalMinutes  = $stats['total_minutes']  ?? 0;
$totalSessions = $stats['total_sessions'] ?? 0;
$longestMins   = $stats['longest_minutes'] ?? 0;

$totalHours   = floor($totalMinutes / 60);
$remainMins   = $totalMinutes % 60;
$avgMins      = $totalSessions > 0 ? round($totalMinutes / $totalSessions) : 0;
$avgHours     = floor($avgMins / 60);
$avgRemain    = $avgMins % 60;
$longHours    = floor($longestMins / 60);
$longRemain   = $longestMins % 60;

// ─── Sessions Table Query ──────────────────────────────────────────────────────
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$totalRows = 0;
$countResult = $conn->query("SELECT COUNT(*) AS cnt FROM sitin_history WHERE id_number = '$uid'");
if ($countResult) {
    $totalRows = $countResult->fetch_assoc()['cnt'] ?? 0;
}

$sessions = $conn->query("
    SELECT 
        history_id AS id,
        DATE(login_time) AS session_date,
        login_time AS time_in,
        logout_time AS time_out,
        TIMESTAMPDIFF(MINUTE, login_time, logout_time) AS duration_minutes,
        lab_name AS lab,
        NULL AS pc_number, 
        purpose,
        status
    FROM sitin_history
    WHERE id_number = '$uid'
    ORDER BY login_time DESC
    LIMIT $perPage OFFSET $offset
");

$totalPages = ceil($totalRows / $perPage);

function formatDuration($mins) {
    if ($mins === null || $mins < 0) return '—';
    $h = floor($mins / 60); $m = $mins % 60;
    return ($h > 0 ? "{$h}h " : "") . "{$m}m";
}
?>

<div style="max-width: 1400px; padding: 0 20px; box-sizing: border-box;">
    
    <div style="background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 30px; display: flex; gap: 30px; min-height: 600px; flex-wrap: wrap;">
        
        <aside style="flex: 1; min-width: 280px; display: flex; flex-direction: column; gap: 20px;">
            <a href="student_sitin.php" style="display: block; text-align: center; background: #003366; color: white; padding: 14px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 0.9rem; letter-spacing: 0.5px; transition: background 0.2s; box-shadow: 0 2px 5px rgba(0,51,102,0.2);">
                <i class="fas fa-plus-circle" style="margin-right:10px;"></i> REQUEST NEW SIT-IN
            </a>
            
            <div style="background: #f8fafc; border-radius: 10px; padding: 24px; border: 1px solid #e2e8f0;">
                <div style="text-align: center; margin-bottom: 25px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px;">
                    <div style="margin-bottom: 12px;">
                        <img src="<?php echo !empty($userData['profile_pic']) ? htmlspecialchars($userData['profile_pic']) : 'default_avatar.png'; ?>" 
                             style="width:90px; height:90px; border-radius:50%; object-fit:cover; border:3px solid #003366; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    </div>
                    <h3 style="color: #003366; margin: 0; font-size: 1.15rem; font-weight: 700;">Student Profile</h3>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display:block; font-size:0.72rem; color:#64748b; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;"><i class="fas fa-id-card" style="width: 18px;"></i> ID Number</label>
                    <span style="font-weight:600; color:#1e293b; font-size: 0.95rem;"><?php echo htmlspecialchars($userData['id_number'] ?? 'N/A'); ?></span>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display:block; font-size:0.72rem; color:#64748b; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;"><i class="fas fa-user" style="width: 18px;"></i> Full Name</label>
                    <span style="font-weight:600; color:#1e293b; font-size: 0.95rem;"><?php
                        $fn = $userData['firstname'] ?? '';
                        $mn = !empty($userData['midname']) ? ' ' . $userData['midname'][0] . '. ' : ' ';
                        $ln = $userData['lastname'] ?? '';
                        echo htmlspecialchars($fn . $mn . $ln);
                    ?></span>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-size:0.72rem; color:#64748b; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;"><i class="fas fa-graduation-cap" style="width: 18px;"></i> Course</label>
                    <span style="font-weight:600; color:#1e293b; font-size: 0.95rem;"><?php echo htmlspecialchars($userData['course'] ?? 'N/A'); ?></span>
                </div>
                
                <div style="background: #f0faf4; padding: 12px 15px; border-radius: 8px; border-left: 4px solid #27ae60; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    <label style="display:block; font-size:0.72rem; color:#27ae60; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;"><i class="fas fa-hourglass-half" style="width: 18px;"></i> Remaining Sessions</label>
                    <span style="font-size: 1.5rem; font-weight: 800; color: #27ae60; display: block; margin-top: 2px;"><?php echo htmlspecialchars($userData['remaining_sessions'] ?? '0'); ?></span>
                </div>
            </div>
        </aside>

        <section style="flex: 3; min-width: 650px; display: flex; flex-direction: column; gap: 25px;">
            
            <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:15px;">
                <div style="background:#f8fafc; border-radius:10px; padding:18px 10px; border:1px solid #e2e8f0; border-top:4px solid #003366; text-align:center;">
                    <i class="fas fa-clock" style="font-size:1.4rem; color:#003366; margin-bottom:6px;"></i>
                    <div style="font-size:1.3rem; font-weight:800; color:#003366;">
                        <?php echo $totalHours; ?><span style="font-size:0.85rem;">h</span> <?php echo $remainMins; ?><span style="font-size:0.85rem;">m</span>
                    </div>
                    <div style="font-size:0.7rem; color:#64748b; font-weight:700; text-transform:uppercase; margin-top:4px; letter-spacing:0.3px;">Total Hours</div>
                </div>

                <div style="background:#f8fafc; border-radius:10px; padding:18px 10px; border:1px solid #e2e8f0; border-top:4px solid #27ae60; text-align:center;">
                    <i class="fas fa-calendar-check" style="font-size:1.4rem; color:#27ae60; margin-bottom:6px;"></i>
                    <div style="font-size:1.3rem; font-weight:800; color:#27ae60;"><?php echo $totalSessions; ?></div>
                    <div style="font-size:0.7rem; color:#64748b; font-weight:700; text-transform:uppercase; margin-top:4px; letter-spacing:0.3px;">Sessions</div>
                </div>

                <div style="background:#f8fafc; border-radius:10px; padding:18px 10px; border:1px solid #e2e8f0; border-top:4px solid #FFD700; text-align:center;">
                    <i class="fas fa-chart-bar" style="font-size:1.4rem; color:#e6b800; margin-bottom:6px;"></i>
                    <div style="font-size:1.3rem; font-weight:800; color:#e6b800;">
                        <?php echo $avgHours > 0 ? $avgHours . '<span style="font-size:0.75rem;">h</span> ' : ''; ?><?php echo $avgRemain; ?><span style="font-size:0.85rem;">m</span>
                    </div>
                    <div style="font-size:0.7rem; color:#64748b; font-weight:700; text-transform:uppercase; margin-top:4px; letter-spacing:0.3px;">Avg. Duration</div>
                </div>

                <div style="background:#f8fafc; border-radius:10px; padding:18px 10px; border:1px solid #e2e8f0; border-top:4px solid #e74c3c; text-align:center;">
                    <i class="fas fa-trophy" style="font-size:1.4rem; color:#e74c3c; margin-bottom:6px;"></i>
                    <div style="font-size:1.3rem; font-weight:800; color:#e74c3c;">
                        <?php echo $longHours > 0 ? $longHours . '<span style="font-size:0.75rem;">h</span> ' : ''; ?><?php echo $longestMins > 0 ? $longRemain . '<span style="font-size:0.85rem;">m</span>' : '—'; ?>
                    </div>
                    <div style="font-size:0.7rem; color:#64748b; font-weight:700; text-transform:uppercase; margin-top:4px; letter-spacing:0.3px;">Longest Stay</div>
                </div>
            </div>

            <div style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                <div style="background: #f8fafc; padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="margin: 0; font-size: 1.05rem; color: #003366; font-weight: 700;"><i class="fas fa-table" style="margin-right: 6px;"></i> Sit-in Summary Logs</h2>
                    <span style="font-size: 0.8rem; background: #e0f2fe; color: #0369a1; padding: 3px 10px; border-radius: 12px; font-weight: 600;"><?php echo $totalRows; ?> Entries Total</span>
                </div>

                <div style="overflow-x: auto; background: white;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 650px;">
                        <thead>
                            <tr style="background: #003366; color: white; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.3px;">
                                <th style="padding: 12px 15px; width: 40px;">#</th>
                                <th style="padding: 12px 15px;">Date</th>
                                <th style="padding: 12px 15px;">Time In</th>
                                <th style="padding: 12px 15px;">Time Out</th>
                                <th style="padding: 12px 15px;">Duration</th>
                                <th style="padding: 12px 15px;">Lab</th>
                                <th style="padding: 12px 15px;">PC No.</th>
                                <th style="padding: 12px 15px;">Status</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.88rem; color: #334155;">
                            <?php
                            $statusColors = ['completed'=>'#27ae60','active'=>'#3498db','Completed'=>'#27ae60','Approved'=>'#27ae60','Active'=>'#3498db','Pending'=>'#ffc107','Rejected'=>'#e74c3c'];
                            $rowNum = $offset + 1;
                            if ($sessions && $sessions->num_rows > 0):
                                while ($s = $sessions->fetch_assoc()):
                                    $sc = $statusColors[$s['status']] ?? '#aaa';
                                    $dur = formatDuration($s['duration_minutes']);
                            ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                <td style="padding: 12px 15px; color: #94a3b8; font-size: 0.8rem;"><?php echo $rowNum++; ?></td>
                                <td style="padding: 12px 15px; font-weight: 600; color: #0f172a;">
                                    <?php echo $s['session_date'] ? date('M d, Y', strtotime($s['session_date'])) : '—'; ?>
                                </td>
                                <td style="padding: 12px 15px;"><?php echo $s['time_in'] ? date('g:i A', strtotime($s['time_in'])) : '—'; ?></td>
                                <td style="padding: 12px 15px;">
                                    <?php echo $s['time_out'] ? date('g:i A', strtotime($s['time_out'])) : '<span style="color:#ffc107; font-weight:600;">Ongoing</span>'; ?>
                                </td>
                                <td style="padding: 12px 15px;">
                                    <span style="background:#f0f4ff; color:#003366; padding:3px 8px; border-radius:6px; font-size:0.78rem; font-weight:600;">
                                        <?php echo $dur; ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 15px; font-weight: 500;"><?php echo htmlspecialchars($s['lab'] ?? '—'); ?></td>
                                <td style="padding: 12px 15px; font-weight: 600; color: #475569;">
                                    <?php echo !empty($s['pc_number']) ? htmlspecialchars($s['pc_number']) : '<span style="color:#cbd5e1;">—</span>'; ?>
                                </td>
                                <td style="padding: 12px 15px;">
                                    <span style="color:<?php echo $sc; ?>; font-weight:bold; font-size:0.82rem;">
                                        ● <?php echo htmlspecialchars($s['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding:50px; color:#94a3b8;">
                                    <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:10px; color:#cbd5e1;"></i>
                                    No valid logs captured for this student profile.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 20px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div style="display:flex; gap:6px;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" style="padding:5px 11px; border:1px solid #cbd5e1; border-radius:6px; text-decoration:none; color:#475569; background: white; font-size:0.8rem;">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        <?php for ($p = max(1, $page-2); $p <= min($totalPages, $page+2); $p++): ?>
                            <a href="?page=<?php echo $p; ?>" style="padding:5px 11px; border:1px solid <?php echo $p==$page?'#003366':'#cbd5e1'; ?>; border-radius:6px; text-decoration:none; color:<?php echo $p==$page?'white':'#475569'; ?>; background:<?php echo $p==$page?'#003366':'white'; ?>; font-size:0.8rem; font-weight: 600;">
                                <?php echo $p; ?>
                            </a>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" style="padding:5px 11px; border:1px solid #cbd5e1; border-radius:6px; text-decoration:none; color:#475569; background: white; font-size:0.8rem;">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <span style="color:#94a3b8; font-size:0.78rem;">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>