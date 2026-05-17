<?php 
include 'admin_check.php'; 
require_once 'db_connect.php'; 
include 'admin_header.php';

$conn->set_charset("utf8mb4");

// Overall numbers
$totalStudents  = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='student'")->fetch_assoc()['c'] ?? 0;
$totalSessions  = $conn->query("SELECT COUNT(*) AS c FROM sitin_records")->fetch_assoc()['c'] ?? 0;
$activeSessions = $conn->query("SELECT COUNT(*) AS c FROM sitin_records WHERE status='active'")->fetch_assoc()['c'] ?? 0;
$todaySessions  = $conn->query("SELECT COUNT(*) AS c FROM sitin_records WHERE DATE(checkin_time)=CURDATE()")->fetch_assoc()['c'] ?? 0;

// Hourly distribution (this week)
$hourlyResult = $conn->query("
    SELECT HOUR(checkin_time) AS hr, COUNT(*) AS cnt
    FROM sitin_records WHERE checkin_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY HOUR(checkin_time) ORDER BY hr
");
$hours = array_fill(0, 24, 0); 
if ($hourlyResult) while ($h = $hourlyResult->fetch_assoc()) $hours[(int)$h['hr']] = (int)$h['cnt'];

// Weekly trend (last 4 weeks)
$weeklyResult = $conn->query("
    SELECT YEARWEEK(checkin_time,1) AS yw, 
           MIN(DATE(checkin_time)) AS week_start, COUNT(*) AS cnt
    FROM sitin_records WHERE checkin_time >= DATE_SUB(NOW(), INTERVAL 28 DAY)
    GROUP BY YEARWEEK(checkin_time,1) ORDER BY yw
");
$wLabels = []; $wCounts = [];
if ($weeklyResult) while ($w = $weeklyResult->fetch_assoc()) {
    $wLabels[] = 'Wk of ' . date('M d', strtotime($w['week_start']));
    $wCounts[] = (int)$w['cnt'];
}

// Monthly trend (last 6 months)
$monthlyResult = $conn->query("
    SELECT DATE_FORMAT(checkin_time, '%Y-%m') AS mo, COUNT(*) AS cnt
    FROM sitin_records WHERE checkin_time >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY mo ORDER BY mo
");
$mLabels = []; $mCounts = [];
if ($monthlyResult) while ($m = $monthlyResult->fetch_assoc()) {
    $mLabels[] = date('M Y', strtotime($m['mo'].'-01'));
    $mCounts[] = (int)$m['cnt'];
}

// Lab usage
$labResult = $conn->query("
    SELECT lab_room, COUNT(*) AS cnt FROM sitin_records GROUP BY lab_room ORDER BY cnt DESC
");
$labLabels = []; $labCounts = [];
if ($labResult) while ($l = $labResult->fetch_assoc()) { 
    $labLabels[] = $l['lab_room'] ? $l['lab_room'] : 'Unknown'; 
    $labCounts[] = (int)$l['cnt']; 
}

// Course breakdown
$courseResult = $conn->query("
    SELECT u.course, COUNT(*) AS cnt 
    FROM sitin_records sr LEFT JOIN users u ON u.id = sr.user_id
    WHERE u.course IS NOT NULL AND u.course != '' 
    GROUP BY u.course ORDER BY cnt DESC LIMIT 8
");
$courseLabels = []; $courseCounts = [];
if ($courseResult) while ($c = $courseResult->fetch_assoc()) { 
    $courseLabels[] = $c['course']; 
    $courseCounts[] = (int)$c['cnt']; 
}

// Purpose breakdown
$purposeResult = $conn->query("
    SELECT purpose, COUNT(*) AS cnt FROM sitin_records GROUP BY purpose ORDER BY cnt DESC LIMIT 8
");
$purposeLabels = []; $purposeCounts = [];
if ($purposeResult) while ($p = $purposeResult->fetch_assoc()) { 
    $purposeLabels[] = $p['purpose'] ? $p['purpose'] : 'Unspecified'; 
    $purposeCounts[] = (int)$p['cnt']; 
}

// Session completion rate
$compResult = $conn->query("
    SELECT 
        SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) AS active,
        SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) AS cancelled
    FROM sitin_records
");
$comp = $compResult ? $compResult->fetch_assoc() : ['completed'=>0,'active'=>0,'cancelled'=>0];
?>

<div class="admin-container" style="max-width: 100%; padding: 20px 40px; box-sizing: border-box; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #f8fafc;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h2 class="section-title" style="margin: 0; font-size: 1.6rem;  font-weight: 700; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-chart-pie"></i> System Analytics Dashboard
        </h2>
        <span style="background: #e2e8f0; color: #475569; padding: 6px 14px; border-radius: 30px; font-size: 0.85rem; font-weight: 600;">
            <i class="fas fa-sync-alt"></i> Live Real-Time Analytics
        </span>
    </div>

    <section class="stats-overview" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px;">
        <div class="stat-box" style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-left:5px solid #003366; display:flex; align-items:center;">
            <i class="fas fa-users" style="font-size:2.2rem; color:#003366; margin-right:20px; background: #f0f7ff; padding: 12px; border-radius: 10px;"></i>
            <div class="stat-text">
                <h3 style="margin:0; font-size:1.8rem; color:#0f172a; font-weight:700;"><?php echo number_format($totalStudents); ?></h3>
                <p style="margin:4px 0 0 0; color:#64748b; font-size:0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Registered Students</p>
            </div>
        </div>
        <div class="stat-box" style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-left:5px solid #27ae60; display:flex; align-items:center;">
            <i class="fas fa-desktop" style="font-size:2.2rem; color:#27ae60; margin-right:20px; background: #f0fdf4; padding: 12px; border-radius: 10px;"></i>
            <div class="stat-text">
                <h3 style="margin:0; font-size:1.8rem; color:#27ae60; font-weight:700;"><?php echo number_format($totalSessions); ?></h3>
                <p style="margin:4px 0 0 0; color:#64748b; font-size:0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">All-time Sessions</p>
            </div>
        </div>
        <div class="stat-box" style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-left:5px solid #f1c40f; display:flex; align-items:center;">
            <i class="fas fa-signal" style="font-size:2.2rem; color:#b78103; margin-right:20px; background: #fefce8; padding: 12px; border-radius: 10px;"></i>
            <div class="stat-text">
                <h3 style="margin:0; font-size:1.8rem; color:#0f172a; font-weight:700;"><?php echo number_format($activeSessions); ?></h3>
                <p style="margin:4px 0 0 0; color:#64748b; font-size:0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Currently Active</p>
            </div>
        </div>
        <div class="stat-box" style="background: linear-gradient(135deg, #002244, #003366); border-radius:12px; padding:24px; box-shadow:0 4px 12px rgba(0,51,102,0.15); display:flex; align-items:center; color:white;">
            <i class="fas fa-calendar-day" style="font-size:2.2rem; color:#fff; margin-right:20px; background: rgba(255,255,255,0.1); padding: 12px; border-radius: 10px;"></i>
            <div class="stat-text">
                <h3 style="margin:0; font-size:1.8rem; font-weight:700; color:#fff;"><?php echo number_format($todaySessions); ?></h3>
                <p style="margin:4px 0 0 0; color:#cbd5e1; font-size:0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Today's Sessions</p>
            </div>
        </div>
    </section>

    <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 20px; margin-bottom: 25px;">
        <div class="admin-card" style="padding:22px; border-radius:12px; background:#fff; border: 1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h3 style="margin:0 0 15px 0; font-size:1rem; color:#1e293b; font-weight:700;"><i class="fas fa-chart-area" style="color:#003366;"></i> Monthly Sit-in Volume</h3>
            <div style="position:relative; height:230px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
        
        <div class="admin-card" style="padding:22px; border-radius:12px; background:#fff; border: 1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h3 style="margin:0 0 15px 0; font-size:1rem; color:#1e293b; font-weight:700;"><i class="fas fa-chart-bar" style="color:#003366;"></i> Weekly Performance</h3>
            <div style="position:relative; height:230px;">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>

        <div class="admin-card" style="padding:22px; border-radius:12px; background:#fff; border: 1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h3 style="margin:0 0 15px 0; font-size:1rem; color:#1e293b; font-weight:700;"><i class="fas fa-clock" style="color:#f1c40f;"></i> Hourly Peak Load (7D)</h3>
            <div style="position:relative; height:230px;">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 20px;">
        <div class="admin-card" style="padding:22px; border-radius:12px; background:#fff; border: 1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h3 style="margin:0 0 15px 0; font-size:1rem; color:#1e293b; font-weight:700;"><i class="fas fa-building" style="color:#27ae60;"></i> Lab Distribution</h3>
            <div style="position:relative; height:210px;">
                <canvas id="labChart"></canvas>
            </div>
        </div>

        <div class="admin-card" style="padding:22px; border-radius:12px; background:#fff; border: 1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h3 style="margin:0 0 15px 0; font-size:1rem; color:#1e293b; font-weight:700;"><i class="fas fa-graduation-cap" style="color:#9b59b6;"></i> Top Courses</h3>
            <div style="position:relative; height:210px;">
                <canvas id="courseChart"></canvas>
            </div>
        </div>

        <div class="admin-card" style="padding:22px; border-radius:12px; background:#fff; border: 1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h3 style="margin:0 0 15px 0; font-size:1rem; color:#1e293b; font-weight:700;"><i class="fas fa-bullseye" style="color:#e74c3c;"></i> Primary Purposes</h3>
            <div style="position:relative; height:210px;">
                <canvas id="purposeChart"></canvas>
            </div>
        </div>

        <div class="admin-card" style="padding:22px; border-radius:12px; background:#fff; border: 1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h3 style="margin:0 0 15px 0; font-size:1rem; color:#1e293b; font-weight:700;"><i class="fas fa-tasks" style="color:#3498db;"></i> Resolution Metrics</h3>
                <div style="position:relative; height:150px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            <div style="background: #f8fafc; border-radius: 8px; padding: 10px; display: flex; justify-content: space-around; font-size:0.78rem; color:#64748b; font-weight:700; text-align: center; border: 1px solid #f1f5f9;">
                <div><span style="color:#27ae60; display:block; font-size: 0.9rem;"><?php echo number_format($comp['completed']??0); ?></span>Done</div>
                <div style="border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; padding: 0 10px;"><span style="color:#3498db; display:block; font-size: 0.9rem;"><?php echo number_format($comp['active']??0); ?></span>Active</div>
                <div><span style="color:#e74c3c; display:block; font-size: 0.9rem;"><?php echo number_format($comp['cancelled']??0); ?></span>Drop</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const palette = ['#003366','#27ae60','#FFD700','#e74c3c','#9b59b6','#3498db','#e67e22','#1abc9c'];

// Monthly Line Chart
new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($mLabels ? $mLabels : ['No records']); ?>,
        datasets: [{ label: 'Sessions', data: <?php echo json_encode($mCounts ? $mCounts : [0]); ?>,
            borderColor: '#003366', backgroundColor: '#00336615',
            borderWidth: 2.5, pointRadius: 4, pointBackgroundColor: '#003366', fill: true, tension: 0.35 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
});

// Weekly Bar Tracker
new Chart(document.getElementById('weeklyChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($wLabels ? $wLabels : ['No data']); ?>,
        datasets: [{ label: 'Sessions', data: <?php echo json_encode($wCounts ? $wCounts : [0]); ?>,
            backgroundColor: '#003366dd', hoverBackgroundColor: '#003366', borderRadius: 6, barThickness: 25 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
});

// Hourly Peak Analysis
new Chart(document.getElementById('hourlyChart'), {
    type: 'bar',
    data: {
        labels: <?php
            $hKeys = []; $hVals = [];
            for ($i = 7; $i <= 19; $i++) {
                $hKeys[] = ($i < 12 ? $i.'AM' : ($i == 12 ? '12PM' : ($i - 12).'PM'));
                $hVals[] = $hours[$i] ?? 0;
            }
            echo json_encode($hKeys);
        ?>,
        datasets: [{ label: 'Sessions', data: <?php echo json_encode($hVals); ?>,
            backgroundColor: '#f1c40fdd', hoverBackgroundColor: '#f1c40f', borderRadius: 4 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
});

// Lab Doughnut Chart
new Chart(document.getElementById('labChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($labLabels ? $labLabels : ['No data']); ?>,
        datasets: [{ data: <?php echo json_encode($labCounts ? $labCounts : [0]); ?>,
            backgroundColor: palette, borderWidth: 2, borderColor: '#fff' }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '70%',
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 }, padding: 12 } } } }
});

// Course Horizontal Bars
new Chart(document.getElementById('courseChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($courseLabels ? $courseLabels : ['No data']); ?>,
        datasets: [{ label: 'Sessions', data: <?php echo json_encode($courseCounts ? $courseCounts : [0]); ?>,
            backgroundColor: '#9b59b6cc', borderRadius: 4, barThickness: 12 }]
    },
    options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y',
        plugins: { legend: { display: false } }, 
        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } }, y: { grid: { display: false } } } }
});

// Purpose Vertical Bars
new Chart(document.getElementById('purposeChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($purposeLabels ? $purposeLabels : ['No data']); ?>,
        datasets: [{ label: 'Sessions', data: <?php echo json_encode($purposeCounts ? $purposeCounts : [0]); ?>,
            backgroundColor: '#e74c3ccc', borderRadius: 4, barThickness: 14 }]
    },
    options: { responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } }, 
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
});

// Completion Status Pie
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Completed','Active','Cancelled'],
        datasets: [{ data: [<?php echo (int)($comp['completed']??0); ?>, <?php echo (int)($comp['active']??0); ?>, <?php echo (int)($comp['cancelled']??0); ?>],
            backgroundColor: ['#27ae60','#3498db','#e74c3c'], borderWidth: 2, borderColor: '#fff' }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '75%',
        plugins: { legend: { display: false } } }
});
</script>
</body>
</html>