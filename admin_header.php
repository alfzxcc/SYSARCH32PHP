<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=3.0">
    <style>
    .admin-nav-dropdown { position: relative; }
    .admin-nav-dropdown:hover .admin-drop-menu { display:block; }
    .admin-drop-menu {
        display:none; position:absolute; top:100%; left:0;
        background:#002244; border-radius:0 0 8px 8px;
        min-width:180px; z-index:9999;
        box-shadow:0 8px 20px rgba(0,0,0,0.3);
        padding:5px 0;
    }
    .admin-drop-menu a {
        display:block; padding:9px 16px;
        color:#fff !important; font-size:13px;
        text-decoration:none; white-space:nowrap;
        transition:background 0.2s;
    }
    .admin-drop-menu a:hover { background:#FFD700; color:#003366 !important; }
    .admin-drop-menu a i { width:16px; margin-right:6px; }
    .nav-menu > li > a > .fa-chevron-down { font-size:0.65rem; margin-left:4px; }
    </style>
</head>
<body>
<header class="top-nav">
    <div class="nav-container">
        <div class="nav-brand">
            <span class="brand-main">College of Computer Studies</span>
            <span class="brand-sub">ADMIN PANEL</span>
        </div>
        <ul class="nav-menu">
            <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Home</a></li>

            <!-- Students -->
            <li class="admin-nav-dropdown">
                <a href="#">Students <i class="fas fa-chevron-down"></i></a>
                <div class="admin-drop-menu">
                    <a href="admin_search.php"><i class="fas fa-search"></i> Search Student</a>
                    <a href="admin_students_list.php"><i class="fas fa-list"></i> Student List</a>
                    <a href="admin_rewards.php"><i class="fas fa-star"></i> Rewards / Points</a>
                </div>
            </li>

            <!-- Sit-in -->
            <li class="admin-nav-dropdown">
                <a href="#">Sit-in <i class="fas fa-chevron-down"></i></a>
                <div class="admin-drop-menu">
                    <a href="admin_sitin_manage.php"><i class="fas fa-desktop"></i> Manage Sit-in</a>
                    <a href="admin_current_sitin.php"><i class="fas fa-signal"></i> Current Sit-in</a>
                    <a href="admin_sitin_records.php"><i class="fas fa-clipboard-list"></i> Sit-in Records</a>
                </div>
            </li>

            <!-- Reports & Analytics -->
            <li class="admin-nav-dropdown">
                <a href="#">Reports <i class="fas fa-chevron-down"></i></a>
                <div class="admin-drop-menu">
                    <a href="admin_generate_reports.php"><i class="fas fa-file-chart-line"></i> Generate Reports</a>
                    <a href="admin_analytics.php"><i class="fas fa-chart-pie"></i> Analytics</a>
                </div>
            </li>

            <li><a href="admin_reservation.php"><i class="fas fa-calendar-alt"></i> Reservations</a></li>

            <!-- More -->
            <li class="admin-nav-dropdown">
                <a href="#">More <i class="fas fa-chevron-down"></i></a>
                <div class="admin-drop-menu">
                    <a href="admin_dashboard.php#announce"><i class="fas fa-bullhorn"></i> Announcements</a>
                    <a href="admin_software.php"><i class="fas fa-upload"></i> Software</a>
                    <a href="admin_testimonials.php"><i class="fas fa-comments"></i> Testimonials</a>
                    <a href="admin_settings.php"><i class="fas fa-cogs"></i> Settings</a>
                </div>
            </li>

            <li><a href="logout.php" class="nav-btn-reg" style="background:#e74c3c;color:white !important;">Logout</a></li>
        </ul>
    </div>
</header>
<main class="page-content">
