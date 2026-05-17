<?php 
include 'admin_check.php'; 
require_once 'db_connect.php'; 
include 'admin_header.php'; 

$conn->set_charset("utf8mb4");

// Date range filters
$fromDate = $_GET['from'] ?? date('Y-m-01');
$toDate   = $_GET['to']   ?? date('Y-m-d');
$labFilter = $conn->real_escape_string($_GET['lab'] ?? '');
$format    = $_GET['format'] ?? 'view';

// Base tracking filter using real structural column names (checkin_time, lab_room)
$baseWhere = "WHERE DATE(sr.checkin_time) BETWEEN '$fromDate' AND '$toDate'" 
           . ($labFilter ? " AND sr.lab_room = '$labFilter'" : "");

// Handle CSV export before any HTML prints out
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sitin_report_' . $fromDate . '_to_' . $toDate . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID Number','Student Name','Lab','Purpose','Time In','Time Out','Duration (min)','Status']);
    $exportResult = $conn->query("
        SELECT u.id_number, CONCAT(u.firstname,' ',u.lastname) AS name,
               sr.lab_room, sr.purpose, sr.checkin_time, sr.checkout_time,
               TIMESTAMPDIFF(MINUTE, sr.checkin_time, sr.checkout_time) AS duration, sr.status
        FROM sitin_records sr 
        LEFT JOIN users u ON u.id = sr.user_id
        $baseWhere
        ORDER BY sr.checkin_time DESC
    ");
    if ($exportResult) while ($row = $exportResult->fetch_assoc()) fputcsv($out, $row);
    fclose($out);
    exit();
}

// Stats for the period
$summaryResult = $conn->query("
    SELECT 
        COUNT(*) AS total_sessions,
        COUNT(DISTINCT sr.user_id) AS unique_students,
        SUM(TIMESTAMPDIFF(MINUTE, sr.checkin_time, sr.checkout_time)) AS total_minutes,
        AVG(TIMESTAMPDIFF(MINUTE, sr.checkin_time, sr.checkout_time)) AS avg_minutes
    FROM sitin_records sr 
    $baseWhere AND sr.checkout_time IS NOT NULL
");
$summary = $summaryResult ? $summaryResult->fetch_assoc() : [];

// Per-lab breakdown
$labBreakdown = $conn->query("
    SELECT sr.lab_room,
           COUNT(*) AS sessions,
           COUNT(DISTINCT sr.user_id) AS students,
           SUM(TIMESTAMPDIFF(MINUTE, sr.checkin_time, sr.checkout_time)) AS total_mins
    FROM sitin_records sr 
    $baseWhere AND sr.checkout_time IS NOT NULL
    GROUP BY sr.lab_room ORDER BY sessions DESC
");

// Per-day trend
$dailyTrend = $conn->query("
    SELECT DATE(sr.checkin_time) AS day, COUNT(*) AS sessions
    FROM sitin_records sr 
    $baseWhere
    GROUP BY DATE(sr.checkin_time)
    ORDER BY day ASC
    LIMIT 31
");

// Top students
$topStudents = $conn->query("
    SELECT u.id_number, u.firstname, u.lastname, u.course,
           COUNT(*) AS sessions,
           SUM(TIMESTAMPDIFF(MINUTE, sr.checkin_time, sr.checkout_time)) AS total_mins
    FROM sitin_records sr
    LEFT JOIN users u ON u.id = sr.user_id
    $baseWhere AND sr.checkout_time IS NOT NULL
    GROUP BY sr.user_id, u.id_number, u.firstname, u.lastname, u.course
    ORDER BY sessions DESC
    LIMIT 10
");

// Top purposes
$topPurposes = $conn->query("
    SELECT sr.purpose, COUNT(*) AS cnt
    FROM sitin_records sr 
    $baseWhere
    GROUP BY sr.purpose ORDER BY cnt DESC LIMIT 8
");

function fmtMins($m) {
    if (!$m) return '0m';
    return floor($m/60).'h '.($m%60).'m';
}
?>

<div class="admin-container" style="max-width: 100%; padding: 20px 40px; box-sizing: border-box; font-family: 'Segoe UI', system-ui, sans-serif; background: #f8fafc;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div>
            <h2 class="section-title" style="margin: 0; font-size: 1.6rem; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-file-invoice"></i> Generated System Reports
            </h2>
            <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.88rem;">Compile, print, and analyze historical laboratory performance statistics.</p>
        </div>
    </div>

    <div class="admin-card" style="margin-bottom:25px; padding: 20px; border-radius: 12px; background: #fff; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <form method="GET" style="display: grid; grid-template-columns: repeat(3, 1fr) auto; gap: 20px; align-items: end;">
            <div style="display: flex; flex-direction: column;">
                <label style="font-size: 0.78rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">From Date</label>
                <input type="date" name="from" value="<?php echo $fromDate; ?>" style="width:100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; box-sizing: border-box; height: 42px;">
            </div>
            <div style="display: flex; flex-direction: column;">
                <label style="font-size: 0.78rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">To Date</label>
                <input type="date" name="to" value="<?php echo $toDate; ?>" style="width:100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; box-sizing: border-box; height: 42px;">
            </div>
            <div style="display: flex; flex-direction: column; margin-bottom: 10px;">
                <label style="font-size: 0.78rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Laboratory Focus</label>
                <select name="lab" style="width:100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; background:#fff; box-sizing: border-box; height: 42px;">
                    <option value="">All Labs</option>
                    <option value="Lab 524" <?php echo $labFilter=='Lab 524'?'selected':''; ?>>Lab 524</option>
                    <option value="Lab 526" <?php echo $labFilter=='Lab 526'?'selected':''; ?>>Lab 526</option>
                    <option value="Lab 542" <?php echo $labFilter=='Lab 542'?'selected':''; ?>>Lab 542</option>
                    <option value="Lab 544" <?php echo $labFilter=='Lab 544'?'selected':''; ?>>Lab 544</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; height: 42px; margin-bottom: 10px;">
                <button type="submit" style="padding: 0 24px; background: #003366; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem;">
                    <i class="fas fa-search"></i> Generate
                </button>
                <button type="button" onclick="window.print()" style="padding: 0 20px; background: #2c3e50; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem;">
                    <i class="fas fa-print"></i> Print/PDF
                </button>
                <a href="?from=<?php echo $fromDate; ?>&to=<?php echo $toDate; ?>&lab=<?php echo urlencode($labFilter); ?>&export=csv" style="padding: 0 20px; background: #27ae60; color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; border-radius: 8px; font-weight: 600; font-size: 0.9rem;">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
            </div>
        </form>
    </div>

    <section class="stats-overview" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px;">
        <div class="stat-box blue" style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-left:5px solid #003366; display:flex; align-items:center;">
            <i class="fas fa-desktop" style="font-size:2.2rem; color:#003366; margin-right:20px; background: #f0f7ff; padding: 12px; border-radius: 10px;"></i>
            <div class="stat-text">
                <h3 style="margin:0; font-size:1.8rem; color:#0f172a; font-weight:700;"><?php echo number_format($summary['total_sessions'] ?? 0); ?></h3>
                <p style="margin:4px 0 0 0; color:#64748b; font-size:0.85rem; font-weight: 600; text-transform: uppercase;">Total Sessions</p>
            </div>
        </div>
        <div class="stat-box green" style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-left:5px solid #27ae60; display:flex; align-items:center;">
            <i class="fas fa-user-graduate" style="font-size:2.2rem; color:#27ae60; margin-right:20px; background: #f0fdf4; padding: 12px; border-radius: 10px;"></i>
            <div class="stat-text">
                <h3 style="margin:0; font-size:1.8rem; color:#27ae60; font-weight:700;"><?php echo number_format($summary['unique_students'] ?? 0); ?></h3>
                <p style="margin:4px 0 0 0; color:#64748b; font-size:0.85rem; font-weight: 600; text-transform: uppercase;">Unique Students</p>
            </div>
        </div>
        <div class="stat-box gold" style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-left:5px solid #f1c40f; display:flex; align-items:center;">
            <i class="fas fa-clock" style="font-size:2.2rem; color:#b78103; margin-right:20px; background: #fefce8; padding: 12px; border-radius: 10px;"></i>
            <div class="stat-text">
                <h3 style="margin:0; font-size:1.8rem; color:#0f172a; font-weight:700;"><?php echo fmtMins(round($summary['total_minutes'] ?? 0)); ?></h3>
                <p style="margin:4px 0 0 0; color:#64748b; font-size:0.85rem; font-weight: 600; text-transform: uppercase;">Total Hours logged</p>
            </div>
        </div>
        <div class="stat-box purple" style="background: linear-gradient(135deg, #7e22ce, #6b21a8); border-radius:12px; padding:24px; box-shadow:0 4px 12px rgba(126,34,206,0.15); display:flex; align-items:center; color:white;">
            <i class="fas fa-chart-bar" style="font-size:2.2rem; color:#fff; margin-right:20px; background: rgba(255,255,255,0.1); padding: 12px; border-radius: 10px;"></i>
            <div class="stat-text">
                <h3 style="margin:0; font-size:1.8rem; font-weight:700; color:#fff;"><?php echo fmtMins(round($summary['avg_minutes'] ?? 0)); ?></h3>
                <p style="margin:4px 0 0 0; color:#cbd5e1; font-size:0.85rem; font-weight: 600; text-transform: uppercase;">Avg. Session Duration</p>
            </div>
        </div>
    </section>

    <div class="admin-grid" style="display:grid; grid-template-columns: 1.3fr 0.7fr; gap:20px; margin-bottom:25px;">
        <div class="admin-card" style="padding:22px; border-radius:12px; background:#fff; border: 1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h3 style="margin:0 0 15px 0; font-size:1rem; color:#1e293b; font-weight:700;"><i class="fas fa-chart-line" style="color:#003366;"></i> Daily Sit-in Trend</h3>
            <?php
            $days = []; $counts = [];
            if ($dailyTrend) while ($d = $dailyTrend->fetch_assoc()) {
                $days[]   = date('M d', strtotime($d['day']));
                $counts[] = (int)$d['sessions'];
            }
            ?>
            <div style="position:relative; height:230px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <div class="admin-card" style="padding:22px; border-radius:12px; background:#fff; border: 1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h3 style="margin:0 0 15px 0; font-size:1rem; color:#1e293b; font-weight:700;"><i class="fas fa-bullseye" style="color:#003366;"></i> Session Purposes</h3>
            <?php
            $purposes = []; $purposeCounts = [];
            if ($topPurposes) while ($p = $topPurposes->fetch_assoc()) {
                $purposes[]      = $p['purpose'] ? $p['purpose'] : 'Unspecified';
                $purposeCounts[] = (int)$p['cnt'];
            }
            ?>
            <div style="position:relative; height:230px;">
                <canvas id="purposeChart"></canvas>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 20px;">
        <div class="admin-card" style="padding:22px; border-radius:12px; background:#fff; border: 1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h3 style="margin:0 0 15px 0; font-size:1rem; color:#1e293b; font-weight:700;"><i class="fas fa-building" style="color:#27ae60;"></i> Lab Performance Matrix</h3>
            <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
                <table style="width:100%; border-collapse:collapse; font-size: 0.88rem; text-align: left;">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0; color:#475569; font-weight: 700;">
                            <th style="padding:14px 16px;">Laboratory</th>
                            <th style="padding:14px 16px;">Sessions</th>
                            <th style="padding:14px 16px;">Students</th>
                            <th style="padding:14px 16px;">Hours</th>
                            <th style="padding:14px 16px;">Volume Bar</th>
                        </tr>
                    </thead>
                    <tbody style="color:#334155;">
                        <?php 
                        $maxSessions = 1;
                        if ($labBreakdown && $labBreakdown->num_rows > 0) {
                            $allLabs = [];
                            while ($lb = $labBreakdown->fetch_assoc()) {
                                $allLabs[] = $lb;
                                $maxSessions = max($maxSessions, $lb['sessions']);
                            }
                            foreach ($allLabs as $lb):
                                $pct = round(($lb['sessions'] / $maxSessions) * 100);
                        ?>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:12px 16px;"><strong><?php echo htmlspecialchars($lb['lab_room'] ?: 'Unknown'); ?></strong></td>
                            <td style="padding:12px 16px;"><?php echo number_format($lb['sessions']); ?></td>
                            <td style="padding:12px 16px;"><?php echo number_format($lb['students']); ?></td>
                            <td style="padding:12px 16px; font-size: 0.82rem; color: #475569;"><?php echo fmtMins($lb['total_mins']); ?></td>
                            <td style="padding:12px 16px;">
                                <div style="background:#e2e8f0; border-radius:10px; height:8px; width:70px; overflow:hidden;">
                                    <div style="background:#003366; width:<?php echo $pct; ?>%; height:100%;"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; } else { ?>
                        <tr><td colspan="5" style="text-align:center; padding:40px; color:#94a3b8;">No records matched for the selected criteria.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-card" style="padding:22px; border-radius:12px; background:#fff; border: 1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h3 style="margin:0 0 15px 0; font-size:1rem; color:#1e293b; font-weight:700;"><i class="fas fa-star" style="color:#f1c40f;"></i> High-Frequency Students</h3>
            <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
                <table style="width:100%; border-collapse:collapse; font-size: 0.88rem; text-align: left;">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0; color:#475569; font-weight: 700;">
                            <th style="padding:14px 16px; width: 45px;">Rank</th>
                            <th style="padding:14px 16px;">ID Number</th>
                            <th style="padding:14px 16px;">Student Name</th>
                            <th style="padding:14px 16px;">Course</th>
                            <th style="padding:14px 16px; text-align:center;">Sessions</th>
                            <th style="padding:14px 16px;">Total Hours</th>
                        </tr>
                    </thead>
                    <tbody style="color:#334155;">
                        <?php if ($topStudents && $topStudents->num_rows > 0):
                            $rank = 1;
                            while ($ts = $topStudents->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding:12px 16px; font-weight: bold; text-align: center;">
                                <?php if ($rank == 1): ?><span style="color:#FFD700;">🥇</span>
                                <?php elseif ($rank == 2): ?><span>🥈</span>
                                <?php elseif ($rank == 3): ?><span>🥉</span>
                                <?php else: echo $rank; endif; $rank++; ?>
                            </td>
                            <td style="padding:12px 16px; font-weight: 600; color: #003366;"><?php echo htmlspecialchars($ts['id_number']); ?></td>
                            <td style="padding:12px 16px; font-weight: 500; color:#0f172a;"><?php echo htmlspecialchars(($ts['firstname']??'') . ' ' . ($ts['lastname']??'')); ?></td>
                            <td style="padding:12px 16px;"><span style="background: #eef2f7; color: #475569; padding: 3px 8px; border-radius: 4px; font-size:0.78rem; font-weight:600;"><?php echo htmlspecialchars($ts['course'] ?? '—'); ?></span></td>
                            <td style="padding:12px 16px; text-align:center;"><span style="background:#003366; color:white; padding:2px 8px; border-radius:4px; font-size:0.8rem; font-weight:700;"><?php echo $ts['sessions']; ?></span></td>
                            <td style="padding:12px 16px; font-weight:500; color: #475569; font-size:0.82rem;"><?php echo fmtMins($ts['total_mins']); ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="6" style="text-align:center; padding:40px; color:#94a3b8;">No student log interactions mapped for this frame.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    @page { size: landscape; margin: 12mm; }
    body { background: #fff; color: #000; }
    form, button, a, i { display: none !important; }
    div.admin-container { padding: 0 !important; }
    .admin-grid { display: block !important; }
    div[style*="display: grid"] { display: block !important; }
    .admin-card { page-break-inside: avoid; border: none !important; box-shadow: none !important; margin-bottom: 30px !important; width: 100% !important; }
    table { width: 100% !important; border: 1px solid #cbd5e1; font-size: 11px; }
    th, td { padding: 8px 10px !important; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Daily Trend Bar Config
new Chart(document.getElementById('trendChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($days); ?>,
        datasets: [{
            label: 'Sessions',
            data: <?php echo json_encode($counts); ?>,
            backgroundColor: '#003366dd',
            hoverBackgroundColor: '#003366',
            borderRadius: 5,
            barThickness: 20
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } }
    }
});

// Purposes Doughnut Config
new Chart(document.getElementById('purposeChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($purposes); ?>,
        datasets: [{
            data: <?php echo json_encode($purposeCounts); ?>,
            backgroundColor: ['#003366','#27ae60','#FFD700','#e74c3c','#9b59b6','#3498db','#e67e22','#1abc9c'],
            borderWidth: 2, borderColor: '#fff'
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 9 }, padding: 8 } } },
        cutout: '65%'
    }
});
</script>
</body>
</html>