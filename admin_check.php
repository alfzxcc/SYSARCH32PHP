<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Update the check to look for the string 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_page.php?error=unauthorized");
    exit();
}
?>