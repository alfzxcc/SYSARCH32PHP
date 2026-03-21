<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$firstname = $_SESSION['firstname'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS Sit-in Monitoring</title>
    <link rel="stylesheet" href="style.css?v=1.3"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<header class="top-nav">
    <div class="nav-container">
        <?php 
            // Determine where the 'Home' should go
            $home_url = isset($_SESSION['user_id']) ? 'dashboard.php' : 'index.php';
        ?>

        <a href="<?php echo $home_url; ?>" class="nav-brand-link">
            <div class="nav-brand">
                <span class="brand-main">College of Computer Studies</span>
                <span class="brand-sub">Sit-In Monitoring System</span>
            </div>
        </a> 

        <ul class="nav-menu">
            <?php if (isset($_SESSION['user_id'])): ?>
                
                <li><a href="#"><i class="fas fa-bell"></i></a></li> <li><a href="dashboard.php">Home</a></li>
                <li><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="history.php">History</a></li>
                <li><a href="reservation.php">Reservation</a></li>
                <li><a href="logout.php" class="nav-btn-reg" style="background:#e74c3c; color:white !important;">Logout</a></li>
            <?php else: ?>
                <li><a href="login_page.php">Login</a></li>
                <li><a href="register.php" class="nav-btn-reg">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>

<main class="page-content">