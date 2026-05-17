<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db_connect.php';

$firstname = $_SESSION['firstname'] ?? 'Guest';
$uid       = $_SESSION['user_id']   ?? null;

$unreadCount = 0;
if ($uid) {
    $notifResult = $conn->query("SELECT COUNT(*) as unread FROM notifications WHERE id_number = '$uid' AND is_read = 0");
    if ($notifResult) $unreadCount = $notifResult->fetch_assoc()['unread'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS Sit-in Monitoring</title>
    <link rel="stylesheet" href="style.css?v=2.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    /* ══════════════════════════════════════════
       DARK MODE — Extended System-Wide Overrides
       ══════════════════════════════════════════ */
    body.dark-mode {
        background: linear-gradient(rgba(0,0,0,0.88),rgba(0,0,0,0.88)),
                    url('UC.jpg') no-repeat center center fixed !important;
        background-size: cover !important;
        color: #e2e8f0 !important;
    }
    body.dark-mode .top-nav            { background:#0a1929; border-bottom-color:#FFD700; }
    
    /* Structural Containers & Review Boards */
    body.dark-mode .profile-card,
    body.dark-mode .announcement-card,
    body.dark-mode .rules-card,
    body.dark-mode .admin-card,
    body.dark-mode .login-card,
    body.dark-mode .register-card,
    body.dark-mode .admin-container,
    body.dark-mode [class*="card"] { 
        background: #1e1e2e !important; 
        color: #e0e0e0 !important; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.6) !important; 
    }

    /* Target specific components from the screenshots */
    body.dark-mode div[style*="background:#ffc107"],
    body.dark-mode div[style*="background: #ffc107"] {
        background: #b48600 !important; /* Muted yellow/amber alert block for dark mode */
        color: #fff !important;
    }
    body.dark-mode div[style*="background:#f9f9f9"],
    body.dark-mode div[style*="background: #f9f9f9"] {
        background: #252538 !important; /* Soft backdrop for blockquotes/reviews */
        border-left-color: #90b4e8 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .card-header-main   { border-bottom-color:#333 !important; }
    body.dark-mode .card-header-main h2{ color:#90b4e8 !important; }
    body.dark-mode .info-group label   { color:#90b4e8 !important; }
    body.dark-mode .info-group span    { background:#2a2a3a !important; color:#ddd !important; border-color:#444 !important; }
    body.dark-mode .info-group.highlight span { background:#1a2a1a !important; color:#7dda8a !important; border-color:#FFD700 !important; }
    
    /* Table Adjustments & History Logs */
    body.dark-mode table, 
    body.dark-mode th,
    body.dark-mode td                  { border-color:#2f2f3f !important; color:#e2e8f0 !important; }
    body.dark-mode th                  { background:#0a1929 !important; color:#ffffff !important; }
    body.dark-mode tr                  { background: transparent !important; }
    body.dark-mode tr:hover            { background:#252535 !important; }
    body.dark-mode td small            { color: #a0aec0 !important; }

    /* Interactive Inputs, Select Elements & Forms */
    body.dark-mode input,
    body.dark-mode select,
    body.dark-mode textarea            { background:#2a2a3a !important; color:#ffffff !important; border-color:#4a4a5a !important; }
    body.dark-mode input::placeholder,
    body.dark-mode textarea::placeholder { color:#718096 !important; }
    body.dark-mode select option       { background:#1e1e2e !important; color:#ffffff !important; }

    /* Buttons and Action Control Normalization */
    body.dark-mode .request-btn        { background:#003366 !important; color: #fff !important; }
    body.dark-mode .stat-box           { background:#1e1e2e !important; }
    body.dark-mode p, body.dark-mode span, body.dark-mode li { color:#cbd5e0; }
    body.dark-mode a                   { color:#90b4e8; }
    body.dark-mode .announcement-item  { background:rgba(255,255,255,0.05) !important; }
    body.dark-mode .section-title      { background:linear-gradient(90deg,#0a1929 0%,rgba(10,25,41,0.85) 100%) !important; }
    
    /* Cancel Action Buttons and Labels */
    body.dark-mode a[style*="background:#e74c3c"],
    body.dark-mode button[style*="background:#e74c3c"] {
        background: #c53030 !important; /* Enhanced darker red for contrast */
    }

    /* ── Dark Mode Toggle Button ── */
    #dmToggle {
        background: none;
        border: 1.5px solid rgba(255,255,255,0.35);
        border-radius: 20px;
        color: #fff;
        padding: 5px 12px;
        cursor: pointer;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.25s;
        white-space: nowrap;
    }
    #dmToggle:hover { background:rgba(255,215,0,0.15); border-color:#FFD700; color:#FFD700; }
    body.dark-mode #dmToggle { border-color:#FFD700; color:#FFD700; background:rgba(255,215,0,0.1); }
    </style>
</head>
<body>

<header class="top-nav">
    <div class="nav-container">
        <?php $home_url = isset($_SESSION['user_id']) ? 'dashboard.php' : 'index.php'; ?>
        <a href="<?php echo $home_url; ?>" class="nav-brand-link">
            <div class="nav-brand">
                <span class="brand-main">College of Computer Studies</span>
                <span class="brand-sub">Sit-In Monitoring System</span>
            </div>
        </a>

        <ul class="nav-menu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="notif-wrapper">
                    <a href="#" id="notifBtn" style="position:relative;">
                        <i class="fas fa-bell"></i>
                        <?php if($unreadCount > 0): ?>
                            <span class="notif-badge"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <div id="notifDropdown" class="notif-dropdown">
                        <div class="notif-header">
                            <span>Notifications</span>
                            <?php if($unreadCount > 0): ?><small style="color:#e74c3c;">New Alerts</small><?php endif; ?>
                        </div>
                        <div class="notif-body">
                            <?php
                            $listRes = $conn->query("SELECT * FROM notifications WHERE id_number = '$uid' ORDER BY created_at DESC LIMIT 5");
                            if ($listRes && $listRes->num_rows > 0) {
                                while ($n = $listRes->fetch_assoc()) {
                                    $cls = $n['is_read']==0 ? 'unread-bg' : '';
                                    echo "<div class='notif-item $cls'>
                                            <p>".htmlspecialchars($n['message'])."</p>
                                            <small><i class='far fa-clock'></i> ".date('M d, h:i A',strtotime($n['created_at']))."</small>
                                          </div>";
                                }
                            } else {
                                echo "<div class='notif-item' style='text-align:center;'>No notifications yet</div>";
                            }
                            ?>
                        </div>
                    </div>
                </li>

                <li><a href="dashboard.php"><i class="fas fa-home" style="margin-right:4px;"></i> Home</a></li>
                <li><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="student_history.php">History</a></li>
                <li><a href="student_reservation.php"><i class="fas fa-calendar-check" style="margin-right:4px;"></i> Reservation</a></li>
                <li><a href="sitin_summary.php">My Summary</a></li>
                <li><a href="software_availability.php">Software</a></li>
                <li><a href="testimonials.php"><i class="fas fa-comment-dots" style="margin-right:4px;"></i> Testimonials</a></li>

                <li>
                    <button id="dmToggle" onclick="toggleDarkMode()" title="Toggle dark mode">
                        <i class="fas fa-moon" id="dmIcon"></i>
                        <span id="dmLabel">Dark</span>
                    </button>
                </li>

                <li><a href="logout.php" class="nav-btn-reg" style="background:#e74c3c;color:white !important;">Logout</a></li>
            <?php else: ?>
                <li><a href="login_page.php">Login</a></li>
                <li><a href="register.php" class="nav-btn-reg">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>

<script>
// ── Notification dropdown ──
const notifBtn      = document.getElementById('notifBtn');
const notifDropdown = document.getElementById('notifDropdown');
if (notifBtn) {
    notifBtn.addEventListener('click', e => {
        e.preventDefault();
        notifDropdown.style.display = notifDropdown.style.display === 'block' ? 'none' : 'block';
    });
    window.addEventListener('click', e => {
        if (!notifBtn.contains(e.target) && !notifDropdown.contains(e.target))
            notifDropdown.style.display = 'none';
    });
}

// ── Dark Mode ──
function applyDark(on) {
    document.body.classList.toggle('dark-mode', on);
    const icon  = document.getElementById('dmIcon');
    const label = document.getElementById('dmLabel');
    if (icon && label) {
        icon.className  = on ? 'fas fa-sun' : 'fas fa-moon';
        label.textContent = on ? 'Light' : 'Dark';
    }
}
function toggleDarkMode() {
    const on = !document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', on ? '1' : '0');
    applyDark(on);
}
// Restore preference on every page load
(function() { applyDark(localStorage.getItem('darkMode') === '1'); })();
</script>

<main class="page-content">