<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<header class="top-nav">
    <div class="nav-container">
        <div class="nav-brand">
            <span class="brand-main">College of Computer Studies</span>
            <span class="brand-sub">ADMIN PANEL</span>
        </div>
        <ul class="nav-menu">
            <li><a href="admin_dashboard.php">Home</a></li>
            <li><a href="admin_search.php">Search</a></li>
            <li><a href="admin_students_list.php"><i class="fas fa-list"></i> Students List</a></li>
            <li><a href="admin_sitin_manage.php"><i class="fas fa-desktop"></i> Sit-in</a></li>
            <li><a href="admin_sitin_records.php"><i class="fas fa-clipboard-list"></i> Records</a></li>
            <li><a href="admin_reports.php">Reports</a></li>
            <li><a href="feedback.php">Feedback</a></li>
            <li><a href="admin_reservation.php">Reservation</a></li>
            <li><a href="logout.php" class="nav-btn-reg" style="background:#e74c3c; color:white !important;">Logout</a></li>
        </ul>
    </div>
</header>
<main class="page-content">